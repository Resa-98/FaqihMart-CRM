<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin: Daftar Pengiriman.
 *
 * Admin menyusun daftar tujuan pengiriman per tanggal & sesi, memilih
 * pelanggan dari CRM, mengatur urutan, lalu mempublikasikan tautan akses
 * untuk driver.
 */
class Deliveries extends MY_Controller
{
	/** Status daftar yang masih boleh diubah tujuannya. */
	private $editable_statuses = array('draft', 'published');

	public function __construct()
	{
		parent::__construct();
		$this->load->model('delivery_model');
		$this->load->model('customer_model');
		$this->load->helper(array('delivery', 'maps'));
	}

	// ---------------------------------------------------------------------
	// Dashboard & daftar
	// ---------------------------------------------------------------------

	public function index()
	{
		$date    = $this->_get_date();
		$filters = array(
			'date'    => $date,
			'session' => $this->_session_filter(),
			'status'  => $this->_status_filter(),
		);

		$data = array(
			'page_title' => 'Daftar Pengiriman',
			'stats'      => $this->delivery_model->dashboard_stats($date),
			'filters'    => $filters,
			'lists'      => $this->delivery_model->get_lists($filters),
			'today'      => date('Y-m-d'),
		);

		$this->render('deliveries/index', $data);
	}

	public function create()
	{
		$data = array(
			'page_title' => 'Buat Daftar Pengiriman',
			'list'       => NULL,
			'form_action' => site_url('deliveries/store'),
		);
		$this->render('deliveries/form', $data);
	}

	public function store()
	{
		$this->_require_post();
		$this->_set_rules();

		if ($this->form_validation->run() === FALSE)
		{
			$this->render('deliveries/form', array(
				'page_title'  => 'Buat Daftar Pengiriman',
				'list'        => NULL,
				'form_action' => site_url('deliveries/store'),
			));
			return;
		}

		$id = $this->delivery_model->create_list(array(
			'delivery_date' => $this->input->post('delivery_date'),
			'session'       => $this->input->post('session'),
			'title'         => $this->_nullable('title'),
			'status'        => 'draft',
			'created_by'    => (int) $this->admin['id'],
		));

		if ($id === FALSE)
		{
			$this->session->set_flashdata('error', 'Gagal membuat daftar pengiriman.');
			redirect('deliveries/create');
		}

		$this->session->set_flashdata('success', 'Daftar pengiriman dibuat. Tambahkan tujuan di bawah.');
		redirect('deliveries/'.$id);
	}

	public function show($id)
	{
		$list = $this->_list_or_404($id);
		$items = $this->delivery_model->get_items($list['id']);

		$q         = trim((string) $this->input->get('q', TRUE));
		$customers = array();
		if ($q !== '')
		{
			$customers = $this->customer_model->get_all(
				array('q' => $q, 'status' => '', 'view' => 'active'),
				25,
				0
			);
		}

		$raw_token = $this->delivery_model->reveal_token($list);
		$editable  = $this->_is_editable($list);

		$data = array(
			'page_title'       => 'Daftar Pengiriman',
			'list'             => $list,
			'items'            => $items,
			'progress'         => $this->delivery_model->progress($list['id']),
			'editable'         => $editable,
			'customer_suggest' => $editable,
			'q'                => $q,
			'customers'        => $customers,
			'driver_url'       => ($raw_token !== NULL) ? site_url('d/'.$raw_token) : NULL,
		);

		$this->render('deliveries/show', $data);
	}

	public function update($id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar yang sudah selesai/dibatalkan tidak dapat diubah.');
			redirect('deliveries/'.$list['id']);
		}

		$this->_set_rules();
		if ($this->form_validation->run() === FALSE)
		{
			$this->render('deliveries/form', array(
				'page_title'  => 'Edit Daftar Pengiriman',
				'list'        => $list,
				'form_action' => site_url('deliveries/update/'.$list['id']),
			));
			return;
		}

		$this->delivery_model->update_list($list['id'], array(
			'delivery_date' => $this->input->post('delivery_date'),
			'session'       => $this->input->post('session'),
			'title'         => $this->_nullable('title'),
		));

