<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$flash_success = $this->session->flashdata('success');
$flash_error   = $this->session->flashdata('error');
$current       = $this->uri->segment(1);
$nav = array(
	array('label' => 'Dashboard', 'url' => site_url('dashboard'), 'key' => 'dashboard'),
	array('label' => 'Pelanggan', 'url' => site_url('customers'), 'key' => 'customers'),
	array('label' => 'Tambah Pelanggan', 'url' => site_url('customers/create'), 'key' => 'create'),
	array('label' => 'Pengiriman', 'url' => site_url('deliveries'), 'key' => 'deliveries'),
	array('label' => 'Ganti Password', 'url' => site_url('change-password'), 'key' => 'change-password'),
);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/webp" href="<?= base_url('assets/icon-logo.webp') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icon-logo.webp') ?>">
<meta name="csrf-token-name" content="<?= html_escape($this->security->get_csrf_token_name()) ?>">
<meta name="csrf-token" content="<?= html_escape($this->security->get_csrf_hash()) ?>">
<title><?= html_escape(isset($page_title) ? $page_title : 'CRM Faqih Mart') ?> &middot; CRM Faqih Mart</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#16A34A', dark: '#15803D' } } } } };
</script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="min-h-screen md:flex">

	<header class="sticky top-0 z-30 flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 md:hidden">
		<a href="<?= site_url('dashboard') ?>" class="flex items-center">
			<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-10 w-auto">
		</a>
		<button type="button" id="menuToggle" class="rounded p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
			<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
		</button>
	</header>

	<aside id="sidebar" class="hidden md:block md:w-64 md:shrink-0 bg-white border-r border-gray-200 md:min-h-screen">
		<div class="hidden md:flex items-center px-5 py-4 border-b border-gray-200">
			<a href="<?= site_url('dashboard') ?>" class="flex items-center">
				<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-20 w-auto">
			</a>
		</div>
		<nav class="p-3 space-y-1">
			<?php foreach ($nav as $item): ?>
				<?php $active = ($current === $item['key']); ?>
				<a href="<?= $item['url'] ?>" class="block rounded px-3 py-2 text-sm font-medium <?= $active ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
					<?= html_escape($item['label']) ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<div class="p-3 border-t border-gray-200">
			<p class="text-xs uppercase tracking-wide text-gray-400">Masuk sebagai</p>
			<p class="font-medium text-gray-800"><?= html_escape(isset($admin['name']) ? $admin['name'] : '-') ?></p>
			<?= form_open('logout', array('class' => 'mt-2')) ?>
				<button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
			<?= form_close() ?>
		</div>
	</aside>

	<div class="flex-1 min-w-0">
		<main class="p-4 md:p-8 max-w-6xl mx-auto">
			<?php if ($flash_success): ?>
				<div class="mb-4 rounded border border-green-200 bg-green-100 px-4 py-3 text-green-800" role="status">
					<?= html_escape($flash_success) ?>
				</div>
			<?php endif; ?>
			<?php if ($flash_error): ?>
				<div class="mb-4 rounded border border-red-200 bg-red-100 px-4 py-3 text-red-800" role="alert">
					<?= html_escape($flash_error) ?>
				</div>
			<?php endif; ?>
