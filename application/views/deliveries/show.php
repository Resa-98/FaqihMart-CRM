<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$id          = (int) $list['id'];
$title       = ($list['title'] !== NULL && $list['title'] !== '') ? $list['title'] : 'Daftar Pengiriman';
$status      = $list['status'];
$show_link   = ($driver_url !== NULL && in_array($status, array('published', 'completed'), TRUE));
$has_token   = ( ! empty($list['token_hash']) && empty($list['token_revoked_at']));
$pct         = ($progress['total'] > 0) ? (int) round($progress['done'] / $progress['total'] * 100) : 0;

$item_customer_ids = array();
foreach ($items as $it)
{
	$item_customer_ids[(int) $it['customer_id']] = TRUE;
}

$wa_text = rawurlencode('Daftar pengiriman '.$list['delivery_date'].' ('.delivery_session_label($list['session']).'): '.$driver_url);
?>
<div class="mb-3">
	<a href="<?= site_url('deliveries') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">&larr; Kembali ke daftar pengiriman</a>
</div>

<div class="mb-5">
	<h1 class="break-words text-xl font-bold tracking-tight text-gray-900 sm:text-2xl"><?= html_escape($title) ?></h1>
	<p class="mt-2 flex flex-wrap items-center gap-2">
		<span class="text-sm text-gray-500"><?= html_escape($list['delivery_date']) ?></span>
		<span class="rounded px-2 py-1 text-xs font-medium <?= $list['session'] === 'pagi' ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800' ?>">
			Sesi <?= html_escape(delivery_session_label($list['session'])) ?>
		</span>
		<span class="rounded-full px-2 py-1 text-xs font-medium <?= delivery_status_badge_classes($status) ?>">
			<?= html_escape(delivery_status_label($status)) ?>
		</span>
	</p>

	<div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
		<?php if ($editable): ?>
			<a href="<?= site_url('deliveries/'.$id.'/edit') ?>" class="inline-flex w-full items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">Edit</a>
		<?php endif; ?>
		<?php if ($editable && $status === 'draft'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="published">
				<button type="submit" class="w-full rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Publikasikan</button>
			<?= form_close() ?>
		<?php elseif ($editable && $status === 'published'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="completed">
				<button type="submit" class="w-full rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Tandai Selesai</button>
			<?= form_close() ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="draft">
				<button type="submit" class="w-full rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Kembalikan ke Draft</button>
			<?= form_close() ?>
		<?php else: ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="published">
				<button type="submit" class="w-full rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Aktifkan Kembali</button>
			<?= form_close() ?>
		<?php endif; ?>
		<?php if ($status !== 'cancelled'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto', 'onsubmit' => "return confirm('Batalkan daftar pengiriman ini?');")) ?>
				<input type="hidden" name="status" value="cancelled">
				<button type="submit" class="w-full rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Batalkan</button>
			<?= form_close() ?>
		<?php endif; ?>
	</div>
</div>

<div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
	<!-- Kolom utama: daftar tujuan -->
	<div class="space-y-4 sm:space-y-6 lg:col-span-2">
		<div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
			<div class="mb-3 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
				<h2 class="text-base font-semibold text-gray-900">Progres</h2>
				<span class="text-sm text-gray-600"><?= (int) $progress['done'] ?> dari <?= (int) $progress['total'] ?> tujuan selesai</span>
			</div>
			<div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
				<div class="h-full rounded-full bg-primary" style="width: <?= $pct ?>%"></div>
			</div>
			<p class="mt-2 break-words text-xs text-gray-500">
				<?= (int) $progress['pending'] ?> belum dikunjungi &middot;
				<?= (int) $progress['in_progress'] ?> sedang diproses &middot;
				<?= (int) $progress['done'] ?> selesai
			</p>
		</div>

		<div id="tujuan" class="rounded-lg bg-white shadow-sm">
			<div class="border-b border-gray-200 px-4 py-3.5 sm:px-5 sm:py-4">
				<h2 class="text-base font-semibold text-gray-900">Tujuan Pengiriman (<?= count($items) ?>)</h2>
			</div>

			<?php if (empty($items)): ?>
				<div class="px-5 py-10 text-center">
					<p class="text-sm text-gray-600">Belum ada tujuan pada daftar ini.</p>
					<p class="mt-1 text-sm text-gray-500">Cari pelanggan pada panel Tambah Tujuan untuk menambahkan tujuan.</p>
				</div>
			<?php else: ?>
				<div class="divide-y divide-gray-100">
					<?php foreach ($items as $i => $item): ?>
						<?php $is_first = ($i === 0); $is_last = ($i === count($items) - 1); ?>
						<div class="p-4 sm:p-5">
							<div class="flex flex-wrap items-start gap-x-3 gap-y-2">
								<span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600"><?= $i + 1 ?></span>
								<div class="min-w-0 flex-1">
									<div class="flex flex-wrap items-center gap-x-2 gap-y-1">
										<a href="<?= site_url('customers/'.$item['customer_id']) ?>" class="break-words font-medium text-gray-900 hover:text-primary">
											<?= html_escape($item['full_name'] !== NULL ? $item['full_name'] : '(pelanggan dihapus)') ?>
										</a>
										<span class="rounded-full px-2 py-0.5 text-xs font-medium <?= delivery_item_status_badge_classes($item['status']) ?>">
											<?= html_escape(delivery_item_status_label($item['status'])) ?>
										</span>
									</div>
									<p class="mt-1 break-words text-sm text-gray-600"><?= html_escape(customer_address_text($item)) ?: '<span class="text-gray-400">Alamat belum tersedia</span>' ?></p>
									<?php if ( ! empty($item['whatsapp_number'])): ?>
										<p class="mt-1 break-words text-xs text-gray-500">WA: <?= html_escape($item['whatsapp_number']) ?></p>
									<?php endif; ?>
								</div>
								<?php if ($editable): ?>
									<div class="flex w-full shrink-0 flex-wrap gap-1 sm:w-auto">
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/move/up', array('class' => 'inline')) ?>
											<button type="submit" <?= $is_first ? 'disabled' : '' ?> class="rounded border border-gray-300 px-3 py-1.5 text-sm <?= $is_first ? 'cursor-not-allowed text-gray-300' : 'text-gray-600 hover:bg-gray-50' ?>" title="Naikkan urutan" aria-label="Naikkan urutan">&uarr;</button>
										<?= form_close() ?>
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/move/down', array('class' => 'inline')) ?>
											<button type="submit" <?= $is_last ? 'disabled' : '' ?> class="rounded border border-gray-300 px-3 py-1.5 text-sm <?= $is_last ? 'cursor-not-allowed text-gray-300' : 'text-gray-600 hover:bg-gray-50' ?>" title="Turunkan urutan" aria-label="Turunkan urutan">&darr;</button>
										<?= form_close() ?>
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/remove', array('class' => 'inline', 'onsubmit' => "return confirm('Hapus tujuan ini dari daftar?');")) ?>
											<button type="submit" class="rounded border border-gray-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50" title="Hapus tujuan">Hapus</button>
										<?= form_close() ?>
									</div>
								<?php endif; ?>
							</div>

							<?php if ($editable): ?>
								<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/note', array('class' => 'mt-3 flex flex-col gap-2 sm:flex-row sm:items-center')) ?>
									<input type="text" name="note" maxlength="500" value="<?= html_escape((string) $item['note']) ?>"
										placeholder="Catatan pengiriman khusus tujuan ini (patokan, jam, dsb.)"
										class="w-full min-w-0 rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
									<button type="submit" class="w-full shrink-0 rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">Simpan catatan</button>
								<?= form_close() ?>
							<?php elseif ( ! empty($item['note'])): ?>
								<p class="mt-2 break-words rounded bg-gray-50 px-3 py-2 text-sm text-gray-700">Catatan: <?= html_escape($item['note']) ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Sidebar: tautan driver + tambah pelanggan -->
	<div class="space-y-4 sm:space-y-6">
		<div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-gray-900">Tautan Driver</h2>

			<?php if ($show_link): ?>
				<label for="driverLink" class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Tautan akses (bagikan ke driver)</label>
				<input type="text" id="driverLink" readonly value="<?= html_escape($driver_url) ?>"
					class="mb-2 w-full rounded border border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-700">
				<div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
					<button type="button" id="copyDriverLink" data-link="<?= html_escape($driver_url) ?>"
						class="w-full rounded bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary-dark sm:w-auto">Salin tautan</button>
					<a href="https://wa.me/?text=<?= $wa_text ?>" target="_blank" rel="noopener noreferrer"
						class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">Bagikan WhatsApp</a>
				</div>
				<p class="mt-2 break-words text-xs text-gray-500">Siapa pun yang memiliki tautan ini dapat melihat daftar dan memperbarui status tujuan.</p>
			<?php else: ?>
				<p class="text-sm text-gray-500">
					Belum ada tautan aktif.
					<?= in_array($status, array('draft', 'cancelled'), TRUE) ? 'Publikasikan daftar untuk membuat tautan driver.' : 'Buat tautan baru untuk dibagikan.' ?>
				</p>
			<?php endif; ?>

			<div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 border-t border-gray-100 pt-3">
				<?= form_open('deliveries/'.$id.'/regenerate', array('class' => 'inline', 'onsubmit' => "return confirm('Buat tautan baru? Tautan lama tidak akan berlaku lagi.');")) ?>
					<button type="submit" class="text-sm font-medium text-primary hover:underline"><?= $show_link ? 'Buat ulang tautan' : 'Buat tautan sekarang' ?></button>
				<?= form_close() ?>
				<?php if ($has_token && $show_link): ?>
					<?= form_open('deliveries/'.$id.'/revoke', array('class' => 'inline', 'onsubmit' => "return confirm('Cabut akses tautan ini? Tautan akan langsung tidak berlaku.');")) ?>
						<button type="submit" class="text-sm font-medium text-red-600 hover:underline">Cabut akses</button>
					<?= form_close() ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-gray-900">Tambah Tujuan</h2>

			<?php if ( ! $editable): ?>
				<p class="text-sm text-gray-500">Daftar yang sudah selesai/dibatalkan tidak dapat diubah.</p>
			<?php else: ?>
				<form method="get" action="<?= site_url('deliveries/'.$id) ?>" class="mb-3 flex gap-2">
					<input type="text" name="q" value="<?= html_escape($q) ?>" placeholder="Nama, WhatsApp, atau alamat..."
						class="w-full min-w-0 flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
					<button type="submit" class="shrink-0 rounded bg-gray-800 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">Cari</button>
				</form>

				<?php if ($q === ''): ?>
					<p class="text-sm text-gray-500">Masukkan kata kunci untuk mencari pelanggan.</p>
				<?php elseif (empty($customers)): ?>
					<p class="break-words text-sm text-gray-500">Tidak ada pelanggan aktif yang cocok dengan &ldquo;<?= html_escape($q) ?>&rdquo;.</p>
				<?php else: ?>
					<ul class="space-y-2">
						<?php foreach ($customers as $c): ?>
							<li class="rounded border border-gray-200 p-3">
								<p class="break-words text-sm font-medium text-gray-900"><?= html_escape($c['full_name']) ?> <span class="break-all font-mono text-xs text-gray-400"><?= html_escape($c['customer_code']) ?></span></p>
								<p class="mt-1 break-words text-xs text-gray-600"><?= html_escape(customer_address_text($c)) ?: 'Alamat belum tersedia' ?></p>
								<p class="mt-1 flex flex-wrap items-center gap-2 text-xs">
									<span class="rounded-full px-2 py-0.5 font-medium <?= location_status_badge_classes($c['location_status']) ?>"><?= html_escape(location_status_label($c['location_status'])) ?></span>
									<?php if ( ! empty($c['whatsapp_number'])): ?><span class="break-words text-gray-500">WA: <?= html_escape($c['whatsapp_number']) ?></span><?php endif; ?>
								</p>
								<div class="mt-2">
									<?php if (isset($item_customer_ids[(int) $c['id']])): ?>
										<span class="text-xs text-gray-400">Sudah ada di daftar ini</span>
									<?php else: ?>
										<?= form_open('deliveries/'.$id.'/items', array('class' => 'inline')) ?>
											<input type="hidden" name="customer_id" value="<?= (int) $c['id'] ?>">
											<button type="submit" class="rounded bg-primary px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-dark">+ Tambah</button>
										<?= form_close() ?>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-gray-900">Informasi</h2>
			<dl class="space-y-2 text-sm">
				<div class="flex flex-wrap justify-between gap-x-2">
					<dt class="text-gray-500">Dibuat</dt>
					<dd class="break-words text-right text-gray-700"><?= html_escape($list['created_at']) ?></dd>
				</div>
				<?php if ( ! empty($list['published_at'])): ?>
					<div class="flex flex-wrap justify-between gap-x-2">
						<dt class="text-gray-500">Dipublikasikan</dt>
						<dd class="break-words text-right text-gray-700"><?= html_escape($list['published_at']) ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( ! empty($list['token_revoked_at'])): ?>
					<div class="flex flex-wrap justify-between gap-x-2">
						<dt class="text-gray-500">Akses dicabut</dt>
						<dd class="break-words text-right text-gray-700"><?= html_escape($list['token_revoked_at']) ?></dd>
					</div>
				<?php endif; ?>
			</dl>
		</div>
	</div>
</div>

<script>
(function () {
	var btn = document.getElementById('copyDriverLink');
	if (!btn) { return; }
	btn.addEventListener('click', function () {
		var link = btn.getAttribute('data-link') || '';
		var done = function () {
			var original = btn.textContent;
			btn.textContent = 'Tersalin!';
			setTimeout(function () { btn.textContent = original; }, 1500);
		};
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(link).then(done, function () { window.prompt('Salin tautan:', link); });
		} else {
			window.prompt('Salin tautan:', link);
		}
	});
})();
</script>
