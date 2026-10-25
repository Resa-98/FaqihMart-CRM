<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper label & badge untuk modul Daftar Pengiriman.
 */

if ( ! function_exists('delivery_session_label'))
{
	/**
	 * @param string $session
	 * @return string
	 */
	function delivery_session_label($session)
	{
		$map = array('pagi' => 'Pagi', 'sore' => 'Sore');
		return isset($map[$session]) ? $map[$session] : $session;
	}
}

if ( ! function_exists('delivery_status_label'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function delivery_status_label($status)
	{
		$map = array(
			'draft'     => 'Draft',
			'published' => 'Dipublikasikan',
			'completed' => 'Selesai',
			'cancelled' => 'Dibatalkan',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if ( ! function_exists('delivery_status_badge_classes'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function delivery_status_badge_classes($status)
	{
		switch ($status)
		{
			case 'published':
				return 'fm-badge fm-badge-primary';
			case 'completed':
				return 'fm-badge fm-badge-gray';
			case 'cancelled':
				return 'fm-badge fm-badge-red';
			default: // draft
				return 'fm-badge fm-badge-amber';
		}
	}
}

if ( ! function_exists('delivery_item_status_label'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function delivery_item_status_label($status)
	{
		$map = array(
			'pending'     => 'Belum dikunjungi',
			'in_progress' => 'Sedang diproses',
			'done'        => 'Selesai',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if ( ! function_exists('delivery_item_status_badge_classes'))
{
	/**
	 * @param string $status
	 * @return string
	 */
	function delivery_item_status_badge_classes($status)
	{
		switch ($status)
		{
			case 'done':
				return 'fm-badge fm-badge-primary';
			case 'in_progress':
				return 'fm-badge fm-badge-blue';
			default: // pending
				return 'fm-badge fm-badge-gray';
		}
	}
}

if ( ! function_exists('delivery_receipts_dir'))
{
	/**
	 * Direktori penyimpanan foto struk (di luar akses langsung via .htaccess).
	 *
	 * @return string Path absolut dengan trailing separator.
	 */
	function delivery_receipts_dir()
	{
		return rtrim(FCPATH, '/\\').DIRECTORY_SEPARATOR.'uploads'
			.DIRECTORY_SEPARATOR.'receipts'.DIRECTORY_SEPARATOR;
	}
}

if ( ! function_exists('delivery_receipt_path'))
{
	/**
	 * Path absolut file struk. Hanya menerima nama file yang aman dan
	 * ekstensi gambar yang diizinkan.
	 *
	 * @param string|null $filename
	 * @return string|null
	 */
	function delivery_receipt_path($filename)
	{
		$filename = str_replace('\\', '/', (string) $filename);
		$filename = basename($filename);

		if ($filename === '' || $filename === '.' || $filename === '..' || strpos($filename, "\0") !== FALSE)
		{
			return NULL;
		}

		if (delivery_receipt_mime($filename) === NULL)
		{
			return NULL;
		}

		return delivery_receipts_dir().$filename;
	}
}

if ( ! function_exists('delivery_receipt_mime'))
{
	/**
	 * MIME type yang diizinkan berdasarkan ekstensi file.
	 *
	 * @param string|null $filename
	 * @return string|null
	 */
	function delivery_receipt_mime($filename)
	{
		$ext = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
		$map = array(
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
		);

		return isset($map[$ext]) ? $map[$ext] : NULL;
	}
}

if ( ! function_exists('delivery_receipt_exists'))
{
	/**
	 * @param string|null $filename
	 * @return bool
	 */
	function delivery_receipt_exists($filename)
	{
		$path = delivery_receipt_path($filename);
		return ($path !== NULL && is_file($path));
	}
}

if ( ! function_exists('delivery_receipt_file'))
{
	/**
	 * Informasi file struk yang siap disajikan, atau NULL bila tidak valid.
	 *
	 * @param string|null $filename
	 * @return array|null
	 */
	function delivery_receipt_file($filename)
	{
		$path = delivery_receipt_path($filename);
		$mime = delivery_receipt_mime($filename);

		if ($path === NULL || $mime === NULL || ! is_file($path))
		{
			return NULL;
		}

		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		return array(
			'path'          => $path,
			'mime'          => $mime,
			'size'          => (int) filesize($path),
			'download_name' => 'struk-'.date('Ymd-His').'.'.$ext,
		);
	}
}
