<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Autentikasi admin. Tidak ada registrasi publik.
 */
class Auth extends CI_Controller
{
	const LOGIN_MAX_ATTEMPTS = 5;
	const LOGIN_WINDOW_MINUTES = 15;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin_model');
		$this->load->helper('form');
		$this->load->database();
	}

	private function _get_client_ip()
	{
		$ip = $this->input->ip_address();
		return $ip ? $ip : '0.0.0.0';
	}

	private function _login_attempts_table_exists()
	{
		if ( ! isset($this->db))
		{
			$this->load->database();
		}

		return method_exists($this->db, 'table_exists') ? $this->db->table_exists('login_attempts') : FALSE;
	}

	private function _is_login_throttled($username)
	{
		if ( ! $this->_login_attempts_table_exists())
		{
			return FALSE;
		}

		$ip = $this->_get_client_ip();
		$since = date('Y-m-d H:i:s', strtotime('-'.self::LOGIN_WINDOW_MINUTES.' minutes'));

		$this->db->where('attempted_at >=', $since);
		$this->db->group_start();
		$this->db->where('username', $username);
		$this->db->or_where('ip_address', $ip);
		$this->db->group_end();
		$this->db->where('success', 0);
		$count = $this->db->count_all_results('login_attempts');

		return $count >= self::LOGIN_MAX_ATTEMPTS;
	}

	private function _log_login_attempt($username, $success)
	{
		if ( ! $this->_login_attempts_table_exists())
		{
			return;
		}

		$this->db->insert('login_attempts', array(
			'username'     => $username,
			'ip_address'   => $this->_get_client_ip(),
			'success'      => $success ? 1 : 0,
			'attempted_at' => date('Y-m-d H:i:s'),
		));
	}

	private function _clear_login_attempts($username)
	{
		if ( ! $this->_login_attempts_table_exists())
		{
			return;
		}

		$ip = $this->_get_client_ip();
		$this->db->group_start()
			->where('username', $username)
			->or_where('ip_address', $ip)
		->group_end()
		->delete('login_attempts');
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

				if ($this->_is_login_throttled($username))
				{
					$this->_log_login_attempt($username, FALSE);
					$data['error'] = 'Terlalu banyak percobaan gagal. Coba lagi dalam '.self::LOGIN_WINDOW_MINUTES.' menit.';
				}
				else
				{
					$admin = $this->admin_model->get_by_username($username);

					if ($admin && password_verify($password, $admin['password_hash']))
					{
						$this->_log_login_attempt($username, TRUE);
						$this->_clear_login_attempts($username);
						$this->session->sess_regenerate(TRUE);
						$this->session->set_userdata(array(
							'admin_id'   => (int) $admin['id'],
							'admin_name' => $admin['name'],
						));
						$this->session->set_flashdata('success', 'Selamat datang, '.$admin['name'].'.');

						if ( ! empty($admin['must_change_password']))
						{
							$this->session->set_flashdata('error', 'Wajib ganti password default sebelum melanjutkan.');
							redirect('change-password');
						}

						redirect('dashboard');
					}

					$this->_log_login_attempt($username, FALSE);
					// Pesan sengaja umum agar tidak membocorkan username valid.
					$data['error'] = 'Username atau password salah.';
				}
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
					$this->db->update('admins', array('must_change_password' => 0), array('id' => (int) $admin['id']));
					$this->session->unset_userdata('must_change_password');
					$this->session->set_flashdata('success', 'Password berhasil diperbarui.');
					redirect('dashboard');
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
