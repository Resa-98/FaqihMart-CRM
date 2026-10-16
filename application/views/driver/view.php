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
<link rel="icon" type="image/webp" href="<?= base_url('assets/icon-logo.webp') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icon-logo.webp') ?>">
<title>Daftar Pengiriman &middot; Faqih Mart</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#16A34A', dark: '#15803D' } } } } };
</script>
</head>
<body class="bg-gray-100 text-gray-800">

<header class="bg-primary px-4 py-3 text-white">
	<div class="mx-auto max-w-2xl">
		<div class="flex items-center justify-between gap-3">
			<span class="rounded-lg bg-white px-2 py-1">
				<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="h-9 w-auto">
			</span>
			<span class="shrink-0 rounded-full bg-white/20 px-2 py-1 text-xs font-small text-white/80"><?= html_escape(delivery_status_label($list['status'])) ?></span>
		</div>
		<div class="mt-2">
			<p class="text-xs uppercase tracking-wide text-white/80">Faqih Mart &middot; Pengiriman</p>
			<h1 class="break-words text-base font-semibold sm:text-lg"><?= html_escape($title) ?></h1>
		</div>
	</div>
</header>

<main class="mx-auto max-w-2xl p-4">
	<div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
		<div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
			<span class="font-medium text-gray-800"><?= html_escape($list['delivery_date']) ?></span>
			<span class="rounded px-2 py-1 text-xs font-medium <?= $list['session'] === 'pagi' ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800' ?>">
				Sesi <?= html_escape(delivery_session_label($list['session'])) ?>
			</span>
			<span><?= (int) $progress['done'] ?> dari <?= (int) $progress['total'] ?> tujuan selesai</span>
		</div>
		<div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200">
			<div class="h-full rounded-full bg-primary" style="width: <?= $pct ?>%"></div>
		</div>
	</div>

	<?php if ($flash_error): ?>
		<div class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert"><?= html_escape($flash_error) ?></div>
	<?php endif; ?>

	<?php if ( ! $can_update): ?>
		<div class="mb-4 rounded border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-700" role="status">
			Daftar ini sudah ditandai <strong>Selesai</strong> oleh admin. Status tujuan tidak dapat diubah lagi.
		</div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="rounded-lg bg-white px-5 py-12 text-center text-gray-500 shadow-sm">
			<p class="text-lg">Belum ada tujuan pengiriman.</p>
			<p class="mt-1 text-sm">Hubungi admin untuk melengkapi daftar.</p>
		</div>
	<?php else: ?>
		<ol class="space-y-4">
			<?php foreach ($items as $i => $item): ?>
				<?php
				$maps_link  = customer_maps_link($item);
				$address    = customer_address_text($item);
				$note        = ! empty($item['note']) ? $item['note'] : ( ! empty($item['delivery_notes']) ? $item['delivery_notes'] : '');
				$unverified  = ($item['location_status'] !== 'verified');
				?>
				<li class="rounded-lg bg-white p-4 shadow-sm <?= $item['status'] === 'done' ? 'opacity-75' : '' ?>">
					<div class="flex items-start gap-3">
						<span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"><?= $i + 1 ?></span>
						<div class="min-w-0 flex-1">
							<div class="flex flex-wrap items-center gap-2">
								<h2 class="text-base font-semibold text-gray-800"><?= html_escape($item['full_name'] !== NULL ? $item['full_name'] : '(pelanggan dihapus)') ?></h2>
								<span class="rounded-full px-2 py-0.5 text-xs font-medium <?= delivery_item_status_badge_classes($item['status']) ?>"><?= html_escape(delivery_item_status_label($item['status'])) ?></span>
							</div>

							<p class="mt-2 text-sm text-gray-700"><?= html_escape($address) ?: 'Alamat belum tersedia.' ?></p>

							<dl class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-600">
								<?php if ( ! empty($item['housing_name'])): ?><div><dt class="inline text-gray-400">Perumahan: </dt><dd class="inline"><?= html_escape($item['housing_name']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['block_number'])): ?><div><dt class="inline text-gray-400">Blok/Nomor: </dt><dd class="inline"><?= html_escape($item['block_number']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['rt_rw'])): ?><div><dt class="inline text-gray-400">RT/RW: </dt><dd class="inline"><?= html_escape($item['rt_rw']) ?></dd></div><?php endif; ?>
								<?php if ( ! empty($item['whatsapp_number'])): ?><div><dt class="inline text-gray-400">WA: </dt><dd class="inline"><?= html_escape($item['whatsapp_number']) ?></dd></div><?php endif; ?>
							</dl>

							<?php if ( ! empty($item['landmark'])): ?>
								<p class="mt-2 text-sm text-gray-700"><span class="text-gray-400">Patokan:</span> <?= html_escape($item['landmark']) ?></p>
							<?php endif; ?>

							<?php if ($note !== ''): ?>
								<p class="mt-2 rounded bg-amber-50 px-3 py-2 text-sm text-amber-900"><span class="font-medium">Catatan:</span> <?= nl2br(html_escape($note)) ?></p>
							<?php endif; ?>

							<?php if ($unverified || $maps_link === NULL): ?>
								<p class="mt-2 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
									<?php if ($maps_link === NULL): ?>
										Tautan Google Maps belum tersedia untuk tujuan ini. Gunakan alamat/patokan di atas.
									<?php else: ?>
										Lokasi ini belum diverifikasi admin. Pastikan titik peta sesuai sebelum berangkat.
									<?php endif; ?>
								</p>
							<?php endif; ?>

							<div class="mt-3 flex  gap-2 pt-3 border-t border-gray-100 flex-wrap">
								<?php if ($maps_link !== NULL): ?> 
									<a href="<?= html_escape($maps_link) ?>" target="_blank" rel="noopener noreferrer"
										class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark  ">Buka Google Maps</a>
								<?php else: ?>
									<span class="rounded border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">Maps tidak tersedia</span>
								<?php endif; ?>
								<button type="button" class="copy-address rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" data-address="<?= html_escape($address) ?>">Salin Alamat</button>
							</div>

							<?php if ($can_update): ?>
								<div class="mt-3 flex flex-wrap gap-2 border-t border-gray-100 pt-3">
									<?php
									$options = array(
										'pending'     => 'Belum',
										'in_progress' => 'Diproses',
										'done'        => 'Selesai',
									);
									?>
									<?php foreach ($options as $value => $label): ?>
										<?= form_open('d/'.$token.'/items/'.$item['id'].'/status', array('class' => 'inline')) ?>
											<input type="hidden" name="status" value="<?= $value ?>">
											<button type="submit" class="rounded px-3 py-1 text-sm font-medium <?= $item['status'] === $value ? 'bg-gray-800 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' ?>"><?= $label ?></button>
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

	<p class="mt-8 text-center text-xs text-gray-400">Halaman ini hanya untuk driver. Jangan bagikan tautan kepada orang lain.</p>
</main>

<script>
(function () {
	var buttons = document.querySelectorAll('.copy-address');
	for (var i = 0; i < buttons.length; i++) {
		buttons[i].addEventListener('click', function () {
			var btn = this;
			var text = btn.getAttribute('data-address') || '';
			var done = function () {
				var original = btn.textContent;
				btn.textContent = 'Tersalin!';
				setTimeout(function () { btn.textContent = original; }, 1500);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(done, function () { window.prompt('Salin alamat:', text); });
			} else {
				window.prompt('Salin alamat:', text);
			}
		});
	}
})();
</script>
</body>
</html>
