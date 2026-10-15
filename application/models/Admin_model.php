<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model
{
	/**
	 * @param int $id
	 * @return array|null
	 */
	public function get($id)
	{
		return $this->db->get_where('admins', array('id' => (int) $id))->row_array();
	}

	/**
	 * @param string $username
	 * @return array|null
	 */
	public function get_by_username($username)
	{
		return $this->db->get_where('admins', array('username' => $username))->row_array();
	}

	/**
	 * @param int    $id
	 * @param string $hash Hash bcrypt baru.
	 * @return bool
	 */
	public function update_password($id, $hash)
	{
		return $this->db->where('id', (int) $id)->update('admins', array('password_hash' => $hash));
	}
}
