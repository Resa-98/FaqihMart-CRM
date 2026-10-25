<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Halaman driver (publik, tanpa login).
 *
 * Akses hanya lewat token acak pada URL. Driver hanya dapat melihat satu
 * daftar yang dibagikan dan memperbarui status tujuan — bukan data pelanggan
 * atau daftar lain.
 */
class Driver extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('delivery_model');
		$this->load->helper(array('delivery', 'maps'));
	}

	public function view($token = '')
	{
		$list = $this->delivery_model->find_by_token($token);

		if ( ! $list)
		{
			$this->_notice();
			return;
		}

		$this->load->view('driver/view', array(
			'list'      => $list,
			'items'     => $this->delivery_model->get_items($list['id']),
			'progress'  => $this->delivery_model->progress($list['id']),
			'token'     => $token,
			'can_update' => ($list['status'] === 'published'),
		));
	}

	public function receipt($token = '', $item_id = 0)
	{
		$list = $this->delivery_model->find_by_token($token);
		if ( ! $list)
		{
			show_404();
		}

		$item = $this->delivery_model->get_item($list['id'], $item_id);
		if ( ! $item || empty($item['receipt_file']))
		{
			show_404();
		}

		$this->_send_receipt($item['receipt_file']);
	}

	public function updateItemStatus($token = '', $item_id = 0)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$list = $this->delivery_model->find_by_token($token);
		if ( ! $list)
		{
			show_404();
		}

		// Status hanya boleh berubah selama daftar masih dipublikasikan.
		if ($list['status'] !== 'published')
		{
			$this->session->set_flashdata('driver_error', 'Daftar ini tidak lagi menerima perubahan status.');
			redirect('d/'.$token);
		}

		$item = $this->delivery_model->get_item($list['id'], $item_id);
		if ( ! $item)
		{
			show_404();
		}

		$status = (string) $this->input->post('status');
		if ( ! in_array($status, array('pending', 'in_progress', 'done'), TRUE))
		{
			show_404();
		}

		$this->delivery_model->update_item_status($item['id'], $status);
		redirect('d/'.$token);
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

	/**
	 * Tampilkan pesan generik (tanpa membocorkan apakah daftar ada).
	 */
	private function _notice()
	{
		$this->output->set_status_header(404);
		$this->load->view('driver/notice');
	}
}
