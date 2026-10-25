<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller untuk seluruh halaman internal CRM.
 *
 * Memastikan hanya admin yang sudah login dapat mengaksesnya. Redirect ke
 * halaman login bila sesi tidak ada atau admin sudah tidak valid.
 */
class MY_Controller extends CI_Controller
{
	/** @var array|null Data admin yang sedang login. */
	protected $admin = NULL;

	public function __construct()
	{
		parent::__construct();

		$admin_id = $this->session->userdata('admin_id');
		if (empty($admin_id))
		{
			redirect('login');
			exit;
		}

		$this->load->model('admin_model');
		$this->admin = $this->admin_model->get((int) $admin_id);

		if (empty($this->admin))
		{
			$this->session->sess_destroy();
			redirect('login');
			exit;
		}

		if ( ! empty($this->admin['must_change_password']))
		{
			$class = strtolower($this->router->fetch_class());
			$method = strtolower($this->router->fetch_method());
			if ( ! ($class === 'auth' && $method === 'changepassword'))
			{
				$this->session->set_flashdata('error', 'Wajib ganti password default sebelum melanjutkan.');
				redirect('change-password');
				exit;
			}
		}
	}

	/**
	 * Render halaman dengan layout header + sidebar + footer.
	 *
	 * @param string $view Nama view di dalam application/views.
	 * @param array  $data Data yang dikirim ke view.
	 * @return void
	 */
	protected function render($view, array $data = array())
	{
		$data['admin'] = $this->admin;
		$this->load->view('layouts/header', $data);
		$this->load->view($view, $data);
		$this->load->view('layouts/footer', $data);
	}
}
