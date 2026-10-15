<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
	<div>
		<h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
		<p class="text-sm text-gray-500">Ringkasan data pelanggan CRM Faqih Mart.</p>
	</div>
	<a href="<?= site_url('customers/create') ?>" class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">
		+ Tambah Pelanggan
	</a>
</div>

<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
	<?php
	$cards = array(
		array('label' => 'Pelanggan Aktif', 'value' => $stats['total'], 'classes' => 'border-gray-200', 'value_classes' => 'text-gray-800'),
		array('label' => 'Lokasi Terverifikasi', 'value' => $stats['verified'], 'classes' => 'border-green-200', 'value_classes' => 'text-green-700'),
		array('label' => 'Belum Diverifikasi', 'value' => $stats['unverified'], 'classes' => 'border-amber-200', 'value_classes' => 'text-amber-700'),
		array('label' => 'Belum Lengkap', 'value' => $stats['incomplete'], 'classes' => 'border-red-200', 'value_classes' => 'text-red-700'),
	);
	?>
	<?php foreach ($cards as $card): ?>
		<div class="rounded-lg border <?= $card['classes'] ?> bg-white p-4 shadow-sm">
			<p class="text-sm text-gray-500"><?= html_escape($card['label']) ?></p>
			<p class="mt-1 text-3xl font-bold <?= $card['value_classes'] ?>"><?= (int) $card['value'] ?></p>
		</div>
	<?php endforeach; ?>
</div>

<div class="rounded-lg bg-white shadow-sm">
	<div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
		<h2 class="font-semibold text-gray-800">Pelanggan Terbaru</h2>
		<a href="<?= site_url('customers') ?>" class="text-sm text-primary hover:underline">Lihat semua</a>
	</div>

	<?php if (empty($recent)): ?>
		<div class="px-5 py-10 text-center text-gray-500">
			<p>Belum ada data pelanggan.</p>
			<a href="<?= site_url('customers/create') ?>" class="mt-2 inline-block text-sm text-primary hover:underline">Tambah pelanggan pertama</a>
		</div>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200 text-sm">
				<thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
					<tr>
						<th class="px-5 py-3">Kode</th>
						<th class="px-5 py-3">Nama</th>
						<th class="px-5 py-3">WhatsApp</th>
						<th class="px-5 py-3">Status Lokasi</th>
						<th class="px-5 py-3"></th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100">
					<?php foreach ($recent as $row): ?>
						<tr>
							<td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-500"><?= html_escape($row['customer_code']) ?></td>
							<td class="px-5 py-3 font-medium text-gray-800"><?= html_escape($row['full_name']) ?></td>
							<td class="whitespace-nowrap px-5 py-3"><?= html_escape($row['whatsapp_number']) ?></td>
							<td class="px-5 py-3">
								<span class="rounded-full px-2 py-1 text-xs font-medium <?= location_status_badge_classes($row['location_status']) ?>">
									<?= html_escape(location_status_label($row['location_status'])) ?>
								</span>
							</td>
							<td class="px-5 py-3 text-right">
								<a href="<?= site_url('customers/'.$row['id']) ?>" class="text-primary hover:underline">Detail</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
