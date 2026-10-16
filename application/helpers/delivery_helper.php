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
				return 'bg-green-100 text-green-800';
			case 'completed':
				return 'bg-gray-200 text-gray-700';
			case 'cancelled':
				return 'bg-red-100 text-red-700';
			default: // draft
				return 'bg-amber-100 text-amber-800';
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
				return 'bg-green-100 text-green-800';
			case 'in_progress':
				return 'bg-blue-100 text-blue-800';
			default: // pending
				return 'bg-gray-100 text-gray-600';
		}
	}
}
