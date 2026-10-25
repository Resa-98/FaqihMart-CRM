<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$flash_success = $this->session->flashdata('success');
$flash_error   = $this->session->flashdata('error');
$current       = $this->uri->segment(1);
$nav = array(
	array('label' => 'Dashboard', 'url' => site_url('dashboard'), 'key' => 'dashboard', 'icon' => 'home'),
	array('label' => 'Pelanggan', 'url' => site_url('customers'), 'key' => 'customers', 'icon' => 'users'),
	array('label' => 'Tambah Pelanggan', 'url' => site_url('customers/create'), 'key' => 'create', 'icon' => 'user-plus'),
	array('label' => 'Pengiriman', 'url' => site_url('deliveries'), 'key' => 'deliveries', 'icon' => 'truck'),
	array('label' => 'Ganti Password', 'url' => site_url('change-password'), 'key' => 'change-password', 'icon' => 'lock'),
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
tailwind.config = {
	theme: {
		extend: {
			colors: {
				primary: { DEFAULT: '#16A34A', dark: '#15803D', light: '#22C55E' },
				surface: '#FFFFFF',
				bg: '#F8FAFC',
				text: { DEFAULT: '#0F172A', muted: '#64748B' },
				border: '#E2E8F0'
			},
			boxShadow: {
				'soft': '0 1px 3px 0 rgb(0 0 0 / 0.02), 0 1px 2px -1px rgb(0 0 0 / 0.02)',
				'card': '0 1px 3px 0 rgb(15 23 42 / 0.04), 0 1px 2px -1px rgb(15 23 42 / 0.04)',
				'elevated': '0 4px 12px -2px rgb(15 23 42 / 0.08)'
			},
			borderRadius: {
				'xl': '0.75rem',
				'2xl': '1rem'
			}
		}
	}
};
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/ui.css') ?>">
<style>
	.focus-ring:focus { outline: 2px solid rgba(22,163,74,0.35); outline-offset: 2px; }
	/* Smooth sidebar toggle */
	#sidebar { transition: transform 0.2s ease, opacity 0.2s ease; }
	@media (max-width: 767px) {
		#sidebar { position: fixed; top: 0; left: 0; z-index: 50; height: 100vh; transform: translateX(-100%); }
		#sidebar:not(.hidden) { transform: translateX(0); }
	}
	/* Scrollbar styling */
	::-webkit-scrollbar { width: 8px; height: 8px; }
	::-webkit-scrollbar-track { background: transparent; }
	::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
	::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
</style>
</head>
<body class="bg-bg text-text antialiased">
<div class="min-h-screen md:flex">
	<!-- Mobile sidebar overlay -->
	<div id="sidebarOverlay" class="hidden" aria-hidden="true"></div>

	<header class="sticky top-0 z-40 flex items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 py-2.5 backdrop-blur md:hidden">
		<a href="<?= site_url('dashboard') ?>" class="flex items-center">
			<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-10 w-auto">
		</a>
		<button type="button" id="menuToggle" class="rounded-lg p-2 text-muted transition-colors hover:bg-slate-50 hover:text-text focus:outline-none focus:ring-2 focus-ring-primary/30" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
			<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
		</button>
	</header>

	<aside id="sidebar" class="hidden md:flex md:flex-col md:w-72 md:shrink-0 bg-white border-r border-slate-200/80 md:min-h-screen shadow-[inset_-1px_0_0_0_rgba(226,232,240,0.6)]">
		<div class="hidden md:flex items-center px-6 py-5 border-b border-slate-200/80">
			<a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2">
				<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-16 w-auto">
			</a>
		</div>
		<nav class="flex-1 p-3 space-y-1">
			<?php foreach ($nav as $item): ?>
				<?php $active = ($current === $item['key']); ?>
				<a href="<?= $item['url'] ?>" class="group flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-all <?= $active ? 'bg-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' ?>">
					<span class="inline-flex h-5 w-5 items-center justify-center">
						<?php if ($item['icon'] === 'home'): ?>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
						<?php elseif ($item['icon'] === 'users'): ?>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 00-8 0M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
						<?php elseif ($item['icon'] === 'user-plus'): ?>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
						<?php elseif ($item['icon'] === 'truck'): ?>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0h2a1 1 0 001-1v-4l-3-4h-3m-4 9H6"/></svg>
						<?php elseif ($item['icon'] === 'lock'): ?>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
						<?php endif; ?>
					</span>
					<span class="font-medium"><?= html_escape($item['label']) ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
		<div class="p-4 border-t border-slate-200/80">
			<div class="rounded-lg bg-slate-50 px-3 py-2.5">
				<p class="text-[11px] uppercase tracking-wide text-slate-400">Masuk sebagai</p>
				<p class="mt-0.5 truncate font-medium text-slate-900"><?= html_escape(isset($admin['name']) ? $admin['name'] : '-') ?></p>
				<?= form_open('logout', array('class' => 'mt-2')) ?>
					<button type="submit" class="text-sm font-medium text-red-600 transition-colors hover:text-red-700">Keluar</button>
				<?= form_close() ?>
			</div>
		</div>
	</aside>

	<div class="flex-1 min-w-0">
		<main class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto">
			<?php if ($flash_success): ?>
				<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
					<?= html_escape($flash_success) ?>
				</div>
			<?php endif; ?>
			<?php if ($flash_error): ?>
				<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
					<?= html_escape($flash_error) ?>
				</div>
			<?php endif; ?>
