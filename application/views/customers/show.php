<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$address_text = customer_address_text($customer);
$is_archived  = ! empty($customer['archived_at']);
?>
<div class="mb-3">
	<a href="<?= site_url('customers') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-dark">&larr; Kembali ke daftar</a>
</div>

<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
	<div class="min-w-0">
		<h1 class="break-words text-xl font-bold tracking-tight text-text sm:text-2xl"><?= html_escape($customer['full_name']) ?></h1>
		<p class="mt-2 flex flex-wrap items-center gap-2">
			<span class="break-all font-mono text-sm text-muted"><?= html_escape($customer['customer_code']) ?></span>
			<span class="rounded-full px-2 py-1 text-xs font-medium <?= location_status_badge_classes($customer['location_status']) ?>">
				<?= html_escape(location_status_label($customer['location_status'])) ?>
			</span>
		</p>
	</div>
	<div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
		<?php if ($maps_link): ?>
			<a href="<?= html_escape($maps_link) ?>" target="_blank" rel="noopener noreferrer"
				class="inline-flex w-full items-center justify-center rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark sm:w-auto">Buka Google Maps</a>
		<?php else: ?>
			<span class="inline-flex w-full items-center justify-center rounded border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-muted" title="Belum ada tautan atau koordinat">Google Maps belum tersedia</span>
		<?php endif; ?>
		<button type="button" id="copyAddress" data-address="<?= html_escape($address_text) ?>"
			class="w-full rounded border border-border bg-surface px-4 py-2 text-sm font-medium text-text hover:bg-slate-50 sm:w-auto">Salin Alamat</button>
		<a href="<?= site_url('customers/'.$customer['id'].'/edit') ?>"
			class="inline-flex w-full items-center justify-center rounded border border-border bg-surface px-4 py-2 text-sm font-medium text-text hover:bg-slate-50 sm:w-auto">Edit</a>
		<?php if ($is_archived): ?>
			<?= form_open('customers/restore/'.$customer['id'], array('class' => 'w-full sm:w-auto', 'onsubmit' => "return confirm('Pulihkan pelanggan ini?');")) ?>
				<button type="submit" class="w-full rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Pulihkan</button>
			<?= form_close() ?>
		<?php else: ?>
			<?= form_open('customers/archive/'.$customer['id'], array('class' => 'w-full sm:w-auto', 'onsubmit' => "return confirm('Arsipkan pelanggan ini? Data arsip tidak muncul di daftar aktif.');")) ?>
				<button type="submit" class="w-full rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Arsipkan</button>
			<?= form_close() ?>
		<?php endif; ?>
	</div>
</div>

<?php if ($is_archived): ?>
	<div class="mb-4 fm-alert fm-alert-info" role="status">
		Pelanggan ini berada di arsip sejak <?= html_escape($customer['archived_at']) ?>.
	</div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
	<div class="space-y-4 sm:space-y-6 lg:col-span-2">
		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-4 text-base font-semibold text-text">Informasi Alamat</h2>
			<?php if ($address_text === ''): ?>
				<p class="text-sm text-red-600">Alamat belum lengkap. Status lokasi: Belum Lengkap.</p>
			<?php else: ?>
				<dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
					<div class="sm:col-span-2">
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Alamat lengkap</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= nl2br(html_escape((string) $customer['full_address'])) ?: '<span class="text-muted">-</span>' ?></dd>
					</div>
					<div>
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Perumahan</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['housing_name']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Blok / Nomor</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['block_number']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">RT/RW</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['rt_rw']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Patokan</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['landmark']) ?: '-' ?></dd>
					</div>
					<div class="sm:col-span-2">
						<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Catatan pengiriman</dt>
						<dd class="mt-0.5 break-words text-sm text-text"><?= nl2br(html_escape((string) $customer['delivery_notes'])) ?: '<span class="text-muted">-</span>' ?></dd>
					</div>
				</dl>
			<?php endif; ?>
		</div>

		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-4 text-base font-semibold text-text">Lokasi</h2>
			<dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
				<div>
					<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Latitude</dt>
					<dd class="mt-0.5 break-all font-mono text-sm text-text"><?= html_escape($customer['latitude']) ?: '-' ?></dd>
				</div>
				<div>
					<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Longitude</dt>
					<dd class="mt-0.5 break-all font-mono text-sm text-text"><?= html_escape($customer['longitude']) ?: '-' ?></dd>
				</div>
				<div class="sm:col-span-2">
					<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Google Maps URL</dt>
					<dd class="mt-0.5">
						<?php if ( ! empty($customer['google_maps_url'])): ?>
							<a href="<?= html_escape($customer['google_maps_url']) ?>" target="_blank" rel="noopener noreferrer" class="break-all text-sm text-primary hover:text-primary-dark"><?= html_escape($customer['google_maps_url']) ?></a>
						<?php else: ?>
							<span class="text-sm text-muted">-</span>
						<?php endif; ?>
					</dd>
				</div>
			</dl>
			<?php if ($customer['location_status'] !== 'verified'): ?>
				<p class="mt-4 break-words rounded border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-800">
					Lokasi ini belum diverifikasi. Pastikan titik sesuai rumah pelanggan sebelum membagikan ke driver.
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="space-y-4 sm:space-y-6">
		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-4 text-base font-semibold text-text">Kontak</h2>
			<dl>
				<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Nomor WhatsApp</dt>
				<dd class="mt-0.5 break-all text-lg font-medium text-text"><?= html_escape($customer['whatsapp_number']) ?></dd>
			</dl>
		</div>

		<div class="fm-card p-4 sm:p-5">
			<h2 class="mb-4 text-base font-semibold text-text">Metadata</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Dibuat</dt>
					<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['created_at']) ?></dd>
				</div>
				<div>
					<dt class="text-[11px] font-medium uppercase tracking-wide text-muted">Diperbarui</dt>
					<dd class="mt-0.5 break-words text-sm text-text"><?= html_escape($customer['updated_at']) ?></dd>
				</div>
			</dl>
		</div>
	</div>
</div>

<script>
(function () {
	var btn = document.getElementById('copyAddress');
	if (!btn) { return; }
	btn.addEventListener('click', function () {
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
})();
</script>
