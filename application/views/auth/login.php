<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/webp" href="<?= base_url('assets/icon-logo.webp') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icon-logo.webp') ?>">
<title>Login &middot; CRM Faqih Mart</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#16A34A', dark: '#15803D' } } } } };
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/ui.css') ?>">
</head>
<body class="min-h-screen bg-bg flex items-center justify-center p-4">
	<div class="w-full max-w-sm">
		<div class="mb-6 text-center">
			<h1 class="sr-only">CRM Faqih Mart</h1>
			<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="mx-auto h-16 w-auto">
			<p class="mt-3 text-sm text-muted">Masuk untuk mengelola data pelanggan</p>
		</div>

		<div class="fm-card p-6">
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

			<?= form_open('login', array('autocomplete' => 'off')) ?>
				<div class="fm-field">
					<label for="username" class="fm-field-label">Username</label>
					<input type="text" name="username" id="username" value="<?= html_escape(set_value('username')) ?>" required
						class="fm-input">
				</div>
				<div class="fm-field">
					<label for="password" class="fm-field-label">Password</label>
					<input type="password" name="password" id="password" required
						class="fm-input">
				</div>
				<button type="submit" class="fm-btn fm-btn-primary w-full">
					Masuk
				</button>
			<?= form_close() ?>
		</div>
	</div>
</body>
</html>
