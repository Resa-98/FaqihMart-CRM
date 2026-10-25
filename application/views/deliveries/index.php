<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$filter_params = array(
	'date'    => $filters['date'],
	'session' => $filters['session'],
	'status'  => $filters['status'],
);
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
	<div>
		<h1 class="text-2xl font-bold text-text">Daftar Pengiriman</h1>
		<p class="text-sm text-muted">Susun tujuan pengiriman per tanggal &amp; sesi untuk driver.</p>
	</div>
	<a href="<?= site_url('deliveries/create') ?>" class="fm-btn fm-btn-primary">
		+ Buat Daftar
	</a>
</div>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-muted">Ringkasan <?= html_escape($filters['date']) ?></h2>
<div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-5">
	<div class="fm-card p-4">
		<p class="text-xs uppercase tracking-wide text-muted">Pengiriman</p>
		<p class="mt-1 text-2xl font-bold text-text"><?= (int) $stats['lists_total'] ?></p>
	</div>
	<div class="fm-card p-4">
		<p class="text-xs uppercase tracking-wide text-muted">Tujuan Pagi</p>
		<p class="mt-1 text-2xl font-bold text-text"><?= (int) $stats['dest_pagi'] ?></p>
	</div>
	<div class="fm-card p-4">
		<p class="text-xs uppercase tracking-wide text-muted">Tujuan Sore</p>
		<p class="mt-1 text-2xl font-bold text-text"><?= (int) $stats['dest_sore'] ?></p>
	</div>
	<div class="fm-card p-4">
		<p class="text-xs uppercase tracking-wide text-muted">Belum Selesai</p>
		<p class="mt-1 text-2xl font-bold text-amber-600"><?= (int) $stats['dest_open'] ?></p>
	</div>
	<div class="fm-card p-4">
		<p class="text-xs uppercase tracking-wide text-muted">Selesai</p>
		<p class="mt-1 text-2xl font-bold text-primary"><?= (int) $stats['dest_done'] ?></p>
	</div>
</div>

<form method="get" action="<?= site_url('deliveries') ?>" class="mb-4 fm-card p-4">
	<div class="flex flex-col gap-3 md:flex-row md:items-end">
		<div class="md:w-48">
			<label for="date" class="fm-field-label">Tanggal</label>
			<input type="date" name="date" id="date" value="<?= html_escape($filters['date']) ?>"
				class="fm-input">
		</div>
		<div class="md:w-40">
			<label for="session" class="fm-field-label">Sesi</label>
			<select name="session" id="session" class="fm-input">
				<option value="">Semua sesi</option>
				<option value="pagi" <?= $filters['session'] === 'pagi' ? 'selected' : '' ?>>Pagi</option>
				<option value="sore" <?= $filters['session'] === 'sore' ? 'selected' : '' ?>>Sore</option>
			</select>
		</div>
		<div class="md:w-48">
			<label for="status" class="fm-field-label">Status</label>
			<select name="status" id="status" class="fm-input">
				<option value="">Semua status</option>
				<option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
				<option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Dipublikasikan</option>
				<option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Selesai</option>
				<option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
			</select>
		</div>
		<div class="flex gap-2">
			<button type="submit" class="fm-btn fm-btn-primary">Terapkan</button>
			<a href="<?= site_url('deliveries') ?>" class="fm-btn fm-btn-secondary">Hari ini</a>
		</div>
	</div>
</form>

<div class="fm-card">
	<?php if (empty($lists)): ?>
		<div class="fm-empty">
			<div class="fm-empty-icon">
				<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0h2a1 1 0 001-1v-4l-3-4h-3m-4 9H6"/></svg>
			</div>
			<p class="fm-empty-title">Belum ada daftar pengiriman.</p>
			<p class="fm-empty-desc">Buat daftar untuk tanggal &amp; sesi ini, lalu tambahkan tujuan dari data pelanggan.</p>
			<a href="<?= site_url('deliveries/create') ?>" class="fm-btn fm-btn-primary">+ Buat Daftar</a>
		</div>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="fm-table min-w-full">
				<thead>
					<tr>
						<th class="px-4 py-3">Tanggal</th>
						<th class="px-4 py-3">Sesi</th>
						<th class="px-4 py-3">Judul</th>
						<th class="px-4 py-3">Status</th>
						<th class="px-4 py-3">Tujuan</th>
						<th class="px-4 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($lists as $row): ?>
						<tr>
							<td class="whitespace-nowrap px-4 py-3 text-muted"><?= html_escape($row['delivery_date']) ?></td>
							<td class="whitespace-nowrap px-4 py-3">
								<span class="rounded px-2 py-1 text-xs font-medium fm-badge-sm fm-badge-blue">
									<?= html_escape(delivery_session_label($row['session'])) ?>
								</span>
							</td>
							<td class="px-4 py-3">
								<a href="<?= site_url('deliveries/'.$row['id']) ?>" class="font-medium text-text hover:text-primary">
									<?= html_escape($row['title'] !== NULL && $row['title'] !== '' ? $row['title'] : '(tanpa judul)') ?>
								</a>
							</td>
							<td class="whitespace-nowrap px-4 py-3">
								<span class="rounded-full px-2 py-1 text-xs font-medium <?= delivery_status_badge_classes($row['status']) ?>">
									<?= html_escape(delivery_status_label($row['status'])) ?>
								</span>
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-muted">
								<?= (int) $row['done_items'] ?> / <?= (int) $row['total_items'] ?> selesai
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-right">
								<a href="<?= site_url('deliveries/'.$row['id']) ?>" class="text-primary hover:text-primary-dark">Kelola</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
