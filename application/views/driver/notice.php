<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<link rel="icon" type="image/webp" href="<?= base_url('assets/icon-logo.webp') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icon-logo.webp') ?>">
<title>Tautan tidak berlaku &middot; Faqih Mart</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#16A34A', dark: '#15803D' } } } } };
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/ui.css') ?>">
<style>
	.header-gradient { background: linear-gradient(135deg, #16A34A 0%, #15803D 100%); }
</style>
</head>
<body class="bg-gray-100 text-text">
<header class="header-gradient px-4 py-3 text-white">
	<div class="mx-auto max-w-2xl">
		<span class="inline-block rounded-lg bg-white px-2 py-1">
			<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-6 w-auto">
		</span>
		<h1 class="mt-2 text-lg font-semibold">Akses Daftar Pengiriman</h1>
	</div>
</header>
<main class="mx-auto max-w-2xl p-4">
	<div class="fm-card p-6 text-center">
		<p class="text-lg font-semibold text-text">Tautan tidak valid atau sudah tidak berlaku.</p>
		<p class="mt-2 text-sm text-muted">
			Daftar ini belum dipublikasikan, sudah dibatalkan, atau aksesnya telah dicabut oleh admin.
			Silakan hubungi admin untuk mendapatkan tautan terbaru.
		</p>
	</div>
</main>
</body>
</html>
