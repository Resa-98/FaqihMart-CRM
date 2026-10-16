<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$default_date = ($list !== NULL && ! empty($list['delivery_date'])) ? $list['delivery_date'] : date('Y-m-d');
$default_session = ($list !== NULL && ! empty($list['session'])) ? $list['session'] : 'pagi';
$errors = validation_errors();
?>
<div class="mb-4">
	<a href="<?= site_url('deliveries') ?>" class="text-sm text-primary hover:underline">&larr; Kembali ke daftar pengiriman</a>
</div>

<h1 class="mb-6 text-2xl font-bold text-gray-800">
	<?= $list === NULL ? 'Buat Daftar Pengiriman' : 'Edit Daftar Pengiriman' ?>
</h1>

<?php if ($errors): ?>
	<div class="mb-4 rounded border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-800" role="alert">
		<?= $errors ?>
	</div>
<?php endif; ?>

<?= form_open($form_action, array('class' => 'max-w-xl rounded-lg bg-white p-5 shadow-sm')) ?>
	<div class="space-y-4">
		<div>
			<label for="delivery_date" class="mb-1 block text-sm font-medium text-gray-700">Tanggal pengiriman <span class="text-red-600">*</span></label>
			<input type="date" name="delivery_date" id="delivery_date" required
				value="<?= html_escape(set_value('delivery_date', $default_date)) ?>"
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
		<div>
			<label for="session" class="mb-1 block text-sm font-medium text-gray-700">Sesi <span class="text-red-600">*</span></label>
			<select name="session" id="session" required
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				<option value="pagi" <?= set_value('session', $default_session) === 'pagi' ? 'selected' : '' ?>>Pagi</option>
				<option value="sore" <?= set_value('session', $default_session) === 'sore' ? 'selected' : '' ?>>Sore</option>
			</select>
		</div>
		<div>
			<label for="title" class="mb-1 block text-sm font-medium text-gray-700">Judul (opsional)</label>
			<input type="text" name="title" id="title" maxlength="150"
				value="<?= html_escape(set_value('title', ($list !== NULL ? (string) $list['title'] : ''))) ?>"
				placeholder="mis. Rute Pagi Perumahan Melati"
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
	</div>

	<div class="mt-6 flex gap-2">
		<button type="submit" class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">
			<?= $list === NULL ? 'Buat Daftar' : 'Simpan Perubahan' ?>
		</button>
		<a href="<?= site_url('deliveries') ?>" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
	</div>
<?= form_close() ?>
