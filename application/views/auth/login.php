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
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
	<div class="w-full max-w-sm">
		<div class="mb-6 text-center">
			<h1 class="sr-only">CRM Faqih Mart</h1>
			<img src="<?= base_url('assets/primary-logo.webp') ?>" alt="CRM Faqih Mart" class="mx-auto h-16 w-auto">
			<p class="mt-3 text-sm text-gray-500">Masuk untuk mengelola data pelanggan</p>
		</div>

		<div class="rounded-lg bg-white p-6 shadow-sm">
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

			<?= form_open('login', array('autocomplete' => 'off')) ?>
				<div class="mb-4">
					<label for="username" class="mb-1 block text-sm font-medium text-gray-700">Username</label>
					<input type="text" name="username" id="username" value="<?= html_escape(set_value('username')) ?>" required
						class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				</div>
				<div class="mb-6">
					<label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
					<input type="password" name="password" id="password" required
						class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				</div>
				<button type="submit" class="w-full rounded bg-primary px-4 py-2 font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
					Masuk
				</button>
			<?= form_close() ?>
		</div>
	</div>
</body>
</html>
