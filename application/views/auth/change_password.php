<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1 class="mb-1 text-2xl font-bold text-text">Ganti Password</h1>
<p class="mb-6 text-sm text-muted">Gunakan password yang kuat dan mudah Anda ingat.</p>

<div class="max-w-md fm-card p-6">
	<?php if ( ! empty($error)): ?>
		<div class="fm-alert fm-alert-error" role="alert">
			<?= html_escape($error) ?>
		</div>
	<?php endif; ?>

	<?php if (validation_errors()): ?>
		<div class="fm-alert fm-alert-error" role="alert">
			<?= validation_errors() ?>
		</div>
	<?php endif; ?>

	<?= form_open('change-password', array('autocomplete' => 'off')) ?>
		<div class="fm-field">
			<label for="current_password" class="fm-field-label">Password saat ini</label>
			<input type="password" name="current_password" id="current_password" required class="fm-input">
		</div>
		<div class="fm-field">
			<label for="new_password" class="fm-field-label">Password baru</label>
			<input type="password" name="new_password" id="new_password" required minlength="8" class="fm-input">
			<p class="fm-field-hint">Minimal 8 karakter.</p>
		</div>
		<div class="fm-field">
			<label for="confirm_password" class="fm-field-label">Konfirmasi password baru</label>
			<input type="password" name="confirm_password" id="confirm_password" required class="fm-input">
		</div>
		<button type="submit" class="fm-btn fm-btn-primary">Simpan Password</button>
	<?= form_close() ?>
</div>
