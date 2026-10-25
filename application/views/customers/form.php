<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ($customer !== NULL);
$field = function ($name, $default = '') use ($customer) {
	$value = ($customer && isset($customer[$name])) ? $customer[$name] : $default;
	return html_escape(set_value($name, $value));
};
$verified_checked = (bool) set_checkbox(
	'location_verified',
	'1',
	($customer && $customer['location_status'] === 'verified')
);
?>
<div class="mb-6">
	<h1 class="text-2xl font-bold text-text"><?= $is_edit ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h1>
	<?php if ($is_edit): ?>
		<p class="text-sm text-muted">Kode: <span class="font-mono"><?= html_escape($customer['customer_code']) ?></span></p>
	<?php endif; ?>
</div>

<?php if (validation_errors()): ?>
	<div class="fm-alert fm-alert-error" role="alert">
		<?= validation_errors() ?>
	</div>
<?php endif; ?>

<?php if ($duplicate): ?>
	<div class="fm-alert fm-alert-warning" role="alert">
		<strong>Peringatan:</strong> Nomor WhatsApp <span class="font-mono"><?= html_escape($duplicate['whatsapp_number']) ?></span>
		sudah dipakai oleh <strong><?= html_escape($duplicate['full_name']) ?></strong>
		(<span class="font-mono"><?= html_escape($duplicate['customer_code']) ?></span>).
		Periksa kembali; jika tetap ingin menyimpan, klik <strong>Simpan Tetap</strong>.
	</div>
<?php endif; ?>

<?= form_open($form_action) ?>
	<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
		<!-- Data pelanggan -->
		<div class="lg:col-span-2 space-y-6">
			<div class="fm-card p-5">
				<h2 class="mb-4 font-semibold text-text">Data Pelanggan</h2>
				<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
					<div>
						<label for="full_name" class="fm-field-label">Nama lengkap <span class="fm-field-required">*</span></label>
						<input type="text" name="full_name" id="full_name" required maxlength="150" value="<?= $field('full_name') ?>"
							class="fm-input">
					</div>
					<div>
						<label for="whatsapp_number" class="fm-field-label">Nomor WhatsApp <span class="fm-field-required">*</span></label>
						<input type="text" name="whatsapp_number" id="whatsapp_number" required maxlength="30" value="<?= $field('whatsapp_number') ?>"
							placeholder="cth: 08123456789"
							class="fm-input">
					</div>
				</div>
			</div>

			<div class="fm-card p-5">
				<h2 class="mb-1 font-semibold text-text">Informasi Alamat</h2>
				<p class="mb-4 fm-field-hint">Jika "Alamat Lengkap" kosong, status lokasi menjadi <strong>Belum Lengkap</strong>.</p>
				<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
					<div class="md:col-span-2">
						<label for="full_address" class="fm-field-label">Alamat lengkap</label>
						<textarea name="full_address" id="full_address" rows="3"
							placeholder="Jalan, gang, nomor, kelurahan, kecamatan, kota..."
							class="fm-input"><?= $field('full_address') ?></textarea>
					</div>
					<div>
						<label for="housing_name" class="fm-field-label">Nama perumahan</label>
						<input type="text" name="housing_name" id="housing_name" maxlength="150" value="<?= $field('housing_name') ?>"
							class="fm-input">
					</div>
					<div>
						<label for="block_number" class="fm-field-label">Blok / nomor rumah</label>
						<input type="text" name="block_number" id="block_number" maxlength="50" value="<?= $field('block_number') ?>"
							class="fm-input">
					</div>
					<div>
						<label for="rt_rw" class="fm-field-label">RT/RW</label>
						<input type="text" name="rt_rw" id="rt_rw" maxlength="20" value="<?= $field('rt_rw') ?>"
							class="fm-input">
					</div>
					<div>
						<label for="landmark" class="fm-field-label">Patokan</label>
						<input type="text" name="landmark" id="landmark" maxlength="255" value="<?= $field('landmark') ?>"
							placeholder="cth: seberang masjid"
							class="fm-input">
					</div>
					<div class="md:col-span-2">
						<label for="delivery_notes" class="fm-field-label">Catatan pengiriman</label>
						<textarea name="delivery_notes" id="delivery_notes" rows="2"
							class="fm-input"><?= $field('delivery_notes') ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Lokasi -->
		<div class="space-y-6">
			<div class="fm-card p-5">
				<h2 class="mb-1 font-semibold text-text">Lokasi Google Maps</h2>
				<p class="mb-4 fm-field-hint">Tempel tautan Google Maps biasa (bukan API berbayar) dan/atau koordinat.</p>
				<div class="space-y-4">
					<div>
						<label for="google_maps_url" class="fm-field-label">Google Maps URL</label>
						<input type="url" name="google_maps_url" id="google_maps_url" maxlength="500" value="<?= $field('google_maps_url') ?>"
							placeholder="https://maps.app.goo.gl/..."
							class="fm-input">
					</div>
					<div class="grid grid-cols-2 gap-3">
						<div>
							<label for="latitude" class="fm-field-label">Latitude</label>
							<input type="text" name="latitude" id="latitude" inputmode="decimal" value="<?= $field('latitude') ?>"
								placeholder="-6.2000000"
								class="fm-input">
						</div>
						<div>
							<label for="longitude" class="fm-field-label">Longitude</label>
							<input type="text" name="longitude" id="longitude" inputmode="decimal" value="<?= $field('longitude') ?>"
								placeholder="106.8000000"
								class="fm-input">
						</div>
					</div>
					<p class="fm-field-hint">Latitude dan Longitude harus diisi bersamaan.</p>
				</div>
			</div>

			<div class="fm-card p-5">
				<h2 class="mb-3 font-semibold text-text">Verifikasi Lokasi</h2>
				<input type="hidden" name="location_verified" value="0">
				<label class="flex items-start gap-3 text-sm">
					<input type="checkbox" name="location_verified" value="1" <?= $verified_checked ? 'checked' : '' ?>
						class="mt-1 h-4 w-4 rounded border-border text-primary focus:ring-primary">
					<span>Saya sudah memeriksa dan memastikan titik lokasi sesuai dengan rumah pelanggan.</span>
				</label>
				<p class="mt-3 fm-field-hint">Menandai terverifikasi memerlukan tautan Maps atau koordinat yang valid.</p>
				<?php if ($is_edit): ?>
					<p class="mt-3 fm-field-hint">Status saat ini:
						<span class="rounded-full px-2 py-1 text-xs font-medium <?= location_status_badge_classes($customer['location_status']) ?>">
							<?= html_escape(location_status_label($customer['location_status'])) ?>
						</span>
					</p>
				<?php endif; ?>
			</div>

			<div class="flex flex-col gap-2">
				<button type="submit" class="fm-btn fm-btn-primary">
					<?= $duplicate ? 'Simpan Tetap' : 'Simpan' ?>
				</button>
				<a href="<?= $is_edit ? site_url('customers/'.$customer['id']) : site_url('customers') ?>"
					class="fm-btn fm-btn-secondary">Batal</a>
			</div>
		</div>
	</div>
<?= form_close() ?>