		$this->session->set_flashdata('success', 'Daftar pengiriman diperbarui.');
		redirect('deliveries/'.$list['id']);
	}

	public function edit($id)
	{
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar yang sudah selesai/dibatalkan tidak dapat diubah.');
			redirect('deliveries/'.$list['id']);
		}

		$this->render('deliveries/form', array(
			'page_title'  => 'Edit Daftar Pengiriman',
			'list'        => $list,
			'form_action' => site_url('deliveries/update/'.$list['id']),
		));
	}

	// ---------------------------------------------------------------------
	// Tujuan
	// ---------------------------------------------------------------------

	public function addItem($id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar ini tidak dapat diubah lagi.');
			redirect('deliveries/'.$list['id']);
		}

		$customer_id = (int) $this->input->post('customer_id');
		$customer    = $this->customer_model->get($customer_id);
		if ( ! $customer)
		{
			$this->session->set_flashdata('error', 'Pelanggan tidak ditemukan.');
			redirect('deliveries/'.$list['id']);
		}

		if ($this->delivery_model->item_exists($list['id'], $customer_id))
		{
			$this->session->set_flashdata('error', $customer['full_name'].' sudah ada di daftar ini. Pelanggan yang sama masih boleh ditambahkan ke daftar/sesi lain.');
			redirect('deliveries/'.$list['id']);
		}

		$this->delivery_model->add_item($list['id'], $customer_id, $this->input->post('note'));
		$this->session->set_flashdata('success', $customer['full_name'].' ditambahkan ke daftar.');
		redirect('deliveries/'.$list['id'].'#tujuan');
	}

	public function removeItem($id, $item_id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar ini tidak dapat diubah lagi.');
			redirect('deliveries/'.$list['id']);
		}

		$item = $this->delivery_model->get_item($list['id'], $item_id);
		if ( ! $item)
		{
			show_404();
		}

		if ($this->delivery_model->remove_item($list['id'], $item_id) && ! empty($item['receipt_file']))
		{
			$this->_delete_receipt($item['receipt_file']);
		}

		$this->session->set_flashdata('success', 'Tujuan dihapus dari daftar.');
		redirect('deliveries/'.$list['id'].'#tujuan');
	}

	public function updateItemNote($id, $item_id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar ini tidak dapat diubah lagi.');
			redirect('deliveries/'.$list['id']);
		}

		if ( ! $this->delivery_model->get_item($list['id'], $item_id))
		{
			show_404();
		}

		$this->delivery_model->update_item_note($list['id'], $item_id, $this->input->post('note'));
		$this->session->set_flashdata('success', 'Catatan tujuan disimpan.');
		redirect('deliveries/'.$list['id'].'#tujuan');
	}

	public function updateItemOrder($id, $item_id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar ini tidak dapat diubah lagi.');
			redirect('deliveries/'.$list['id']);
		}

		$item = $this->delivery_model->get_item($list['id'], $item_id);
		if ( ! $item)
		{
			show_404();
		}

		$this->form_validation->set_rules('order_details', 'Rincian pesanan', 'trim|max_length[2000]');
		$this->form_validation->set_message('max_length', 'Rincian pesanan maksimal 2000 karakter.');
		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('error', strip_tags($this->form_validation->error_string()));
			redirect('deliveries/'.$list['id'].'#item-'.(int) $item['id']);
		}

		$details = trim((string) $this->input->post('order_details'));
		$data    = array(
			'order_details' => ($details === '') ? NULL : $details,
		);

		$old_receipt = isset($item['receipt_file']) ? (string) $item['receipt_file'] : '';
		$new_receipt = NULL;
		$upload_error = NULL;

		$file_error = isset($_FILES['receipt']['error']) ? (int) $_FILES['receipt']['error'] : UPLOAD_ERR_NO_FILE;
		$file_name  = isset($_FILES['receipt']['name']) ? (string) $_FILES['receipt']['name'] : '';
		$attempted_upload = ($file_error !== UPLOAD_ERR_NO_FILE && $file_name !== '');
		$remove_receipt   = ($this->input->post('remove_receipt') === '1');

		if ($attempted_upload)
		{
			$new_receipt = $this->_upload_receipt($upload_error);
			if ($new_receipt !== NULL)
			{
				$data['receipt_file'] = $new_receipt;
			}
		}
		elseif ($remove_receipt)
		{
			$data['receipt_file'] = NULL;
		}

		if ( ! $this->delivery_model->update_item_order($list['id'], $item['id'], $data))
		{
			if ($new_receipt !== NULL)
			{
				$this->_delete_receipt($new_receipt);
			}

			$this->session->set_flashdata('error', 'Gagal menyimpan rincian pesanan.');
			redirect('deliveries/'.$list['id'].'#item-'.(int) $item['id']);
		}

		if (($new_receipt !== NULL || ( ! $attempted_upload && $remove_receipt))
			&& $old_receipt !== '' && $old_receipt !== $new_receipt)
		{
			$this->_delete_receipt($old_receipt);
		}

		if ($upload_error !== NULL)
		{
			log_message('error', 'Upload struk gagal (item '.$item['id'].'): '.$upload_error);
			$this->session->set_flashdata('error', 'Rincian teks tersimpan, tetapi struk gagal diunggah. Gunakan JPG/PNG maksimal 5 MB.');
			redirect('deliveries/'.$list['id'].'#item-'.(int) $item['id']);
		}

		$this->session->set_flashdata('success', 'Rincian pesanan disimpan.');
		redirect('deliveries/'.$list['id'].'#item-'.(int) $item['id']);
	}

	public function itemReceipt($id, $item_id)
	{
		$list = $this->_list_or_404($id);
		$item = $this->delivery_model->get_item($list['id'], $item_id);

		if ( ! $item || empty($item['receipt_file']))
		{
			show_404();
		}

		$this->_send_receipt($item['receipt_file']);
	}

	public function moveItem($id, $item_id, $direction)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		if ( ! $this->_is_editable($list))
		{
			$this->session->set_flashdata('error', 'Daftar ini tidak dapat diubah lagi.');
			redirect('deliveries/'.$list['id']);
		}

		if ( ! in_array($direction, array('up', 'down'), TRUE))
		{
			show_404();
		}

		$this->delivery_model->move_item($list['id'], $item_id, $direction);
		redirect('deliveries/'.$list['id'].'#tujuan');
	}

	// ---------------------------------------------------------------------
	// Status & tautan driver
	// ---------------------------------------------------------------------

	public function setStatus($id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		$status = (string) $this->input->post('status');
		if ( ! in_array($status, array('draft', 'published', 'completed', 'cancelled'), TRUE))
		{
			show_404();
		}

		$update = array('status' => $status);

		if ($status === 'published')
		{
			$update['published_at'] = date('Y-m-d H:i:s');
			$this->delivery_model->update_list($list['id'], $update);

			// Sediakan tautan driver bila belum ada token aktif.
			if (empty($list['token_hash']) || ! empty($list['token_revoked_at']))
			{
				$this->delivery_model->generate_token($list['id']);
			}
		}
		else
		{
			$this->delivery_model->update_list($list['id'], $update);
		}

		$this->session->set_flashdata('success', 'Status daftar: '.delivery_status_label($status).'.');
		redirect('deliveries/'.$list['id']);
	}

	public function regenerate($id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		$this->delivery_model->generate_token($list['id']);

		// Tautan hanya berguna bila daftar dipublikasikan.
		if ( ! in_array($list['status'], array('published', 'completed'), TRUE))
		{
			$this->delivery_model->update_list($list['id'], array(
				'status'       => 'published',
				'published_at' => date('Y-m-d H:i:s'),
			));
		}

		$this->session->set_flashdata('success', 'Tautan driver baru dibuat. Tautan lama tidak berlaku lagi.');
		redirect('deliveries/'.$list['id']);
	}

	public function revoke($id)
	{
		$this->_require_post();
		$list = $this->_list_or_404($id);

		$this->delivery_model->revoke_token($list['id']);
		$this->session->set_flashdata('success', 'Akses tautan driver dicabut.');
		redirect('deliveries/'.$list['id']);
	}

	// ---------------------------------------------------------------------

	private function _set_rules()
	{
		$this->form_validation->set_rules('delivery_date', 'Tanggal pengiriman', 'trim|required|callback__valid_date');
		$this->form_validation->set_rules('session', 'Sesi', 'trim|required|in_list[pagi,sore]');
		$this->form_validation->set_rules('title', 'Judul', 'trim|max_length[150]');
	}

	public function _valid_date($value)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return TRUE;
		}

		$date = DateTime::createFromFormat('Y-m-d', $value);
		if ( ! $date || $date->format('Y-m-d') !== $value)
		{
			$this->form_validation->set_message('_valid_date', 'Format tanggal harus YYYY-MM-DD.');
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	private function _list_or_404($id)
	{
		$list = $this->delivery_model->get_list($id);
		if ( ! $list)
		{
			show_404();
		}
		return $list;
	}

	private function _is_editable(array $list)
	{
		return in_array($list['status'], $this->editable_statuses, TRUE);
	}

	private function _nullable($field)
	{
		$value = trim((string) $this->input->post($field));
		return ($value === '') ? NULL : $value;
	}

	/**
	 * Unggah foto struk. Mengembalikan nama file bila berhasil, NULL bila gagal.
	 *
	 * @param string $error Pesan kesalahan (diisi bila gagal).
	 * @return string|null
	 */
	private function _upload_receipt(&$error)
	{
		$error = NULL;
		$dir   = delivery_receipts_dir();

		if ( ! is_dir($dir))
		{
			@mkdir($dir, 0755, TRUE);
		}

		if ( ! is_dir($dir) || ! is_writable($dir))
		{
			$error = 'Folder penyimpanan struk tidak dapat ditulis.';
			return NULL;
		}

		$config = array(
			'upload_path'     => $dir,
			'allowed_types'   => 'jpg|jpeg|png',
			'max_size'        => 5120,
			'encrypt_name'    => TRUE,
			'file_ext_tolower' => TRUE,
			'remove_spaces'   => TRUE,
			'detect_mime'     => TRUE,
		);

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('receipt'))
		{
			$error = trim(strip_tags($this->upload->display_errors('', '')));
			return NULL;
		}

		$upload_data = $this->upload->data();
		$info = @getimagesize($upload_data['full_path']);
		$ext  = strtolower(ltrim((string) $upload_data['file_ext'], '.'));
		$types = array(
			IMAGETYPE_JPEG => array('jpg', 'jpeg'),
			IMAGETYPE_PNG  => array('png'),
		);

		if ($info === FALSE
			|| ! isset($types[$info[2]])
			|| ! in_array($ext, $types[$info[2]], TRUE))
		{
			@unlink($upload_data['full_path']);
			$error = 'File bukan gambar JPG/PNG yang valid.';
			return NULL;
		}

		return $upload_data['file_name'];
	}

	private function _delete_receipt($filename)
	{
		$path = delivery_receipt_path($filename);
		if ($path !== NULL && is_file($path))
		{
			@unlink($path);
		}
	}

	private function _send_receipt($filename)
	{
		$file = delivery_receipt_file($filename);
		if ($file === NULL)
		{
			show_404();
		}

		$content = @file_get_contents($file['path']);
		if ($content === FALSE)
		{
			show_404();
		}

		$this->output
			->set_header('Content-Type: '.$file['mime'])
			->set_header('Content-Disposition: inline; filename="'.$file['download_name'].'"')
			->set_header('Content-Length: '.$file['size'])
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: private, max-age=0, must-revalidate')
			->set_header('X-Robots-Tag: noindex, nofollow')
			->set_output($content);
	}

	private function _get_date()
	{
		$date = trim((string) $this->input->get('date', TRUE));
		if ($date === '')
		{
			return date('Y-m-d');
		}

		$d = DateTime::createFromFormat('Y-m-d', $date);
		if ( ! $d || $d->format('Y-m-d') !== $date)
		{
			return date('Y-m-d');
		}
		return $date;
	}

	private function _session_filter()
	{
		$session = (string) $this->input->get('session', TRUE);
		return in_array($session, array('pagi', 'sore'), TRUE) ? $session : '';
	}

	private function _status_filter()
	{
		$status = (string) $this->input->get('status', TRUE);
		return in_array($status, array('draft', 'published', 'completed', 'cancelled'), TRUE) ? $status : '';
	}

	private function _require_post()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}
	}
}
