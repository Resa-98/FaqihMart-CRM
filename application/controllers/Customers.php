<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->helper('maps');
	}

	public function index()
	{
		$filters = $this->_filters();

		$total      = $this->customer_model->count_all($filters);
		$per_page   = self::PER_PAGE;
		$page       = max(1, (int) $this->input->get('page'));
		$total_page = max(1, (int) ceil($total / $per_page));
		$page       = min($page, $total_page);

		$data = array(
			'page_title'  => 'Pelanggan',
			'customers'   => $this->customer_model->get_all($filters, $per_page, ($page - 1) * $per_page),
			'filters'     => $filters,
			'total'       => $total,
			'page'        => $page,
			'total_page'  => $total_page,
		);

		$this->render('customers/index', $data);
	}

	public function create()
	{
		$this->_render_form(NULL);
	}

	public function store()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}
		$this->_save(NULL);
	}

	public function show($id)
	{
		$customer = $this->customer_model->get((int) $id);
		if ( ! $customer)
		{
			show_404();
		}

		$data = array(
			'page_title' => 'Detail Pelanggan',
			'customer'   => $customer,
			'maps_link'  => customer_maps_link($customer),
		);

		$this->render('customers/show', $data);
	}

	public function edit($id)
	{
		$customer = $this->customer_model->get((int) $id);
		if ( ! $customer)
		{
			show_404();
		}
		$this->_render_form((int) $id);
	}

	public function update($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}
		if ( ! $this->customer_model->get((int) $id))
		{
			show_404();
		}
		$this->_save((int) $id);
	}

	public function archive($id)
	{
		$this->_require_post();
		$customer = $this->customer_model->get((int) $id);
		if ( ! $customer)
		{
			show_404();
		}

		$this->customer_model->archive($customer['id']);
		$this->session->set_flashdata('success', 'Pelanggan '.$customer['full_name'].' berhasil diarsipkan.');
		redirect('customers');
	}

	public function restore($id)
	{
		$this->_require_post();
		$customer = $this->customer_model->get((int) $id);
		if ( ! $customer)
		{
			show_404();
		}

		$this->customer_model->restore($customer['id']);
		$this->session->set_flashdata('success', 'Pelanggan '.$customer['full_name'].' berhasil dipulihkan.');
		redirect('customers?view=archived');
	}

	/**
	 * Simpan cepat tautan Google Maps via AJAX (realtime, tanpa reload).
	 * Hanya menerima POST dan mengembalikan JSON + token CSRF terbaru.
	 */
	public function saveMapsUrl($id)
	{
		$this->_require_post();

		$customer = $this->customer_model->get((int) $id);
		if ( ! $customer)
		{
			$this->_json(array('ok' => FALSE, 'message' => 'Pelanggan tidak ditemukan.'), 404);
			return;
		}

		$url = trim((string) $this->input->post('google_maps_url'));
		if ($url !== '' && ! is_valid_google_maps_url($url))
		{
			$this->_json(array(
				'ok'      => FALSE,
				'message' => 'Tautan tidak valid. Gunakan tautan Google Maps (https).',
			), 422);
			return;
		}

		$this->customer_model->update_maps_url($customer['id'], ($url === '') ? NULL : $url);

		$customer['google_maps_url'] = ($url === '') ? NULL : $url;
		$link = customer_maps_link($customer);

		$this->_json(array(
			'ok'            => TRUE,
			'message'       => ($url === '') ? 'Tautan Maps dikosongkan.' : 'Tautan Maps disimpan.',
			'google_maps_url' => $url,
			'maps_link'     => $link,
			'has_link'      => ( ! empty($link)),
		));
	}

	private function _json(array $payload, $status = 200)
	{
		// Sertakan token CSRF terbaru di setiap respons: csrf_regenerate=TRUE
		// membuat token berubah tiap POST, jadi sisi klien perlu memutakhirkannya.
		if ( ! isset($payload['csrf_token']))
		{
			$payload['csrf_token'] = $this->security->get_csrf_hash();
		}

		$this->output
			->set_status_header($status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($payload));
	}

	public function export()
	{
		$filters = $this->_filters();
		$rows    = $this->customer_model->get_all($filters, NULL, NULL);

		$handle = fopen('php://temp', 'r+');
		fwrite($handle, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8.
		fputcsv($handle, array(
			'Kode', 'Nama', 'WhatsApp', 'Perumahan', 'Alamat Lengkap', 'Blok/Nomor',
			'RT/RW', 'Patokan', 'Latitude', 'Longitude', 'Google Maps', 'Catatan Pengiriman',
			'Status Lokasi', 'Dibuat',
		));

		foreach ($rows as $row)
		{
			fputcsv($handle, array(
				$this->_csv_safe($row['customer_code']),
				$this->_csv_safe($row['full_name']),
				$this->_csv_safe($row['whatsapp_number']),
				$this->_csv_safe($row['housing_name']),
				$this->_csv_safe($row['full_address']),
				$this->_csv_safe($row['block_number']),
				$this->_csv_safe($row['rt_rw']),
				$this->_csv_safe($row['landmark']),
				$this->_csv_safe($row['latitude']),
				$this->_csv_safe($row['longitude']),
				$this->_csv_safe($row['google_maps_url']),
				$this->_csv_safe($row['delivery_notes']),
				location_status_label($row['location_status']),
				$this->_csv_safe($row['created_at']),
			));
		}

		rewind($handle);
		$csv = stream_get_contents($handle);
		fclose($handle);

		$this->load->helper('download');
		force_download('pelanggan-'.date('Ymd-His').'.csv', $csv);
	}

	// ---------------------------------------------------------------------

	private function _save($id = NULL)
	{
		$this->_set_rules();

		if ($this->form_validation->run() === TRUE)
		{
			$input     = $this->_input();
			$duplicate = $this->customer_model->find_duplicate($input['whatsapp_number'], $id);

			if ($duplicate && (string) $this->input->post('confirm_duplicate') !== '1')
			{
				$this->_render_form($id, $duplicate);
				return;
			}

			if ($id === NULL)
			{
				$new_id = $this->customer_model->create($input);
				if ($new_id === FALSE)
				{
					$this->session->set_flashdata('error', 'Gagal menyimpan data pelanggan.');
					redirect('customers/create');
				}
				$this->session->set_flashdata('success', 'Pelanggan berhasil ditambahkan.');
				redirect('customers/'.$new_id);
			}

			$this->customer_model->update($id, $input);
			$this->session->set_flashdata('success', 'Perubahan pelanggan berhasil disimpan.');
			redirect('customers/'.$id);
		}

		$this->_render_form($id, NULL);
	}

	private function _render_form($id = NULL, $duplicate = NULL)
	{
		$customer = ($id === NULL) ? NULL : $this->customer_model->get((int) $id);
		if ($id !== NULL && ! $customer)
		{
			show_404();
		}

		$data = array(
			'page_title'  => ($id === NULL) ? 'Tambah Pelanggan' : 'Edit Pelanggan',
			'customer'    => $customer,
			'form_action' => ($id === NULL) ? site_url('customers/store') : site_url('customers/update/'.(int) $id),
			'duplicate'   => $duplicate,
		);

		$this->render('customers/form', $data);
	}

	private function _set_rules()
	{
		$this->form_validation->set_rules('full_name', 'Nama lengkap', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('whatsapp_number', 'Nomor WhatsApp', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('housing_name', 'Nama perumahan', 'trim|max_length[150]');
		$this->form_validation->set_rules('full_address', 'Alamat lengkap', 'trim');
		$this->form_validation->set_rules('block_number', 'Blok/Nomor rumah', 'trim|max_length[50]');
		$this->form_validation->set_rules('rt_rw', 'RT/RW', 'trim|max_length[20]');
		$this->form_validation->set_rules('landmark', 'Patokan', 'trim|max_length[255]');
		$this->form_validation->set_rules('latitude', 'Latitude', 'trim|callback__validate_latitude');
		$this->form_validation->set_rules('google_maps_url', 'Google Maps URL', 'trim|max_length[500]|callback__valid_maps_url');
		$this->form_validation->set_rules('delivery_notes', 'Catatan pengiriman', 'trim');
		$this->form_validation->set_rules('location_verified', 'Status verifikasi', 'callback__valid_verified');
	}

	/**
	 * Validasi pasangan koordinat dan rentangnya.
	 */
	public function _validate_latitude($value)
	{
		$lat = trim((string) $value);
		$lng = trim((string) $this->input->post('longitude'));

		if ($lat === '' && $lng === '')
		{
			return TRUE;
		}

		if ($lat === '' || $lng === '')
		{
			$this->form_validation->set_message('_validate_latitude', 'Latitude dan Longitude harus diisi berpasangan.');
			return FALSE;
		}

		if ( ! is_numeric($lat) || $lat < -90 || $lat > 90)
		{
			$this->form_validation->set_message('_validate_latitude', 'Latitude harus berupa angka antara -90 dan 90.');
			return FALSE;
		}

		if ( ! is_numeric($lng) || $lng < -180 || $lng > 180)
		{
			$this->form_validation->set_message('_validate_latitude', 'Longitude harus berupa angka antara -180 dan 180.');
			return FALSE;
		}

		return TRUE;
	}

	public function _valid_maps_url($value)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return TRUE;
		}

		if ( ! is_valid_google_maps_url($value))
		{
			$this->form_validation->set_message('_valid_maps_url', 'Tautan Google Maps tidak valid. Gunakan format https://google.com/maps/..., https://maps.google.com/..., atau https://maps.app.goo.gl/...');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Cegah menandai terverifikasi tanpa bukti lokasi.
	 */
	public function _valid_verified($value)
	{
		if ((string) $value !== '1')
		{
			return TRUE;
		}

		$lat = trim((string) $this->input->post('latitude'));
		$lng = trim((string) $this->input->post('longitude'));
		$url = trim((string) $this->input->post('google_maps_url'));

		if (($lat !== '' && $lng !== '') || ($url !== '' && is_valid_google_maps_url($url)))
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_verified', 'Lokasi tidak dapat ditandai terverifikasi tanpa koordinat atau tautan Google Maps yang valid.');
		return FALSE;
	}

	private function _input()
	{
		$lat          = trim((string) $this->input->post('latitude'));
		$lng          = trim((string) $this->input->post('longitude'));
		$url          = trim((string) $this->input->post('google_maps_url'));
		$full_address = trim((string) $this->input->post('full_address'));
		$verified     = ((string) $this->input->post('location_verified') === '1');

		$status = 'unverified';
		if ($full_address === '')
		{
			$status = 'incomplete';
		}
		if ($verified)
		{
			$status = 'verified';
		}

		return array(
			'full_name'       => trim((string) $this->input->post('full_name')),
			'whatsapp_number' => trim((string) $this->input->post('whatsapp_number')),
			'housing_name'    => $this->_nullable('housing_name'),
			'full_address'    => ($full_address === '') ? NULL : $full_address,
			'block_number'    => $this->_nullable('block_number'),
			'rt_rw'           => $this->_nullable('rt_rw'),
			'landmark'        => $this->_nullable('landmark'),
			'latitude'        => ($lat === '') ? NULL : $lat,
			'longitude'       => ($lng === '') ? NULL : $lng,
			'google_maps_url' => ($url === '') ? NULL : $url,
			'delivery_notes'  => $this->_nullable('delivery_notes'),
			'location_status' => $status,
		);
	}

	private function _nullable($field)
	{
		$value = trim((string) $this->input->post($field));
		return ($value === '') ? NULL : $value;
	}

	private function _filters()
	{
		$q      = trim((string) $this->input->get('q', TRUE));
		$status = (string) $this->input->get('status', TRUE);
		$view   = (string) $this->input->get('view', TRUE);

		if ( ! in_array($status, array('incomplete', 'unverified', 'verified'), TRUE))
		{
			$status = '';
		}
		if ( ! in_array($view, array('active', 'archived'), TRUE))
		{
			$view = 'active';
		}

		return array('q' => $q, 'status' => $status, 'view' => $view);
	}

	private function _require_post()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}
	}

	/**
	 * Cegah CSV injection pada nilai yang dimulai karakter formula.
	 */
	private function _csv_safe($value)
	{
		$value = (string) $value;
		if ($value !== '' && in_array($value[0], array('=', '+', '-', '@'), TRUE))
		{
			return "'".$value;
		}
		return $value;
	}
}
