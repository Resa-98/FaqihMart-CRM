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
	<a href="<?= site_url('deliveries') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark">&larr; Kembali ke daftar pengiriman</a>
</div>

<div class="mb-5">
	<h1 class="break-words text-xl font-bold tracking-tight text-text sm:text-2xl"><?= html_escape($title) ?></h1>
	<p class="mt-2 flex flex-wrap items-center gap-2">
		<span class="text-sm text-muted"><?= html_escape($list['delivery_date']) ?></span>
		<span class="rounded px-2 py-1 text-xs font-medium fm-badge fm-badge-blue">
			Sesi <?= html_escape(delivery_session_label($list['session'])) ?>
		</span>
		<span class="rounded-full px-2 py-1 text-xs font-medium <?= delivery_status_badge_classes($status) ?>">
			<?= html_escape(delivery_status_label($status)) ?>
		</span>
	</p>

	<div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
		<?php if ($editable): ?>
			<a href="<?= site_url('deliveries/'.$id.'/edit') ?>" class="fm-btn fm-btn-secondary sm:w-auto">Edit</a>
		<?php endif; ?>
		<?php if ($editable && $status === 'draft'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="published">
				<button type="submit" class="fm-btn fm-btn-primary w-full sm:w-auto">Publikasikan</button>
			<?= form_close() ?>
		<?php elseif ($editable && $status === 'published'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="completed">
				<button type="submit" class="fm-btn fm-btn-primary w-full sm:w-auto">Tandai Selesai</button>
			<?= form_close() ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="draft">
				<button type="submit" class="fm-btn fm-btn-secondary w-full sm:w-auto">Kembalikan ke Draft</button>
			<?= form_close() ?>
		<?php else: ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto')) ?>
				<input type="hidden" name="status" value="published">
				<button type="submit" class="fm-btn fm-btn-secondary w-full sm:w-auto">Aktifkan Kembali</button>
			<?= form_close() ?>
		<?php endif; ?>
		<?php if ($status !== 'cancelled'): ?>
			<?= form_open('deliveries/'.$id.'/status', array('class' => 'w-full sm:w-auto', 'onsubmit' => "return confirm('Batalkan daftar pengiriman ini?');")) ?>
				<input type="hidden" name="status" value="cancelled">
				<button type="submit" class="fm-btn fm-btn-danger w-full sm:w-auto">Batalkan</button>
			<?= form_close() ?>
		<?php endif; ?>
	</div>
</div>

<div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
	<!-- Kolom utama: daftar tujuan -->
	<div class="space-y-4 sm:space-y-6 lg:col-span-2">
		<div class="fm-card p-4 sm:p-5">
			<div class="mb-3 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
				<h2 class="text-base font-semibold text-text">Progres</h2>
				<span class="text-sm text-muted"><?= (int) $progress['done'] ?> dari <?= (int) $progress['total'] ?> tujuan selesai</span>
			</div>
			<div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
				<div class="h-full rounded-full bg-primary" style="width: <?= $pct ?>%"></div>
			</div>
			<p class="mt-2 break-words text-xs text-muted">
				<?= (int) $progress['pending'] ?> belum dikunjungi &middot;
				<?= (int) $progress['in_progress'] ?> sedang diproses &middot;
				<?= (int) $progress['done'] ?> selesai
			</p>
		</div>

		<div id="tujuan" class="fm-card">
			<div class="border-b border-border px-4 py-3.5 sm:px-5 sm:py-4">
				<h2 class="text-base font-semibold text-text">Tujuan Pengiriman (<?= count($items) ?>)</h2>
			</div>

			<?php if (empty($items)): ?>
				<div class="px-5 py-10 text-center">
					<p class="text-sm text-muted">Belum ada tujuan pada daftar ini.</p>
					<p class="mt-1 text-sm text-muted">Cari pelanggan pada panel Tambah Tujuan untuk menambahkan tujuan.</p>
				</div>
			<?php else: ?>
				<div class="divide-y">
					<?php foreach ($items as $i => $item): ?>
						<?php
						$is_first      = ($i === 0);
						$is_last       = ($i === count($items) - 1);
						$order_details = isset($item['order_details']) ? trim((string) $item['order_details']) : '';
						$receipt_file  = isset($item['receipt_file']) ? (string) $item['receipt_file'] : '';
						$has_receipt   = ($receipt_file !== '' && delivery_receipt_exists($receipt_file));
						$receipt_url   = site_url('deliveries/'.$id.'/items/'.$item['id'].'/receipt');
						$has_order     = ($order_details !== '' || $receipt_file !== '');
						?>
						<div id="item-<?= (int) $item['id'] ?>" class="scroll-mt-6 p-4 sm:p-5">
							<div class="flex flex-wrap items-start gap-x-3 gap-y-2">
								<span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-muted"><?= $i + 1 ?></span>
								<div class="min-w-0 flex-1">
									<div class="flex flex-wrap items-center gap-x-2 gap-y-1">
										<a href="<?= site_url('customers/'.$item['customer_id']) ?>" class="break-words font-medium text-text hover:text-primary">
											<?= html_escape($item['full_name'] !== NULL ? $item['full_name'] : '(pelanggan dihapus)') ?>
										</a>
										<span class="rounded-full px-2 py-0.5 text-xs font-medium <?= delivery_item_status_badge_classes($item['status']) ?>">
											<?= html_escape(delivery_item_status_label($item['status'])) ?>
										</span>
									</div>
									<p class="mt-1 break-words text-sm text-muted"><?= html_escape(customer_address_text($item)) ?: '<span class="text-muted">Alamat belum tersedia</span>' ?></p>
									<?php if ( ! empty($item['whatsapp_number'])): ?>
										<p class="mt-1 break-words text-xs text-muted">WA: <?= html_escape($item['whatsapp_number']) ?></p>
									<?php endif; ?>
								</div>
								<?php if ($editable): ?>
									<div class="flex w-full shrink-0 flex-wrap gap-1 sm:w-auto">
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/move/up', array('class' => 'inline')) ?>
											<button type="submit" <?= $is_first ? 'disabled' : '' ?> class="fm-action-btn <?= $is_first ? 'text-muted' : 'text-text hover:bg-slate-50' ?>" title="Naikkan urutan" aria-label="Naikkan urutan">&uarr;</button>
										<?= form_close() ?>
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/move/down', array('class' => 'inline')) ?>
											<button type="submit" <?= $is_last ? 'disabled' : '' ?> class="fm-action-btn <?= $is_last ? 'text-muted' : 'text-text hover:bg-slate-50' ?>" title="Turunkan urutan" aria-label="Turunkan urutan">&darr;</button>
										<?= form_close() ?>
										<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/remove', array('class' => 'inline', 'onsubmit' => "return confirm('Hapus tujuan ini dari daftar?');")) ?>
											<button type="submit" class="fm-action-btn fm-action-btn--danger" title="Hapus tujuan">Hapus</button>
										<?= form_close() ?>
									</div>
								<?php endif; ?>
							</div>

							<?php if ($has_order): ?>
								<div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 sm:p-3.5">
									<p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800">Rincian Pesanan</p>
									<?php if ($order_details !== ''): ?>
										<div class="mt-1.5 break-words text-sm leading-relaxed text-emerald-950"><?= nl2br(html_escape($order_details)) ?></div>
									<?php endif; ?>
									<?php if ($has_receipt): ?>
										<a href="<?= html_escape($receipt_url) ?>" target="_blank" rel="noopener noreferrer"
											class="mt-2.5 inline-flex max-w-full items-center gap-2 rounded-lg border border-emerald-200 bg-white p-1.5 pr-3 text-sm font-medium text-emerald-800 hover:bg-emerald-100">
											<img src="<?= html_escape($receipt_url) ?>" alt="Foto struk pesanan" loading="lazy"
												class="h-14 w-14 shrink-0 rounded object-contain">
											<span class="min-w-0 break-words">Lihat foto struk</span>
										</a>
									<?php elseif ($receipt_file !== ''): ?>
										<p class="mt-2 rounded-lg bg-red-50 px-2.5 py-2 text-xs text-red-700">File struk tidak ditemukan di server. Silakan unggah ulang.</p>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($editable): ?>
								<details class="mt-3 overflow-hidden rounded-xl border border-border bg-slate-50" <?= ($receipt_file !== '' && ! $has_receipt) ? 'open' : '' ?>>
									<summary class="cursor-pointer px-3 py-3 text-sm font-semibold text-primary"><?= $has_order ? 'Ubah rincian pesanan' : 'Tambah rincian pesanan' ?></summary>
									<?= form_open_multipart('deliveries/'.$id.'/items/'.$item['id'].'/order', array('class' => 'space-y-3 border-t border-border px-3 py-3 sm:px-4')) ?>
										<div>
											<label for="orderDetails<?= (int) $item['id'] ?>" class="fm-field-label">Rincian produk</label>
											<textarea id="orderDetails<?= (int) $item['id'] ?>" name="order_details" rows="4" maxlength="2000"
												placeholder="Contoh:&#10;3x Beras 5 kg&#10;2x Minyak goreng 2 L"
												class="fm-input w-full text-base sm:text-sm"><?= html_escape($order_details) ?></textarea>
											<p class="mt-1 text-xs text-muted">Satu baris untuk satu produk. Maksimal 2000 karakter.</p>
										</div>
										<div>
											<label for="orderReceipt<?= (int) $item['id'] ?>" class="fm-field-label">Foto struk <span class="font-normal text-muted">(opsional)</span></label>
											<input type="file" id="orderReceipt<?= (int) $item['id'] ?>" name="receipt" accept="image/jpeg,image/png"
												class="block w-full cursor-pointer rounded-lg border border-border bg-white text-sm text-muted file:mr-3 file:border-0 file:bg-primary file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-dark">
											<p class="mt-1 text-xs text-muted">JPG/PNG, maksimal 5 MB. Unggah file baru untuk mengganti struk.</p>
											<?php if ($receipt_file !== '' && ! $has_receipt): ?>
												<p class="mt-1 text-xs text-red-700">File struk lama tidak ditemukan di server. Unggah file baru atau hapus referensinya.</p>
											<?php endif; ?>
											<?php if ($receipt_file !== ''): ?>
												<label class="mt-2 flex min-h-[44px] items-center gap-2 rounded-lg bg-white px-2 text-sm text-red-700">
													<input type="checkbox" name="remove_receipt" value="1" class="h-4 w-4 rounded border-border">
													Hapus <?= $has_receipt ? 'struk saat ini' : 'referensi struk lama' ?>
												</label>
											<?php endif; ?>
										</div>
										<button type="submit" class="fm-btn fm-btn-primary min-h-[44px] w-full sm:w-auto">Simpan rincian</button>
									<?= form_close() ?>
								</details>
							<?php endif; ?>

							<?php if ($editable): ?>
								<?= form_open('deliveries/'.$id.'/items/'.$item['id'].'/note', array('class' => 'mt-3 flex flex-col gap-2 sm:flex-row sm:items-center')) ?>
									<input type="text" name="note" maxlength="500" value="<?= html_escape((string) $item['note']) ?>"
										placeholder="Catatan pengiriman khusus tujuan ini (patokan, jam, dsb.)"
										class="fm-input w-full min-w-0">
									<button type="submit" class="fm-btn fm-btn-secondary w-full shrink-0 sm:w-auto">Simpan catatan</button>
								<?= form_close() ?>
							<?php elseif ( ! empty($item['note'])): ?>
								<p class="mt-2 break-words rounded bg-slate-50 px-3 py-2 text-sm text-text">Catatan: <?= html_escape($item['note']) ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Sidebar: tautan driver + tambah pelanggan -->
	<div class="space-y-4 sm:space-y-6">
		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-text">Tautan Driver</h2>

			<?php if ($show_link): ?>
				<label for="driverLink" class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted">Tautan akses (bagikan ke driver)</label>
				<input type="text" id="driverLink" readonly value="<?= html_escape($driver_url) ?>"
					class="mb-2 w-full rounded border border-border bg-slate-50 px-3 py-2 text-xs text-text">
				<div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
					<button type="button" id="copyDriverLink" data-link="<?= html_escape($driver_url) ?>"
						class="fm-btn fm-btn-primary sm:w-auto">Salin tautan</button>
					<a href="https://wa.me/?text=<?= $wa_text ?>" target="_blank" rel="noopener noreferrer"
						class="fm-btn fm-btn-secondary text-center sm:w-auto">Bagikan WhatsApp</a>
				</div>
				<p class="mt-2 break-words text-xs text-muted">Siapa pun yang memiliki tautan ini dapat melihat daftar dan memperbarui status tujuan.</p>
			<?php else: ?>
				<p class="text-sm text-muted">
					Belum ada tautan aktif.
					<?= in_array($status, array('draft', 'cancelled'), TRUE) ? 'Publikasikan daftar untuk membuat tautan driver.' : 'Buat tautan baru untuk dibagikan.' ?>
				</p>
			<?php endif; ?>

			<div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 border-t border-border pt-3">
				<?= form_open('deliveries/'.$id.'/regenerate', array('class' => 'inline', 'onsubmit' => "return confirm('Buat tautan baru? Tautan lama tidak akan berlaku lagi.');")) ?>
					<button type="submit" class="text-sm font-medium text-primary hover:text-primary-dark"><?= $show_link ? 'Buat ulang tautan' : 'Buat tautan sekarang' ?></button>
				<?= form_close() ?>
				<?php if ($has_token && $show_link): ?>
					<?= form_open('deliveries/'.$id.'/revoke', array('class' => 'inline', 'onsubmit' => "return confirm('Cabut akses tautan ini? Tautan akan langsung tidak berlaku.');")) ?>
						<button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Cabut akses</button>
					<?= form_close() ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-text">Tambah Tujuan</h2>

			<?php if ( ! $editable): ?>
				<p class="text-sm text-muted">Daftar yang sudah selesai/dibatalkan tidak dapat diubah.</p>
			<?php else: ?>
				<form method="get" action="<?= site_url('deliveries/'.$id) ?>" class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-start">
					<div class="fm-suggest-wrap min-w-0 flex-1" data-customer-suggest
						data-suggest-endpoint="<?= site_url('customers/suggest') ?>"
						data-suggest-view="active" data-suggest-status="">
						<label for="deliveryCustomerSearch" class="sr-only">Cari pelanggan untuk ditambahkan</label>
						<input type="text" name="q" id="deliveryCustomerSearch" value="<?= html_escape($q) ?>"
							placeholder="Nama, WhatsApp, alamat, blok, atau RT/RW..."
							class="fm-input fm-suggest-input" autocomplete="off" enterkeyhint="search"
							role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false"
							aria-describedby="deliverySuggestHelp" data-suggest-input>
						<div class="fm-suggest-list" data-suggest-list hidden></div>
						<p id="deliverySuggestHelp" class="sr-only">Ketik minimal 2 karakter untuk melihat saran kata kunci. Gunakan panah atas dan bawah untuk memilih, lalu Enter untuk mencari.</p>
						<p class="sr-only" role="status" aria-live="polite" data-suggest-feedback></p>
					</div>
					<button type="submit" class="fm-btn fm-btn-primary min-h-[44px] w-full shrink-0 sm:w-auto">Cari</button>
				</form>

				<?php if ($q === ''): ?>
					<p class="text-sm text-muted">Masukkan kata kunci untuk mencari pelanggan.</p>
				<?php elseif (empty($customers)): ?>
					<p class="break-words text-sm text-muted">Tidak ada pelanggan aktif yang cocok dengan &ldquo;<?= html_escape($q) ?>&rdquo;.</p>
				<?php else: ?>
					<ul class="space-y-2">
						<?php foreach ($customers as $c): ?>
							<li class="rounded border border-border p-3">
								<p class="break-words text-sm font-medium text-text"><?= html_escape($c['full_name']) ?> <span class="break-all font-mono text-xs text-muted"><?= html_escape($c['customer_code']) ?></span></p>
								<p class="mt-1 break-words text-xs text-muted"><?= html_escape(customer_address_text($c)) ?: 'Alamat belum tersedia' ?></p>
								<p class="mt-1 flex flex-wrap items-center gap-2 text-xs">
									<span class="rounded-full px-2 py-0.5 font-medium <?= location_status_badge_classes($c['location_status']) ?>"><?= html_escape(location_status_label($c['location_status'])) ?></span>
									<?php if ( ! empty($c['whatsapp_number'])): ?><span class="break-words text-muted">WA: <?= html_escape($c['whatsapp_number']) ?></span><?php endif; ?>
								</p>
								<div class="mt-2">
									<?php if (isset($item_customer_ids[(int) $c['id']])): ?>
										<span class="text-xs text-muted">Sudah ada di daftar ini</span>
									<?php else: ?>
										<?= form_open('deliveries/'.$id.'/items', array('class' => 'inline')) ?>
											<input type="hidden" name="customer_id" value="<?= (int) $c['id'] ?>">
											<button type="submit" class="fm-btn fm-btn-primary fm-btn-sm">+ Tambah</button>
										<?= form_close() ?>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-3 text-base font-semibold text-text">Informasi</h2>
			<dl class="space-y-2 text-sm">
				<div class="flex flex-wrap justify-between gap-x-2">
					<dt class="text-muted">Dibuat</dt>
					<dd class="break-words text-right text-text"><?= html_escape($list['created_at']) ?></dd>
				</div>
				<?php if ( ! empty($list['published_at'])): ?>
					<div class="flex flex-wrap justify-between gap-x-2">
						<dt class="text-muted">Dipublikasikan</dt>
						<dd class="break-words text-right text-text"><?= html_escape($list['published_at']) ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( ! empty($list['token_revoked_at'])): ?>
					<div class="flex flex-wrap justify-between gap-x-2">
						<dt class="text-muted">Akses dicabut</dt>
						<dd class="break-words text-right text-text"><?= html_escape($list['token_revoked_at']) ?></dd>
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
