<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="mb-6">
	<h1 class="text-2xl font-bold text-text">Impor Pelanggan</h1>
	<p class="text-sm text-muted">Unggah file CSV hasil ekspor atau sesuai format aplikasi.</p>
</div>

<?php if ($this->session->flashdata('error')): ?>
	<div class="fm-alert fm-alert-error">
		<?= html_escape($this->session->flashdata('error')) ?>
	</div>
<?php endif; ?>

<div class="grid gap-6 lg:grid-cols-2">
	<div class="fm-card p-6">
		<h2 class="mb-4 text-lg font-semibold text-text">Unggah File CSV</h2>
		<?= form_open_multipart('customers/import/process') ?>
			<div class="mb-4">
				<label class="mb-1 block text-sm font-medium text-text">File CSV</label>
				<input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-text file:mr-4 file:rounded file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-primary-dark">
				<p class="mt-1 text-xs text-muted">Maksimal 2MB (.CSV / .TXT)</p>
			</div>
			<div class="flex gap-2">
				<button type="submit" class="fm-btn fm-btn-primary">Impor Sekarang</button>
				<a href="<?= site_url('customers') ?>" class="fm-btn fm-btn-secondary">Kembali</a>
			</div>
		<?= form_close() ?>
	</div>

	<div class="fm-card p-6">
		<h2 class="mb-4 text-lg font-semibold text-text">Keterangan</h2>
		<ul class="space-y-2 text-sm text-text">
			<li class="fm-field-hint">• Gunakan file hasil <strong>Ekspor CSV</strong> biar header-nya cocok.</li>
			<li class="fm-field-hint">• Jika <strong>Kode</strong> sudah ada, datanya akan diperbarui. Jika belum, akan ditambahkan.</li>
			<li class="fm-field-hint">• Nomor WhatsApp otomatis dibersihkan (tanda minus/spasi/+ dihapus).</li>
			<li class="fm-field-hint">• Status lokasi dikonversi ke <code class="fm-field-hint">verified/incomplete/unverified</code>.</li>
			<li class="fm-field-hint">• Data impor otomatis masuk ke pelanggan <strong>aktif</strong>.</li>
		</ul>
	</div>
</div>
