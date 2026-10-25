<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$default_date = ($list !== NULL && ! empty($list['delivery_date'])) ? $list['delivery_date'] : date('Y-m-d');
$default_session = ($list !== NULL && ! empty($list['session'])) ? $list['session'] : 'pagi';
$errors = validation_errors();
?>
<div class="mb-4">
	<a href="<?= site_url('deliveries') ?>" class="text-sm text-primary hover:text-primary-dark">&larr; Kembali ke daftar pengiriman</a>
</div>

<h1 class="mb-6 text-2xl font-bold text-text">
	<?= $list === NULL ? 'Buat Daftar Pengiriman' : 'Edit Daftar Pengiriman' ?>
</h1>

<?php if ($errors): ?>
	<div class="fm-alert fm-alert-error" role="alert">
		<?= $errors ?>
	</div>
<?php endif; ?>

<?= form_open($form_action, array('class' => 'max-w-xl fm-card p-5')) ?>
	<div class="space-y-4">
		<div>
			<label for="delivery_date" class="fm-field-label">Tanggal pengiriman <span class="fm-field-required">*</span></label>
			<input type="date" name="delivery_date" id="delivery_date" required
				value="<?= html_escape(set_value('delivery_date', $default_date)) ?>"
				class="fm-input">
		</div>
		<div>
			<label for="session" class="fm-field-label">Sesi <span class="fm-field-required">*</span></label>
			<select name="session" id="session" required
				class="fm-input">
				<option value="pagi" <?= set_value('session', $default_session) === 'pagi' ? 'selected' : '' ?>>Pagi</option>
				<option value="sore" <?= set_value('session', $default_session) === 'sore' ? 'selected' : '' ?>>Sore</option>
			</select>
		</div>
		<div>
			<label for="title" class="fm-field-label">Judul (opsional)</label>
			<input type="text" name="title" id="title" maxlength="150"
				value="<?= html_escape(set_value('title', ($list !== NULL ? (string) $list['title'] : ''))) ?>"
				placeholder="mis. Rute Pagi Perumahan Melati"
				class="fm-input">
		</div>
	</div>

	<div class="mt-6 flex gap-2">
		<button type="submit" class="fm-btn fm-btn-primary">
			<?= $list === NULL ? 'Buat Daftar' : 'Simpan Perubahan' ?>
		</button>
		<a href="<?= site_url('deliveries') ?>" class="fm-btn fm-btn-secondary">Batal</a>
	</div>
<?= form_close() ?>
