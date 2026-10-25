<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model modul Daftar Pengiriman.
 *
 * Mengelola daftar pengiriman (delivery_lists) dan tujuannya
 * (delivery_items) yang merujuk ke tabel customers yang sudah ada.
 */
class Delivery_model extends CI_Model
{
	const SESSION_PAGI = 'pagi';
	const SESSION_SORE = 'sore';

	const STATUS_DRAFT     = 'draft';
	const STATUS_PUBLISHED = 'published';
	const STATUS_COMPLETED = 'completed';
	const STATUS_CANCELLED = 'cancelled';

	const ITEM_PENDING     = 'pending';
	const ITEM_IN_PROGRESS = 'in_progress';
	const ITEM_DONE        = 'done';

	public function __construct()
	{
		parent::__construct();
		// Untuk mendekripsi tautan driver saat admin ingin menyalinnya ulang.
		$this->load->library('encryption');
	}

	// ---------------------------------------------------------------------
	// Daftar pengiriman
	// ---------------------------------------------------------------------

	/**
	 * Daftar pengiriman beserta jumlah tujuan dan yang selesai.
	 *
	 * @param array    $filters date (Y-m-d), session, status
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array
	 */
	public function get_lists(array $filters = array(), $limit = NULL, $offset = NULL)
	{
		$this->db->select(
				'l.*,'
				.' (SELECT COUNT(*) FROM delivery_items i WHERE i.delivery_list_id = l.id) AS total_items,'
				.' (SELECT COUNT(*) FROM delivery_items i WHERE i.delivery_list_id = l.id AND i.status = "done") AS done_items',
				FALSE
			)
			->from('delivery_lists l');

		$this->_apply_list_filters($filters);

		$this->db->order_by('l.delivery_date', 'DESC')
			->order_by('l.session', 'ASC')
			->order_by('l.id', 'DESC');

		return $this->db->get('', $limit, $offset)->result_array();
	}

	/**
	 * @param array $filters
	 * @return int
	 */
	public function count_lists(array $filters = array())
	{
		$this->db->from('delivery_lists l');
		$this->_apply_list_filters($filters);
		return (int) $this->db->count_all_results();
	}

	/**
	 * @param int $id
	 * @return array|null
	 */
	public function get_list($id)
	{
		return $this->db->get_where('delivery_lists', array('id' => (int) $id))->row_array();
	}

	/**
	 * @param array $data
	 * @return int|false
	 */
	public function create_list(array $data)
	{
		if ($this->db->insert('delivery_lists', $data))
		{
			return (int) $this->db->insert_id();
		}
		return FALSE;
	}

	/**
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update_list($id, array $data)
	{
		return $this->db->where('id', (int) $id)->update('delivery_lists', $data);
	}

	/**
	 * @param int    $id
	 * @param string $status
	 * @return bool
	 */
	public function set_status($id, $status)
	{
		return $this->update_list($id, array('status' => $status));
	}

	// ---------------------------------------------------------------------
	// Token akses driver
	// ---------------------------------------------------------------------

	/**
	 * Buat token acak baru, simpan hash (untuk pencarian) + bentuk
	 * terenkripsi (agar admin bisa menyalin ulang tautan).
	 *
	 * @param int $id
	 * @return string Token mentah (64 karakter hex).
	 */
	public function generate_token($id)
	{
		$raw = bin2hex(random_bytes(32));

		$this->update_list($id, array(
			'token_hash'       => hash('sha256', $raw),
			'token_enc'        => base64_encode($this->encryption->encrypt($raw)),
			'token_created_at' => date('Y-m-d H:i:s'),
			'token_revoked_at' => NULL,
		));

		return $raw;
	}

	/**
	 * Cabut akses: kosongkan token sehingga tautan lama langsung mati.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function revoke_token($id)
	{
		return $this->update_list($id, array(
			'token_hash'       => NULL,
			'token_enc'        => NULL,
			'token_revoked_at' => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Ambil tautan mentah dari bentuk terenkripsi (untuk ditampilkan ke admin).
	 *
	 * @param array $list
	 * @return string|null
	 */
	public function reveal_token(array $list)
	{
		if (empty($list['token_enc']) || ! empty($list['token_revoked_at']))
		{
			return NULL;
		}

		$raw = $this->encryption->decrypt(base64_decode($list['token_enc']));
		return ($raw === FALSE || $raw === '') ? NULL : $raw;
	}

