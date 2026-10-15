<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Autentikasi admin. Tidak ada registrasi publik.
 */
class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin_model');
		$this->load->helper('form');
	}

	public function login()
	{
		if ($this->session->userdata('admin_id'))
		{
			redirect('dashboard');
		}

		$data['error'] = NULL;

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('username', 'Username', 'trim|required|max_length[50]');
			$this->form_validation->set_rules('password', 'Password', 'required|max_length[100]');

			if ($this->form_validation->run() === TRUE)
			{
				$username = (string) $this->input->post('username', TRUE);
				$password = (string) $this->input->post('password');
				$admin = $this->admin_model->get_by_username($username);

				if ($admin && password_verify($password, $admin['password_hash']))
				{
					$this->session->sess_regenerate(TRUE);
					$this->session->set_userdata(array(
						'admin_id'   => (int) $admin['id'],
						'admin_name' => $admin['name'],
					));
					$this->session->set_flashdata('success', 'Selamat datang, '.$admin['name'].'.');
					redirect('dashboard');
				}

				// Pesan sengaja umum agar tidak membocorkan username valid.
				$data['error'] = 'Username atau password salah.';
			}
		}

		$data['page_title'] = 'Login';
		$this->load->view('auth/login', $data);
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}

	public function changePassword()
	{
		if ( ! $this->session->userdata('admin_id'))
		{
			redirect('login');
		}

		$data['error'] = NULL;

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('current_password', 'Password saat ini', 'required');
			$this->form_validation->set_rules('new_password', 'Password baru', 'required|min_length[8]|max_length[100]');
			$this->form_validation->set_rules('confirm_password', 'Konfirmasi password', 'required|matches[new_password]');

			if ($this->form_validation->run() === TRUE)
			{
				$admin = $this->admin_model->get((int) $this->session->userdata('admin_id'));
				if ($admin && password_verify((string) $this->input->post('current_password'), $admin['password_hash']))
				{
					$this->admin_model->update_password(
						$admin['id'],
						password_hash((string) $this->input->post('new_password'), PASSWORD_BCRYPT)
					);
					$this->session->set_flashdata('success', 'Password berhasil diperbarui.');
					redirect('change-password');
				}

				$data['error'] = 'Password saat ini tidak sesuai.';
			}
		}

		$data['admin'] = $this->admin_model->get((int) $this->session->userdata('admin_id'));
		$data['page_title'] = 'Ganti Password';
		$this->load->view('layouts/header', $data);
		$this->load->view('auth/change_password', $data);
		$this->load->view('layouts/footer', $data);
	}
}
