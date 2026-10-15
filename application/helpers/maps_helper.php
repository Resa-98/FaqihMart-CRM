<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper terkait lokasi dan Google Maps.
 * Hanya menerima tautan Google Maps biasa (tanpa API berbayar).
 */

if ( ! function_exists('is_valid_google_maps_url'))
{
	/**
	 * Validasi tautan Google Maps. Wajib https dan host yang dikenal.
	 *
	 * @param string $url
	 * @return bool
	 */
	function is_valid_google_maps_url($url)
	{
		$url = trim((string) $url);
		if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === FALSE)
		{
			return FALSE;
		}

		$parts = parse_url($url);
		if (empty($parts['scheme']) || strtolower($parts['scheme']) !== 'https')
		{
			return FALSE;
		}
		if (empty($parts['host']) || isset($parts['user']) || isset($parts['pass']))
		{
			return FALSE;
		}

		$host = strtolower($parts['host']);
		$path = isset($parts['path']) ? $parts['path'] : '';

		// Tautan pendek resmi Google Maps.
		if ($host === 'maps.app.goo.gl')
		{
			return TRUE;
		}
		if ($host === 'goo.gl')
		{
			return strpos($path, '/maps') === 0;
		}

		// maps.google.<tld> (mis. maps.google.com, maps.google.co.id)
		if (preg_match('/^maps\.google\.[a-z]{2,3}(\.[a-z]{2,3})?$/', $host))
		{
			return TRUE;
		}

		// google.<tld>/maps/... (termasuk www.), tld dibatasi agar
		// host jahat seperti google.com.evil.com ditolak.
		if (preg_match('/^(www\.)?google\.[a-z]{2,3}(\.[a-z]{2,3})?$/', $host)
			&& strpos($path, '/maps') === 0)
		{
			return TRUE;
		}

		return FALSE;
	}
}

if ( ! function_exists('customer_maps_link'))
{
	/**
	 * Tautan navigasi terbaik: utamakan koordinat terverifikasi, lalu URL Maps.
	 *
	 * @param array $customer
	 * @return string|null
	 */
	function customer_maps_link(array $customer)
	{
		if ( ! empty($customer['latitude']) && ! empty($customer['longitude']))
		{
			return 'https://www.google.com/maps/search/?api=1&query='
				.rawurlencode($customer['latitude'].','.$customer['longitude']);
		}

		if ( ! empty($customer['google_maps_url']) && is_valid_google_maps_url($customer['google_maps_url']))
		{
			return $customer['google_maps_url'];
		}

		return NULL;
	}
}

if ( ! function_exists('customer_address_text'))
{
	/**
	 * Susun teks alamat lengkap dari kolom-kolom yang tersedia.
	 *
	 * @param array $customer
	 * @return string
	 */
	function customer_address_text(array $customer)
	{
		$parts = array(
			isset($customer['full_address']) ? $customer['full_address'] : '',
			! empty($customer['housing_name']) ? $customer['housing_name'] : '',
			! empty($customer['block_number']) ? 'Blok '.$customer['block_number'] : '',
			! empty($customer['rt_rw']) ? 'RT/RW '.$customer['rt_rw'] : '',
			! empty($customer['landmark']) ? 'Patokan: '.$customer['landmark'] : '',
		);

		$parts = array_filter($parts, function ($value) {
			return trim((string) $value) !== '';
		});

		return implode(', ', $parts);
	}
}

if ( ! function_exists('location_status_label'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function location_status_label($status)
	{
		$map = array(
			'incomplete' => 'Belum Lengkap',
			'unverified' => 'Belum Diverifikasi',
			'verified'   => 'Terverifikasi',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if ( ! function_exists('location_status_badge_classes'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function location_status_badge_classes($status)
	{
		switch ($status)
		{
			case 'verified':
				return 'bg-green-100 text-green-800';
			case 'unverified':
				return 'bg-amber-100 text-amber-800';
			default:
				return 'bg-red-100 text-red-800';
		}
	}
}
