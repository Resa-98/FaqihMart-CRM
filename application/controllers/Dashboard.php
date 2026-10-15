<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	public function index()
	{
		$this->load->model('customer_model');
		$this->load->helper('maps');

		$data = array(
			'page_title' => 'Dashboard',
			'stats'      => $this->customer_model->get_stats(),
			'recent'     => $this->customer_model->get_recent(5),
		);

		$this->render('dashboard/index', $data);
	}
}
