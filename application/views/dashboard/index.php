<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
	<div>
		<h1 class="text-2xl font-semibold tracking-tight text-text sm:text-3xl">Dashboard</h1>
		<p class="mt-1 text-sm text-muted">Ringkasan data pelanggan CRM Faqih Mart.</p>
	</div>
	<a href="<?= site_url('customers/create') ?>" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/30">
		<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
		Tambah Pelanggan
	</a>
</div>

<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
	<?php
	$cards = array(
		array('label' => 'Pelanggan Aktif', 'value' => $stats['total'], 'icon' => 'users', 'icon_bg' => 'fm-bg-gray-50 text-green-600'),
		array('label' => 'Lokasi Terverifikasi', 'value' => $stats['verified'], 'icon' => 'check', 'icon_bg' => 'fm-bg-gray-50 text-green-600'),
		array('label' => 'Belum Diverifikasi', 'value' => $stats['unverified'], 'icon' => 'clock', 'icon_bg' => 'bg-amber-50 text-amber-600'),
		array('label' => 'Belum Lengkap', 'value' => $stats['incomplete'], 'icon' => 'alert', 'icon_bg' => 'bg-red-50 text-red-600'),
	);
	?>
	<?php foreach ($cards as $card): ?>
		<div class="group relative overflow-hidden fm-card p-5 transition-shadow hover:shadow-md">
			<div class="flex items-start justify-between gap-3">
				<div>
					<p class="text-sm font-medium text-muted"><?= html_escape($card['label']) ?></p>
					<p class="mt-2 text-3xl font-semibold tracking-tight text-text"><?= (int) $card['value'] ?></p>
				</div>
				<span class="inline-flex h-10 w-10 items-center justify-center rounded-lg <?= $card['icon_bg'] ?>">
					<?php if ($card['icon'] === 'users'): ?>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 00-8 0M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
					<?php elseif ($card['icon'] === 'check'): ?>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<?php elseif ($card['icon'] === 'clock'): ?>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<?php elseif ($card['icon'] === 'alert'): ?>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
					<?php endif; ?>
				</span>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="fm-card">
	<div class="border-b border-border px-5 py-4">
		<div>
			<h2 class="text-base font-semibold text-text">Pelanggan Terbaru</h2>
			<p class="mt-0.5 text-sm text-muted">5 pelanggan terakhir yang ditambahkan</p>
		</div>
		<a href="<?= site_url('customers') ?>" class="text-sm font-medium text-primary transition-colors hover:text-primary-dark">Lihat semua</a>
	</div>

	<?php if (empty($recent)): ?>
		<div class="px-5 py-12 text-center">
			<div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full fm-bg-gray-50 text-muted">
				<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 00-8 0M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
			</div>
			<p class="text-sm text-muted">Belum ada data pelanggan.</p>
			<a href="<?= site_url('customers/create') ?>" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark">Tambah pelanggan pertama</a>
		</div>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="fm-table min-w-full">
				<thead>
					<tr>
						<th class="px-5 py-3">Kode</th>
						<th class="px-5 py-3">Nama</th>
						<th class="px-5 py-3">WhatsApp</th>
						<th class="px-5 py-3">Status Lokasi</th>
						<th class="px-5 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y">
					<?php foreach ($recent as $row): ?>
						<tr>
							<td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs text-muted"><?= html_escape($row['customer_code']) ?></td>
							<td class="px-5 py-3.5 font-medium text-text"><?= html_escape($row['full_name']) ?></td>
							<td class="whitespace-nowrap px-5 py-3.5 text-muted"><?= html_escape($row['whatsapp_number']) ?></td>
							<td class="px-5 py-3.5">
								<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?= location_status_badge_classes($row['location_status']) ?>">
									<?= html_escape(location_status_label($row['location_status'])) ?>
								</span>
							</td>
							<td class="px-5 py-3.5 text-right">
								<a href="<?= site_url('customers/'.$row['id']) ?>" class="text-sm font-medium text-primary hover:text-primary-dark">Detail</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
