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
				->or_like('landmark', $q)
				->or_like('customer_code', $q)
				->group_end();
		}
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
