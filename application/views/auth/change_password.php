<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1 class="mb-1 text-2xl font-bold text-gray-800">Ganti Password</h1>
<p class="mb-6 text-sm text-gray-500">Gunakan password yang kuat dan mudah Anda ingat.</p>

<div class="max-w-md rounded-lg bg-white p-6 shadow-sm">
	<?php if ( ! empty($error)): ?>
		<div class="mb-4 rounded border border-red-200 bg-red-100 px-3 py-2 text-sm text-red-800" role="alert">
			<?= html_escape($error) ?>
		</div>
	<?php endif; ?>

	<?php if (validation_errors()): ?>
		<div class="mb-4 rounded border border-red-200 bg-red-100 px-3 py-2 text-sm text-red-800" role="alert">
			<?= validation_errors() ?>
		</div>
	<?php endif; ?>

	<?= form_open('change-password', array('autocomplete' => 'off')) ?>
		<div class="mb-4">
			<label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">Password saat ini</label>
			<input type="password" name="current_password" id="current_password" required
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
		<div class="mb-4">
			<label for="new_password" class="mb-1 block text-sm font-medium text-gray-700">Password baru</label>
			<input type="password" name="new_password" id="new_password" required minlength="8"
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
			<p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
		</div>
		<div class="mb-6">
			<label for="confirm_password" class="mb-1 block text-sm font-medium text-gray-700">Konfirmasi password baru</label>
			<input type="password" name="confirm_password" id="confirm_password" required
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
		<button type="submit" class="rounded bg-primary px-4 py-2 font-medium text-white hover:bg-primary-dark">
			Simpan Password
		</button>
	<?= form_close() ?>
</div>