	/**
	 * Cari daftar berdasarkan token mentah. Hanya daftar yang sudah
	 * dipublikasikan (atau selesai) dan belum dicabut yang dikembalikan.
	 *
	 * @param string $raw
	 * @return array|null
	 */
	public function find_by_token($raw)
	{
		$raw = (string) $raw;
		if ($raw === '')
		{
			return NULL;
		}

		return $this->db->where('token_hash', hash('sha256', $raw))
			->where('token_revoked_at IS NULL', NULL, FALSE)
			->where_in('status', array(self::STATUS_PUBLISHED, self::STATUS_COMPLETED))
			->get('delivery_lists')->row_array();
	}

	// ---------------------------------------------------------------------
	// Tujuan pengiriman
	// ---------------------------------------------------------------------

	/**
	 * Tujuan sebuah daftar, urut sesuai sort_order, digabung data pelanggan.
	 * LEFT JOIN agar tujuan tetap tampil walau pelanggan diarsipkan.
	 *
	 * @param int $list_id
	 * @return array
	 */
	public function get_items($list_id)
	{
		$customer_cols = array(
			'c.customer_code', 'c.full_name', 'c.whatsapp_number', 'c.housing_name',
			'c.full_address', 'c.block_number', 'c.rt_rw', 'c.landmark',
			'c.latitude', 'c.longitude', 'c.google_maps_url', 'c.delivery_notes',
			'c.location_status', 'c.archived_at',
		);

		return $this->db->select('i.*, '.implode(', ', $customer_cols), FALSE)
			->from('delivery_items i')
			->join('customers c', 'c.id = i.customer_id', 'left')
			->where('i.delivery_list_id', (int) $list_id)
			->order_by('i.sort_order', 'ASC')
			->order_by('i.id', 'ASC')
			->get()->result_array();
	}

	/**
	 * @param int $list_id
	 * @param int $item_id
	 * @return array|null
	 */
	public function get_item($list_id, $item_id)
	{
		return $this->db->get_where('delivery_items', array(
			'id'               => (int) $item_id,
			'delivery_list_id' => (int) $list_id,
		))->row_array();
	}

	/**
	 * @param int $list_id
	 * @param int $customer_id
	 * @return bool
	 */
	public function item_exists($list_id, $customer_id)
	{
		return $this->db->where('delivery_list_id', (int) $list_id)
			->where('customer_id', (int) $customer_id)
			->count_all_results('delivery_items') > 0;
	}

	/**
	 * Tambahkan pelanggan sebagai tujuan (diletakkan di urutan terakhir).
	 *
	 * @param int         $list_id
	 * @param int         $customer_id
	 * @param string|null $note
	 * @return int|false
	 */
	public function add_item($list_id, $customer_id, $note = NULL)
	{
		$data = array(
			'delivery_list_id' => (int) $list_id,
			'customer_id'      => (int) $customer_id,
			'sort_order'       => $this->_next_sort_order($list_id),
			'note'             => ($note === NULL || $note === '') ? NULL : $note,
			'status'           => self::ITEM_PENDING,
		);

		if ($this->db->insert('delivery_items', $data))
		{
			return (int) $this->db->insert_id();
		}
		return FALSE;
	}

	/**
	 * @param int $list_id
	 * @param int $item_id
	 * @return bool
	 */
	public function remove_item($list_id, $item_id)
	{
		return $this->db->where('id', (int) $item_id)
			->where('delivery_list_id', (int) $list_id)
			->delete('delivery_items');
	}

	/**
	 * Ubah catatan khusus satu tujuan.
	 *
	 * @param int         $list_id
	 * @param int         $item_id
	 * @param string|null $note
	 * @return bool
	 */
	public function update_item_note($list_id, $item_id, $note)
	{
		return $this->db->where('id', (int) $item_id)
			->where('delivery_list_id', (int) $list_id)
			->update('delivery_items', array(
				'note' => ($note === NULL || $note === '') ? NULL : $note,
			));
	}

	/**
	 * Perbarui rincian pesanan dan/atau nama file struk satu tujuan.
	 *
	 * @param int   $list_id
	 * @param int   $item_id
	 * @param array $data Kolom yang boleh diubah: order_details, receipt_file.
	 * @return bool
	 */
	public function update_item_order($list_id, $item_id, array $data)
	{
		$allowed = array();
		foreach (array('order_details', 'receipt_file') as $field)
		{
			if (array_key_exists($field, $data))
			{
				$allowed[$field] = $data[$field];
			}
		}

		if (empty($allowed))
		{
			return FALSE;
		}

		return $this->db->where('id', (int) $item_id)
			->where('delivery_list_id', (int) $list_id)
			->update('delivery_items', $allowed);
	}

