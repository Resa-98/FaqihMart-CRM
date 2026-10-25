<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$flash_error = $this->session->flashdata('driver_error');
$pct = ($progress['total'] > 0) ? (int) round($progress['done'] / $progress['total'] * 100) : 0;
$title = ($list['title'] !== NULL && $list['title'] !== '') ? $list['title'] : 'Daftar Pengiriman';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<link rel="icon" type="image/webp" href="<?= base_url('assets/icon-logo.webp') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icon-logo.webp') ?>">
<title>Daftar Pengiriman &middot; Faqih Mart</title>
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
		<div class="flex items-center justify-between gap-3">
			<span class="rounded-lg bg-white px-2 py-1">
				<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-9 w-auto">
			</span>
			<span class="shrink-0 rounded-full bg-white/20 px-2 py-1 text-xs font-medium text-white/80"><?= html_escape(delivery_status_label($list['status'])) ?></span>
		</div>
		<div class="mt-2">
			<p class="text-xs uppercase tracking-wide text-white/80">Faqih Mart &middot; Pengiriman</p>
			<h1 class="break-words text-base font-semibold sm:text-lg"><?= html_escape($title) ?></h1>
		</div>
	</div>
</header>

<main class="mx-auto max-w-2xl p-4">
	<div class="mb-4 fm-card p-4">
		<div class="flex flex-wrap items-center gap-3 text-sm text-muted">
			<span class="font-medium text-text"><?= html_escape($list['delivery_date']) ?></span>
			<span class="rounded px-2 py-1 text-xs font-medium fm-badge fm-badge-blue">
				Sesi <?= html_escape(delivery_session_label($list['session'])) ?>
			</span>
			<span><?= (int) $progress['done'] ?> dari <?= (int) $progress['total'] ?> tujuan selesai</span>
		</div>
		<div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
			<div class="h-full rounded-full bg-primary" style="width: <?= $pct ?>%"></div>
		</div>
	</div>

	<?php if ($flash_error): ?>
		<div class="mb-4 fm-alert fm-alert-warning" role="alert"><?= html_escape($flash_error) ?></div>
	<?php endif; ?>

	<?php if ( ! $can_update): ?>
		<div class="mb-4 fm-alert fm-alert-info" role="status">
			Daftar ini sudah ditandai <strong>Selesai</strong> oleh admin. Status tujuan tidak dapat diubah lagi.
		</div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="fm-card p-5 text-center">
			<p class="text-lg text-text">Belum ada tujuan pengiriman.</p>
			<p class="mt-1 text-sm text-muted">Hubungi admin untuk melengkapi daftar.</p>
		</div>
	<?php else: ?>
		<ol class="space-y-3">
			<?php foreach ($items as $i => $item): ?>
				<?php
				$maps_link     = customer_maps_link($item);
				$address       = customer_address_text($item);
				$note          = ! empty($item['note']) ? $item['note'] : ( ! empty($item['delivery_notes']) ? $item['delivery_notes'] : '');
				$unverified    = ($item['location_status'] !== 'verified');
				$order_details = isset($item['order_details']) ? trim((string) $item['order_details']) : '';
				$receipt_file  = isset($item['receipt_file']) ? (string) $item['receipt_file'] : '';
				$has_receipt   = ($receipt_file !== '' && delivery_receipt_exists($receipt_file));
				$receipt_url   = site_url('d/'.$token.'/items/'.$item['id'].'/receipt');
				?>
				<li class="fm-card p-4 <?= $item['status'] === 'done' ? 'bg-slate-50' : '' ?>">
					<div class="flex items-start gap-3">
						<span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"><?= $i + 1 ?></span>
						<div class="min-w-0 flex-1">
							<div class="flex flex-wrap items-center gap-2">
								<h2 class="text-base font-semibold text-text"><?= html_escape($item['full_name'] !== NULL ? $item['full_name'] : '(pelanggan dihapus)') ?></h2>
								<span class="rounded-full px-2 py-0.5 text-xs font-medium <?= delivery_item_status_badge_classes($item['status']) ?>"><?= html_escape(delivery_item_status_label($item['status'])) ?></span>
							</div>

							<p class="mt-2 text-sm text-text"><?= html_escape($address) ?: 'Alamat belum tersedia.' ?></p>

							<dl class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-muted">
								<?php if ( ! empty($item['housing_name'])): ?><div><dt class="inline text-muted">Perumahan: </dt><dd class="inline"><?= html_escape($item['housing_name']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['block_number'])): ?><div><dt class="inline text-muted">Blok/Nomor: </dt><dd class="inline"><?= html_escape($item['block_number']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['rt_rw'])): ?><div><dt class="inline text-muted">RT/RW: </dt><dd class="inline"><?= html_escape($item['rt_rw']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['whatsapp_number'])): ?><div><dt class="inline text-muted">WA: </dt><dd class="inline"><?= html_escape($item['whatsapp_number']) ?></dd></div><?php endif; ?>
							</dl>

							<?php if ( ! empty($item['landmark'])): ?>
								<p class="mt-2 text-sm text-text"><span class="text-muted">Patokan:</span> <?= html_escape($item['landmark']) ?></p>
							<?php endif; ?>

							<?php if ($order_details !== '' || $receipt_file !== ''): ?>
								<div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
									<p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800">Rincian Pesanan</p>
									<?php if ($order_details !== ''): ?>
										<div class="mt-1.5 break-words text-sm leading-relaxed text-emerald-950"><?= nl2br(html_escape($order_details)) ?></div>
									<?php endif; ?>
									<?php if ($has_receipt): ?>
										<a href="<?= html_escape($receipt_url) ?>" target="_blank" rel="noopener noreferrer"
											class="mt-2.5 flex w-full items-center gap-3 rounded-lg border border-emerald-200 bg-white p-2 text-left hover:bg-emerald-100">
											<img src="<?= html_escape($receipt_url) ?>" alt="Foto struk pesanan" loading="lazy"
												class="h-16 w-16 shrink-0 rounded-md object-contain">
											<span class="min-w-0 flex-1">
												<span class="block text-sm font-semibold text-emerald-800">Lihat foto struk</span>
												<span class="block text-xs text-emerald-700">Buka di tab baru</span>
											</span>
										</a>
									<?php elseif ($receipt_file !== ''): ?>
										<p class="mt-2 rounded-lg bg-red-50 px-2.5 py-2 text-xs text-red-700">Foto struk tidak ditemukan. Hubungi admin.</p>
									<?php endif; ?>
									<?php if ($order_details !== ''): ?>
										<button type="button" data-order="<?= html_escape($order_details) ?>"
											class="copy-order mt-2 flex min-h-[44px] w-full items-center justify-center rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100">Salin rincian</button>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($note !== ''): ?>
								<p class="mt-2 rounded bg-amber-50 px-3 py-2 text-sm text-amber-900"><span class="font-medium">Catatan:</span> <?= nl2br(html_escape($note)) ?></p>
							<?php endif; ?>

							<?php if ($unverified || $maps_link === NULL): ?>
								<p class="mt-2 rounded border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-800">
									<?php if ($maps_link === NULL): ?>
										Tautan Google Maps belum tersedia untuk tujuan ini. Gunakan alamat/patokan di atas.
									<?php else: ?>
										Lokasi ini belum diverifikasi admin. Pastikan titik peta sesuai sebelum berangkat.
									<?php endif; ?>
								</p>
							<?php endif; ?>

							<div class="mt-3 flex gap-2 pt-3 border-t border-slate-200 flex-wrap">
								<?php if ($maps_link !== NULL): ?>
									<a href="<?= html_escape($maps_link) ?>" target="_blank" rel="noopener noreferrer"
										class="inline-flex min-h-[44px] items-center justify-center rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Buka Google Maps</a>
								<?php else: ?>
									<span class="inline-flex min-h-[44px] items-center justify-center rounded border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-muted">Maps tidak tersedia</span>
								<?php endif; ?>
								<button type="button" class="copy-address min-h-[44px] rounded border border-border bg-surface px-4 py-2 text-sm font-medium text-text hover:bg-slate-50" data-address="<?= html_escape($address) ?>">Salin Alamat</button>
							</div>

							<?php if ($can_update): ?>
								<div class="mt-3 flex flex-wrap gap-2 border-t border-slate-200 pt-3">
									<?php
									$options = array(
										'pending'     => 'Belum',
										'in_progress' => 'Diproses',
										'done'        => 'Selesai',
									);
									?>
									<?php foreach ($options as $value => $label): ?>
										<?= form_open('d/'.$token.'/items/'.$item['id'].'/status', array('class' => 'w-full sm:w-auto')) ?>
											<input type="hidden" name="status" value="<?= $value ?>">
											<button type="submit" class="min-h-[44px] w-full rounded px-3 py-2 text-sm font-medium sm:w-auto <?= $item['status'] === $value ? 'bg-primary text-white border-primary' : 'border border-border bg-surface text-text hover:bg-slate-50' ?>"><?= $label ?></button>
										<?= form_close() ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>

	<p class="mt-8 text-center text-xs text-muted">Halaman ini hanya untuk driver. Jangan bagikan tautan kepada orang lain.</p>
</main>

<script>
(function () {
	function bindCopy(selector, attribute, promptLabel) {
		var buttons = document.querySelectorAll(selector);
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].addEventListener('click', function () {
				var btn = this;
				var text = btn.getAttribute(attribute) || '';
				var done = function () {
					var original = btn.textContent;
					btn.textContent = 'Tersalin!';
					setTimeout(function () { btn.textContent = original; }, 1500);
				};
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(done, function () { window.prompt(promptLabel, text); });
				} else {
					window.prompt(promptLabel, text);
				}
			});
		}
	}

	bindCopy('.copy-address', 'data-address', 'Salin alamat:');
	bindCopy('.copy-order', 'data-order', 'Salin rincian:');
})();
</script>
</body>
</html>
