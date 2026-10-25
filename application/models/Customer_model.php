<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
	const STATUS_INCOMPLETE = 'incomplete';
	const STATUS_UNVERIFIED = 'unverified';
	const STATUS_VERIFIED   = 'verified';

	/**
	 * Daftar pelanggan dengan filter, urut terbaru.
	 *
	 * @param array    $filters q, status, view (active|archived)
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array
	 */
	public function get_all(array $filters, $limit = NULL, $offset = NULL)
	{
		$this->_apply_filters($filters);
		$this->db->order_by('created_at', 'DESC')->order_by('id', 'DESC');
		return $this->db->get('customers', $limit, $offset)->result_array();
	}

	/**
	 * @param array $filters
	 * @return int
	 */
	public function count_all(array $filters)
	{
		$this->_apply_filters($filters);
		return (int) $this->db->count_all_results('customers');
	}

	/**
	 * Saran kata kunci pencarian ringkas.
	 *
	 * Hanya mengembalikan kata kunci dan label tipe, tanpa konteks pelanggan.
	 *
	 * @param array $filters q, status, view (active|archived)
	 * @param int   $limit
	 * @return array
	 */
	public function search_suggestions(array $filters, $limit = 6)
	{
		$limit = max(1, (int) $limit);
		$q     = isset($filters['q']) ? trim((string) $filters['q']) : '';

		if ($this->_strlen($q) < 2)
		{
			return array();
		}

		// Ambil kandidat pelanggan yang cocok dengan filter & kata kunci aktif.
		$filters['q'] = $q;
		$rows = $this->get_all($filters, 50, 0);

		$fields = array(
			'full_name'       => array('type' => 'name',     'label' => 'Nama',            'priority' => 10, 'match' => 'text'),
			'customer_code'   => array('type' => 'code',     'label' => 'Kode',            'priority' => 20, 'match' => 'text'),
			'whatsapp_number' => array('type' => 'wa',       'label' => 'WhatsApp',        'priority' => 30, 'match' => 'wa'),
			'housing_name'    => array('type' => 'address',  'label' => 'Alamat/Perumahan', 'priority' => 40, 'match' => 'text'),
			'full_address'    => array('type' => 'address',  'label' => 'Alamat/Perumahan', 'priority' => 40, 'match' => 'text'),
			'block_number'    => array('type' => 'block_rt', 'label' => 'Blok/RT-RW',      'priority' => 50, 'match' => 'block'),
			'rt_rw'           => array('type' => 'block_rt', 'label' => 'Blok/RT-RW',      'priority' => 50, 'match' => 'rt_rw'),
		);

		$found = array();
		foreach ($rows as $row)
		{
			foreach ($fields as $field => $meta)
			{
				$keyword = $this->_normalize_text(isset($row[$field]) ? $row[$field] : '');
				if ($keyword === '')
				{
					continue;
				}

				$rank = $this->_suggestion_rank($meta['match'], $keyword, $q);
				if ($rank === NULL)
				{
					continue;
				}

				$label = $keyword;
				if ($meta['type'] === 'address' && $this->_strlen($keyword) > 80)
				{
					list($keyword, $label) = $this->_excerpt($keyword, $q, 80);
				}

				$key = $meta['type'].'|'.$this->_strtolower($keyword);
				if (isset($found[$key]) && $found[$key]['rank'] <= $rank)
				{
					continue;
				}

				$found[$key] = array(
					'type'       => $meta['type'],
					'type_label' => $meta['label'],
					'keyword'    => $keyword,
					'label'      => $label,
					'rank'       => $rank,
					'priority'   => $meta['priority'],
				);
			}
		}

		$suggestions = array_values($found);
		usort($suggestions, function ($a, $b) {
			if ($a['rank'] !== $b['rank'])
			{
				return $a['rank'] - $b['rank'];
			}
			if ($a['priority'] !== $b['priority'])
			{
				return $a['priority'] - $b['priority'];
			}

			$length = $this->_strlen($a['keyword']) - $this->_strlen($b['keyword']);
			if ($length !== 0)
			{
				return $length;
			}
			return strcasecmp($a['keyword'], $b['keyword']);
		});

		$suggestions = array_slice($suggestions, 0, $limit);
		foreach ($suggestions as &$suggestion)
		{
			unset($suggestion['rank'], $suggestion['priority']);
		}
		unset($suggestion);

		return $suggestions;
	}

	/**
	 * @param int|null $limit
	 * @return array
	 */
	public function get_recent($limit = 5)
	{
		return $this->db->where('archived_at IS NULL', NULL, FALSE)
			->order_by('created_at', 'DESC')->order_by('id', 'DESC')
			->get('customers', (int) $limit)->result_array();
	}

	/**
	 * Statistik untuk dashboard (hanya data aktif kecuali arsip).
	 *
	 * @return array
	 */
	public function get_stats()
	{
		$row = $this->db->select(
				"COUNT(*) AS total,"
				." SUM(location_status = 'verified') AS verified,"
				." SUM(location_status = 'unverified') AS unverified,"
				." SUM(location_status = 'incomplete') AS incomplete",
				FALSE
			)
			->where('archived_at IS NULL', NULL, FALSE)
			->get('customers')->row_array();

		$archived = (int) $this->db->where('archived_at IS NOT NULL', NULL, FALSE)
			->count_all_results('customers');

		return array(
			'total'      => (int) ($row['total'] ?? 0),
			'verified'   => (int) ($row['verified'] ?? 0),
			'unverified' => (int) ($row['unverified'] ?? 0),
			'incomplete' => (int) ($row['incomplete'] ?? 0),
			'archived'   => $archived,
		);
	}

	/**
	 * @param int $id
	 * @return array|null
	 */
	public function get($id)
	{
		return $this->db->get_where('customers', array('id' => (int) $id))->row_array();
	}

	/**
	 * Cari pelanggan dengan nomor WhatsApp sama (data aktif).
	 *
	 * @param string   $whatsapp_number
	 * @param int|null $exclude_id
	 * @return array|null
	 */
	public function find_duplicate($whatsapp_number, $exclude_id = NULL)
	{
		$this->db->where('whatsapp_number', trim($whatsapp_number));
		$this->db->where('archived_at IS NULL', NULL, FALSE);
		if ($exclude_id)
		{
			$this->db->where('id !=', (int) $exclude_id);
		}
		return $this->db->get('customers')->row_array();
	}

	/**
	 * Simpan pelanggan baru dengan kode FM-00001 otomatis.
	 *
	 * @param array $data
	 * @return int|false ID pelanggan atau FALSE bila gagal.
	 */
	public function create(array $data)
	{
		$number = $this->_next_code_number();

		for ($i = 0; $i < 10; $i++)
		{
			$code = 'FM-'.str_pad((string) ($number + $i), 5, '0', STR_PAD_LEFT);
			if ($this->code_exists($code))
			{
				continue;
			}

			$data['customer_code'] = $code;
			if ($this->db->insert('customers', $data))
			{
				return (int) $this->db->insert_id();
			}
		}

		return FALSE;
	}

	/**
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, array $data)
	{
		return $this->db->where('id', (int) $id)->update('customers', $data);
	}

	/**
	 * Perbarui hanya tautan Google Maps (dipakai simpan cepat realtime).
	 *
	 * @param int         $id
	 * @param string|null $url
	 * @return bool
	 */
	public function update_maps_url($id, $url)
	{
		return $this->db->where('id', (int) $id)
			->update('customers', array('google_maps_url' => $url));
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function code_exists($code)
	{
		return $this->db->where('customer_code', $code)->count_all_results('customers') > 0;
	}

	/**
	 * Arsipkan (soft delete).
	 *
	 * @param int $id
	 * @return bool
	 */
	public function archive($id)
	{
		return $this->db->where('id', (int) $id)
			->update('customers', array('archived_at' => date('Y-m-d H:i:s')));
	}

	/**
	 * Pulihkan dari arsip.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function restore($id)
	{
		return $this->db->where('id', (int) $id)
			->update('customers', array('archived_at' => NULL));
	}

	// ---------------------------------------------------------------------

	private function _apply_filters(array $filters)
	{
		if (isset($filters['view']) && $filters['view'] === 'archived')
		{
			$this->db->where('archived_at IS NOT NULL', NULL, FALSE);
		}
		else
		{
			$this->db->where('archived_at IS NULL', NULL, FALSE);
		}

		if ( ! empty($filters['status']))
		{
			$this->db->where('location_status', $filters['status']);
		}

		if ( ! empty($filters['q']))
		{
			$q = $filters['q'];
			$this->db->group_start()
				->like('full_name', $q)
				->or_like('whatsapp_number', $q)
				->or_like('full_address', $q)
				->or_like('housing_name', $q)
				->or_like('block_number', $q)
				->or_like('rt_rw', $q)
				->or_like('landmark', $q)
				->or_like('customer_code', $q);

			// "RT 03" / "RW 05" / "Blok A" tetap menemukan kolom yang hanya
			// menyimpan angka atau kode tanpa awalan.
			$rt_term = preg_replace('/^(rt|rw)\.?\s*(\d.*)$/i', '$2', $q);
			if ($rt_term !== NULL && $rt_term !== $q && $rt_term !== '')
			{
				$this->db->or_like('rt_rw', $rt_term);
			}

			$block_term = preg_replace('/^(blok|blk)(?:\.|\s)+([a-z0-9].*)$/i', '$2', $q);
			if ($block_term !== NULL && $block_term !== $q && $block_term !== '')
			{
				$this->db->or_like('block_number', $block_term);
			}

			// Alamat dari textarea bisa berisi baris baru atau spasi ganda.
			// Bandingkan versi yang spasinya sudah dinormalkan.
			if (preg_match('/\s/', $q))
			{
				$address_expr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(full_address, CONCAT(CHAR(13), CHAR(10)), ' '), CHAR(13), ' '), CHAR(10), ' '), CHAR(9), ' '), '  ', ' '), '  ', ' ')";
				$address_like = $this->db->escape_like_str($this->_normalize_text($q));
				$this->db->or_where($address_expr." LIKE '%".$address_like."%' ESCAPE '!'", NULL, FALSE);
			}

			// Nomor WhatsApp disimpan apa adanya (08..., 62..., spasi/tanda
			// hubung). Normalisasi kolom agar pencarian tetap cocok.
			$phone_digits = $this->_digits($q);
			if ($phone_digits !== ''
				&& strlen($phone_digits) >= 3
				&& preg_match('/^[0-9+\-\s().]+$/', $q))
			{
				$wa_expr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(whatsapp_number, ' ', ''), '-', ''), '+', ''), '.', ''), '(', ''), ')', '')";
				foreach ($this->_phone_variants($phone_digits) as $variant)
				{
					$like = $this->db->escape('%'.$variant.'%');
					$this->db->or_where($wa_expr.' LIKE '.$like, NULL, FALSE);
				}
			}

			$this->db->group_end();
		}
	}

	/**
	 * Rank kecocokan saran: 0 exact, 1 prefix, 2 contains, NULL tidak cocok.
	 *
	 * @param string $type
	 * @param string $keyword
	 * @param string $q
	 * @return int|null
	 */
	private function _suggestion_rank($type, $keyword, $q)
	{
		$keyword_norm = $this->_normalize_text($keyword);
		$q_norm       = $this->_normalize_text($q);
		if ($keyword_norm === '' || $q_norm === '')
		{
			return NULL;
		}

		if ($type === 'wa')
		{
			$keyword_digits = $this->_digits($keyword_norm);
			$query_digits   = $this->_digits($q_norm);
			if ($keyword_digits === ''
				|| $query_digits === ''
				|| strlen($query_digits) < 3
				|| ! preg_match('/^[0-9+\-\s().]+$/', $q_norm))
			{
				return NULL;
			}

			$best = NULL;
			foreach ($this->_phone_variants($query_digits) as $variant)
			{
				$rank = $this->_text_rank($keyword_digits, $variant);
				if ($rank !== NULL && ($best === NULL || $rank < $best))
				{
					$best = $rank;
				}
			}
			return $best;
		}

		$variants = array($q_norm);
		if ($type === 'rt_rw')
		{
			$stripped = preg_replace('/^(rt|rw)\.?\s*(\d.*)$/i', '$2', $q_norm);
			if ($stripped !== NULL && $stripped !== $q_norm && $stripped !== '')
			{
				$variants[] = $stripped;
			}
		}
		elseif ($type === 'block')
		{
			$stripped = preg_replace('/^(blok|blk)(?:\.|\s)+([a-z0-9].*)$/i', '$2', $q_norm);
			if ($stripped !== NULL && $stripped !== $q_norm && $stripped !== '')
			{
				$variants[] = $stripped;
			}
		}

		$best = NULL;
		foreach ($variants as $variant)
		{
			$rank = $this->_text_rank($keyword_norm, $variant);
			if ($rank !== NULL && ($best === NULL || $rank < $best))
			{
				$best = $rank;
			}
		}
		return $best;
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 * @return int|null
	 */
	private function _text_rank($haystack, $needle)
	{
		$haystack = $this->_strtolower($this->_normalize_text($haystack));
		$needle   = $this->_strtolower($this->_normalize_text($needle));
		if ($needle === '')
		{
			return NULL;
		}

		if ($haystack === $needle)
		{
			return 0;
		}
		if ($this->_strpos($haystack, $needle) === 0)
		{
			return 1;
		}
		return ($this->_strpos($haystack, $needle) !== FALSE) ? 2 : NULL;
	}

	/**
	 * Potong teks panjang di sekitar kecocokan tanpa memutus kata.
	 *
	 * @param string $text
	 * @param string $needle
	 * @param int    $max
	 * @return array array($keyword, $label)
	 */
	private function _excerpt($text, $needle, $max = 80)
	{
		$text       = $this->_normalize_text($text);
		$text_len   = $this->_strlen($text);
		$needle_len = $this->_strlen($needle);
		$pos        = $this->_stripos($text, $needle);
		if ($pos === FALSE)
		{
			$pos = 0;
		}

		$start = max(0, $pos - (int) floor($max / 3));
		if ($start > 0)
		{
			// Mulai dari awal kata, asalkan kata kunci tetap masuk jendela.
			$before = $this->_substr($text, 0, $start);
			$space  = function_exists('mb_strrpos')
				? mb_strrpos($before, ' ', 0, 'UTF-8')
				: strrpos($before, ' ');
			if ($space !== FALSE && ($space + 1 + $max) >= ($pos + $needle_len))
			{
				$start = $space + 1;
			}
		}

		$part  = trim($this->_substr($text, $start, $max));
		$more  = ($start + $max) < $text_len;

		if ($more)
		{
			// Potong di batas kata, tetapi jangan sampai kata kunci ikut terbuang.
			$space      = function_exists('mb_strrpos')
				? mb_strrpos($part, ' ', 0, 'UTF-8')
				: strrpos($part, ' ');
			$needle_end = ($pos + $needle_len) - $start;
			if ($space !== FALSE && $space >= (int) floor($max * 0.6) && $space >= $needle_end)
			{
				$part = rtrim($this->_substr($part, 0, $space));
			}
		}

		$label = ($start > 0 ? '…' : '').$part.($more ? '…' : '');

		return array($part, $label);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	private function _normalize_text($value)
	{
		$value = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', (string) $value);
		$value = preg_replace('/\s+/', ' ', $value);
		return trim($value);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	private function _digits($value)
	{
		return preg_replace('/\D+/', '', (string) $value);
	}

	/**
	 * Variasi nomor Indonesia: 08..., 628..., atau 8....
	 *
	 * @param string $value
	 * @return array
	 */
	private function _phone_variants($value)
	{
		$digits = $this->_digits($value);
		if ($digits === '')
		{
			return array();
		}

		$variants = array($digits);
		if (strpos($digits, '62') === 0 && strlen($digits) > 2)
		{
			$variants[] = '0'.substr($digits, 2);
		}
		elseif (strpos($digits, '0') === 0 && strlen($digits) > 1)
		{
			$variants[] = '62'.substr($digits, 1);
		}
		elseif (strpos($digits, '8') === 0)
		{
			$variants[] = '0'.$digits;
			$variants[] = '62'.$digits;
		}

		return array_values(array_unique($variants));
	}

	private function _strlen($value)
	{
		return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
	}

	private function _strtolower($value)
	{
		return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
	}

	private function _strpos($haystack, $needle)
	{
		return function_exists('mb_strpos') ? mb_strpos($haystack, $needle, 0, 'UTF-8') : strpos($haystack, $needle);
	}

	private function _stripos($haystack, $needle)
	{
		return function_exists('mb_stripos') ? mb_stripos($haystack, $needle, 0, 'UTF-8') : stripos($haystack, $needle);
	}

	private function _substr($value, $start, $length = NULL)
	{
		if (function_exists('mb_substr'))
		{
			return mb_substr($value, $start, $length, 'UTF-8');
		}
		return ($length === NULL) ? substr($value, $start) : substr($value, $start, $length);
	}

	private function _next_code_number()
	{
		$row = $this->db->select(
				"MAX(CAST(SUBSTRING(customer_code, 4) AS UNSIGNED)) AS max_no",
				FALSE
			)
			->get('customers')->row_array();

		return ((int) ($row['max_no'] ?? 0)) + 1;
	}
}