	/**
	 * Geser tujuan satu langkah ke atas/bawah dengan menukar sort_order.
	 *
	 * @param int    $list_id
	 * @param int    $item_id
	 * @param string $direction up|down
	 * @return bool
	 */
	public function move_item($list_id, $item_id, $direction)
	{
		$items = $this->get_items($list_id);
		$count = count($items);

		for ($i = 0; $i < $count; $i++)
		{
			if ((int) $items[$i]['id'] !== (int) $item_id)
			{
				continue;
			}

			$target = ($direction === 'up') ? $i - 1 : $i + 1;
			if ($target < 0 || $target >= $count)
			{
				return FALSE;
			}

			$this->db->trans_start();
			$this->db->where('id', (int) $items[$i]['id'])
				->update('delivery_items', array('sort_order' => (int) $items[$target]['sort_order']));
			$this->db->where('id', (int) $items[$target]['id'])
				->update('delivery_items', array('sort_order' => (int) $items[$i]['sort_order']));
			$this->db->trans_complete();

			return $this->db->trans_status();
		}

		return FALSE;
	}

	/**
	 * Perbarui status satu tujuan (dipakai driver).
	 *
	 * @param int    $item_id
	 * @param string $status
	 * @return bool
	 */
	public function update_item_status($item_id, $status)
	{
		return $this->db->where('id', (int) $item_id)
			->update('delivery_items', array(
				'status'       => $status,
				'completed_at' => ($status === self::ITEM_DONE) ? date('Y-m-d H:i:s') : NULL,
			));
	}

	// ---------------------------------------------------------------------
	// Progres & statistik
	// ---------------------------------------------------------------------

	/**
	 * Ringkasan progres sebuah daftar.
	 *
	 * @param int $list_id
	 * @return array total, done, in_progress, pending
	 */
	public function progress($list_id)
	{
		$row = $this->db->select(
				"COUNT(*) AS total,"
				." SUM(status = 'done') AS done,"
				." SUM(status = 'in_progress') AS in_progress,"
				." SUM(status = 'pending') AS pending",
				FALSE
			)
			->where('delivery_list_id', (int) $list_id)
			->get('delivery_items')->row_array();

		return array(
			'total'       => (int) ($row['total'] ?? 0),
			'done'        => (int) ($row['done'] ?? 0),
			'in_progress' => (int) ($row['in_progress'] ?? 0),
			'pending'     => (int) ($row['pending'] ?? 0),
		);
	}

	/**
	 * Statistik dashboard pengiriman untuk satu tanggal.
	 *
	 * @param string $date Y-m-d
	 * @return array
	 */
	public function dashboard_stats($date)
	{
		$lists = $this->db->select("COUNT(*) AS total, SUM(status = 'published') AS published, SUM(status = 'completed') AS completed", FALSE)
			->where('delivery_date', $date)
			->where('status !=', self::STATUS_CANCELLED)
			->get('delivery_lists')->row_array();

		$items = $this->db->select(
				"COUNT(i.id) AS total,"
				." SUM(l.session = 'pagi') AS pagi,"
				." SUM(l.session = 'sore') AS sore,"
				." SUM(i.status = 'done') AS done,"
				." SUM(i.status != 'done') AS open",
				FALSE
			)
			->from('delivery_lists l')
			->join('delivery_items i', 'i.delivery_list_id = l.id', 'left')
			->where('l.delivery_date', $date)
			->where('l.status !=', self::STATUS_CANCELLED)
			->get()->row_array();

		return array(
			'lists_total'     => (int) ($lists['total'] ?? 0),
			'lists_published' => (int) ($lists['published'] ?? 0),
			'lists_completed' => (int) ($lists['completed'] ?? 0),
			'dest_total'      => (int) ($items['total'] ?? 0),
			'dest_pagi'       => (int) ($items['pagi'] ?? 0),
			'dest_sore'       => (int) ($items['sore'] ?? 0),
			'dest_done'       => (int) ($items['done'] ?? 0),
			'dest_open'       => (int) ($items['open'] ?? 0),
		);
	}

	// ---------------------------------------------------------------------

	private function _next_sort_order($list_id)
	{
		$row = $this->db->select('MAX(sort_order) AS max_sort', FALSE)
			->where('delivery_list_id', (int) $list_id)
			->get('delivery_items')->row_array();

		return ((int) ($row['max_sort'] ?? 0)) + 1;
	}

	private function _apply_list_filters(array $filters)
	{
		if ( ! empty($filters['date']))
		{
			$this->db->where('l.delivery_date', $filters['date']);
		}
		if ( ! empty($filters['session']) && in_array($filters['session'], array(self::SESSION_PAGI, self::SESSION_SORE), TRUE))
		{
			$this->db->where('l.session', $filters['session']);
		}
		if ( ! empty($filters['status']) && in_array($filters['status'], array(self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_COMPLETED, self::STATUS_CANCELLED), TRUE))
		{
			$this->db->where('l.status', $filters['status']);
		}
	}
}
