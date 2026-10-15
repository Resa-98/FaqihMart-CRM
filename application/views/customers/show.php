<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$address_text = customer_address_text($customer);
$is_archived  = ! empty($customer['archived_at']);
?>
<div class="mb-4">
	<a href="<?= site_url('customers') ?>" class="text-sm text-primary hover:underline">&larr; Kembali ke daftar</a>
</div>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
	<div>
		<h1 class="text-2xl font-bold text-gray-800"><?= html_escape($customer['full_name']) ?></h1>
		<p class="mt-1 flex items-center gap-3 text-sm text-gray-500">
			<span class="font-mono"><?= html_escape($customer['customer_code']) ?></span>
			<span class="rounded-full px-2 py-1 text-xs font-medium <?= location_status_badge_classes($customer['location_status']) ?>">
				<?= html_escape(location_status_label($customer['location_status'])) ?>
			</span>
		</p>
	</div>
	<div class="flex flex-wrap gap-2">
		<?php if ($maps_link): ?>
			<a href="<?= html_escape($maps_link) ?>" target="_blank" rel="noopener noreferrer"
				class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Buka Google Maps</a>
		<?php else: ?>
			<span class="rounded border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400" title="Belum ada tautan atau koordinat">Google Maps belum tersedia</span>
		<?php endif; ?>
		<button type="button" id="copyAddress" data-address="<?= html_escape($address_text) ?>"
			class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Salin Alamat</button>
		<a href="<?= site_url('customers/'.$customer['id'].'/edit') ?>"
			class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit</a>
		<?php if ($is_archived): ?>
			<?= form_open('customers/restore/'.$customer['id'], array('class' => 'inline', 'onsubmit' => "return confirm('Pulihkan pelanggan ini?');")) ?>
				<button type="submit" class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">Pulihkan</button>
			<?= form_close() ?>
		<?php else: ?>
			<?= form_open('customers/archive/'.$customer['id'], array('class' => 'inline', 'onsubmit' => "return confirm('Arsipkan pelanggan ini? Data arsip tidak muncul di daftar aktif.');")) ?>
				<button type="submit" class="rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Arsipkan</button>
			<?= form_close() ?>
		<?php endif; ?>
	</div>
</div>

<?php if ($is_archived): ?>
	<div class="mb-4 rounded border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-700" role="status">
		Pelanggan ini berada di arsip sejak <?= html_escape($customer['archived_at']) ?>.
	</div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
	<div class="lg:col-span-2 space-y-6">
		<div class="rounded-lg bg-white p-5 shadow-sm">
			<h2 class="mb-4 font-semibold text-gray-800">Informasi Alamat</h2>
			<?php if ($address_text === ''): ?>
				<p class="text-sm text-red-700">Alamat belum lengkap. Status lokasi: Belum Lengkap.</p>
			<?php else: ?>
				<dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
					<div class="sm:col-span-2">
						<dt class="text-xs uppercase tracking-wide text-gray-400">Alamat lengkap</dt>
						<dd class="text-gray-800"><?= nl2br(html_escape((string) $customer['full_address'])) ?: '<span class="text-gray-400">-</span>' ?></dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wide text-gray-400">Perumahan</dt>
						<dd class="text-gray-800"><?= html_escape($customer['housing_name']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wide text-gray-400">Blok / Nomor</dt>
						<dd class="text-gray-800"><?= html_escape($customer['block_number']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wide text-gray-400">RT/RW</dt>
						<dd class="text-gray-800"><?= html_escape($customer['rt_rw']) ?: '-' ?></dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wide text-gray-400">Patokan</dt>
						<dd class="text-gray-800"><?= html_escape($customer['landmark']) ?: '-' ?></dd>
					</div>
					<div class="sm:col-span-2">
						<dt class="text-xs uppercase tracking-wide text-gray-400">Catatan pengiriman</dt>
						<dd class="text-gray-800"><?= nl2br(html_escape((string) $customer['delivery_notes'])) ?: '<span class="text-gray-400">-</span>' ?></dd>
					</div>
				</dl>
			<?php endif; ?>
		</div>

		<div class="rounded-lg bg-white p-5 shadow-sm">
			<h2 class="mb-4 font-semibold text-gray-800">Lokasi</h2>
			<dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
				<div>
					<dt class="text-xs uppercase tracking-wide text-gray-400">Latitude</dt>
					<dd class="font-mono text-gray-800"><?= html_escape($customer['latitude']) ?: '-' ?></dd>
				</div>
				<div>
					<dt class="text-xs uppercase tracking-wide text-gray-400">Longitude</dt>
					<dd class="font-mono text-gray-800"><?= html_escape($customer['longitude']) ?: '-' ?></dd>
				</div>
				<div class="sm:col-span-2">
					<dt class="text-xs uppercase tracking-wide text-gray-400">Google Maps URL</dt>
					<dd>
						<?php if ( ! empty($customer['google_maps_url'])): ?>
							<a href="<?= html_escape($customer['google_maps_url']) ?>" target="_blank" rel="noopener noreferrer" class="break-all text-primary hover:underline"><?= html_escape($customer['google_maps_url']) ?></a>
						<?php else: ?>
							<span class="text-gray-400">-</span>
						<?php endif; ?>
					</dd>
				</div>
			</dl>
			<?php if ($customer['location_status'] !== 'verified'): ?>
				<p class="mt-4 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
					Lokasi ini belum diverifikasi. Pastikan titik sesuai rumah pelanggan sebelum membagikan ke driver.
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="space-y-6">
		<div class="rounded-lg bg-white p-5 shadow-sm">
			<h2 class="mb-4 font-semibold text-gray-800">Kontak</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-xs uppercase tracking-wide text-gray-400">Nomor WhatsApp</dt>
					<dd class="text-lg font-medium text-gray-800"><?= html_escape($customer['whatsapp_number']) ?></dd>
				</div>
			</dl>
		</div>

		<div class="rounded-lg bg-white p-5 shadow-sm">
			<h2 class="mb-4 font-semibold text-gray-800">Metadata</h2>
			<dl class="space-y-3 text-sm">
				<div>
					<dt class="text-xs uppercase tracking-wide text-gray-400">Dibuat</dt>
					<dd class="text-gray-800"><?= html_escape($customer['created_at']) ?></dd>
				</div>
				<div>
					<dt class="text-xs uppercase tracking-wide text-gray-400">Diperbarui</dt>
					<dd class="text-gray-800"><?= html_escape($customer['updated_at']) ?></dd>
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
