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
		<h1 class="text-2xl font-bold text-gray-800">Daftar Pengiriman</h1>
		<p class="text-sm text-gray-500">Susun tujuan pengiriman per tanggal &amp; sesi untuk driver.</p>
	</div>
	<a href="<?= site_url('deliveries/create') ?>" class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">
		+ Buat Daftar
	</a>
</div>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Ringkasan <?= html_escape($filters['date']) ?></h2>
<div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-5">
	<div class="rounded-lg bg-white p-4 shadow-sm">
		<p class="text-xs uppercase tracking-wide text-gray-400">Pengiriman</p>
		<p class="mt-1 text-2xl font-bold text-gray-800"><?= (int) $stats['lists_total'] ?></p>
	</div>
	<div class="rounded-lg bg-white p-4 shadow-sm">
		<p class="text-xs uppercase tracking-wide text-gray-400">Tujuan Pagi</p>
		<p class="mt-1 text-2xl font-bold text-gray-800"><?= (int) $stats['dest_pagi'] ?></p>
	</div>
	<div class="rounded-lg bg-white p-4 shadow-sm">
		<p class="text-xs uppercase tracking-wide text-gray-400">Tujuan Sore</p>
		<p class="mt-1 text-2xl font-bold text-gray-800"><?= (int) $stats['dest_sore'] ?></p>
	</div>
	<div class="rounded-lg bg-white p-4 shadow-sm">
		<p class="text-xs uppercase tracking-wide text-gray-400">Belum Selesai</p>
		<p class="mt-1 text-2xl font-bold text-amber-600"><?= (int) $stats['dest_open'] ?></p>
	</div>
	<div class="rounded-lg bg-white p-4 shadow-sm">
		<p class="text-xs uppercase tracking-wide text-gray-400">Selesai</p>
		<p class="mt-1 text-2xl font-bold text-primary"><?= (int) $stats['dest_done'] ?></p>
	</div>
</div>

<form method="get" action="<?= site_url('deliveries') ?>" class="mb-4 rounded-lg bg-white p-4 shadow-sm">
	<div class="flex flex-col gap-3 md:flex-row md:items-end">
		<div class="md:w-48">
			<label for="date" class="mb-1 block text-sm font-medium text-gray-700">Tanggal</label>
			<input type="date" name="date" id="date" value="<?= html_escape($filters['date']) ?>"
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
		<div class="md:w-40">
			<label for="session" class="mb-1 block text-sm font-medium text-gray-700">Sesi</label>
			<select name="session" id="session" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				<option value="">Semua sesi</option>
				<option value="pagi" <?= $filters['session'] === 'pagi' ? 'selected' : '' ?>>Pagi</option>
				<option value="sore" <?= $filters['session'] === 'sore' ? 'selected' : '' ?>>Sore</option>
			</select>
		</div>
		<div class="md:w-48">
			<label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
			<select name="status" id="status" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				<option value="">Semua status</option>
				<option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
				<option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Dipublikasikan</option>
				<option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Selesai</option>
				<option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
			</select>
		</div>
		<div class="flex gap-2">
			<button type="submit" class="rounded bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Terapkan</button>
			<a href="<?= site_url('deliveries') ?>" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Hari ini</a>
		</div>
	</div>
</form>

<div class="rounded-lg bg-white shadow-sm">
	<?php if (empty($lists)): ?>
		<div class="px-5 py-12 text-center text-gray-500">
			<p class="text-lg">Belum ada daftar pengiriman.</p>
			<p class="mt-1 text-sm">Buat daftar untuk tanggal &amp; sesi ini, lalu tambahkan tujuan dari data pelanggan.</p>
			<a href="<?= site_url('deliveries/create') ?>" class="mt-4 inline-block rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">+ Buat Daftar</a>
		</div>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200 text-sm">
				<thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
					<tr>
						<th class="px-4 py-3">Tanggal</th>
						<th class="px-4 py-3">Sesi</th>
						<th class="px-4 py-3">Judul</th>
						<th class="px-4 py-3">Status</th>
						<th class="px-4 py-3">Tujuan</th>
						<th class="px-4 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100">
					<?php foreach ($lists as $row): ?>
						<tr class="hover:bg-gray-50">
							<td class="whitespace-nowrap px-4 py-3 text-gray-700"><?= html_escape($row['delivery_date']) ?></td>
							<td class="whitespace-nowrap px-4 py-3">
								<span class="rounded px-2 py-1 text-xs font-medium <?= $row['session'] === 'pagi' ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800' ?>">
									<?= html_escape(delivery_session_label($row['session'])) ?>
								</span>
							</td>
							<td class="px-4 py-3">
								<a href="<?= site_url('deliveries/'.$row['id']) ?>" class="font-medium text-gray-800 hover:text-primary">
									<?= html_escape($row['title'] !== NULL && $row['title'] !== '' ? $row['title'] : '(tanpa judul)') ?>
								</a>
							</td>
							<td class="whitespace-nowrap px-4 py-3">
								<span class="rounded-full px-2 py-1 text-xs font-medium <?= delivery_status_badge_classes($row['status']) ?>">
									<?= html_escape(delivery_status_label($row['status'])) ?>
								</span>
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-gray-600">
								<?= (int) $row['done_items'] ?> / <?= (int) $row['total_items'] ?> selesai
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-right">
								<a href="<?= site_url('deliveries/'.$row['id']) ?>" class="text-primary hover:underline">Kelola</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
