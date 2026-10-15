<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$query_params = array(
	'q'      => $filters['q'],
	'status' => $filters['status'],
	'view'   => $filters['view'],
);
$export_url = site_url('customers/export').'?'.http_build_query($query_params);
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
	<div>
		<h1 class="text-2xl font-bold text-gray-800">Pelanggan</h1>
		<p class="text-sm text-gray-500"><?= (int) $total ?> data <?= $filters['view'] === 'archived' ? '(arsip)' : 'aktif' ?></p>
	</div>
	<div class="flex gap-2">
		<a href="<?= html_escape($export_url) ?>" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
			Ekspor CSV
		</a>
		<a href="<?= site_url('customers/create') ?>" class="rounded bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-dark">
			+ Tambah Pelanggan
		</a>
	</div>
</div>

<form method="get" action="<?= site_url('customers') ?>" class="mb-4 rounded-lg bg-white p-4 shadow-sm">
	<div class="flex flex-col gap-3 md:flex-row md:items-end">
		<div class="flex-1">
			<label for="q" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
			<input type="text" name="q" id="q" value="<?= html_escape($filters['q']) ?>" placeholder="Nama, WhatsApp, atau alamat..."
				class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
		</div>
		<div class="md:w-48">
			<label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status Lokasi</label>
			<select name="status" id="status" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				<option value="">Semua status</option>
				<option value="verified" <?= $filters['status'] === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
				<option value="unverified" <?= $filters['status'] === 'unverified' ? 'selected' : '' ?>>Belum Diverifikasi</option>
				<option value="incomplete" <?= $filters['status'] === 'incomplete' ? 'selected' : '' ?>>Belum Lengkap</option>
			</select>
		</div>
		<div class="md:w-40">
			<label for="view" class="mb-1 block text-sm font-medium text-gray-700">Tampilkan</label>
			<select name="view" id="view" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
				<option value="active" <?= $filters['view'] === 'active' ? 'selected' : '' ?>>Aktif</option>
				<option value="archived" <?= $filters['view'] === 'archived' ? 'selected' : '' ?>>Arsip</option>
			</select>
		</div>
		<div class="flex gap-2">
			<button type="submit" class="rounded bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Terapkan</button>
			<a href="<?= site_url('customers') ?>" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
		</div>
	</div>
</form>

<div class="rounded-lg bg-white shadow-sm">
	<?php if (empty($customers)): ?>
		<div class="px-5 py-12 text-center text-gray-500">
			<p class="text-lg">Tidak ada data pelanggan.</p>
			<p class="mt-1 text-sm">Coba ubah kata kunci atau filter, atau tambah pelanggan baru.</p>
		</div>
	<?php else: ?>
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200 text-sm">
				<thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
					<tr>
						<th class="px-4 py-3">Kode</th>
						<th class="px-4 py-3">Nama</th>
						<th class="px-4 py-3">WhatsApp</th>
						<th class="px-4 py-3">Alamat</th>
						<th class="px-4 py-3">Peta</th>
						<th class="px-4 py-3">Status Lokasi</th>
						<th class="px-4 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100">
					<?php foreach ($customers as $row): ?>
						<tr class="hover:bg-gray-50">
							<td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-500"><?= html_escape($row['customer_code']) ?></td>
							<td class="px-4 py-3">
								<a href="<?= site_url('customers/'.$row['id']) ?>" class="font-medium text-gray-800 hover:text-primary"><?= html_escape($row['full_name']) ?></a>
							</td>
							<td class="whitespace-nowrap px-4 py-3"><?= html_escape($row['whatsapp_number']) ?></td>
							<td class="max-w-xs px-4 py-3 text-gray-600"><?= html_escape(customer_address_text($row)) ?></td>
							<?php $maps_link = customer_maps_link($row); ?>
							<td class="px-4 py-3">
								<div class="maps-cell flex items-center gap-1"
									data-maps-url="<?= html_escape((string) $row['google_maps_url']) ?>"
									data-maps-endpoint="<?= site_url('customers/'.$row['id'].'/maps') ?>">
									<a href="<?= $maps_link ? html_escape($maps_link) : '#' ?>"
										<?= $maps_link ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
										data-has-link="<?= $maps_link ? '1' : '0' ?>"
										class="maps-open rounded p-1 <?= $maps_link ? 'text-primary hover:bg-green-50' : 'text-gray-300' ?>"
										title="<?= $maps_link ? 'Buka di Google Maps' : 'Belum ada tautan/koordinat Maps' ?>">
										<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
									</a>
									<button type="button" class="maps-edit rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Tempel / ubah tautan Maps">
										<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
									</button>
									<span class="maps-status text-xs"></span>
									<span class="maps-editor hidden items-center gap-1">
										<input type="url" class="maps-input w-52 rounded border border-gray-300 px-2 py-1 text-xs focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
											placeholder="https://maps.app.goo.gl/..." value="<?= html_escape((string) $row['google_maps_url']) ?>">
										<button type="button" class="maps-save rounded bg-primary px-2 py-1 text-xs font-medium text-white hover:bg-primary-dark">Simpan</button>
										<button type="button" class="maps-cancel rounded border border-gray-300 px-2 py-1 text-xs text-gray-600">Batal</button>
									</span>
								</div>
							</td>
							<td class="whitespace-nowrap px-4 py-3">
								<span class="rounded-full px-2 py-1 text-xs font-medium <?= location_status_badge_classes($row['location_status']) ?>">
									<?= html_escape(location_status_label($row['location_status'])) ?>
								</span>
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-right">
								<a href="<?= site_url('customers/'.$row['id']) ?>" class="text-primary hover:underline">Detail</a>
								<?php if ($filters['view'] === 'archived'): ?>
									<?= form_open('customers/restore/'.$row['id'], array('class' => 'inline', 'onsubmit' => "return confirm('Pulihkan pelanggan ini?');")) ?>
										<button type="submit" class="ml-2 text-primary hover:underline">Pulihkan</button>
									<?= form_close() ?>
								<?php else: ?>
									<?= form_open('customers/archive/'.$row['id'], array('class' => 'inline', 'onsubmit' => "return confirm('Arsipkan pelanggan ini? Data arsip tidak muncul di daftar aktif.');")) ?>
										<button type="submit" class="ml-2 text-red-600 hover:underline">Arsipkan</button>
									<?= form_close() ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php if ($total_page > 1): ?>
	<?php
	$link_params = function ($page) use ($query_params) {
		return site_url('customers').'?'.http_build_query(array_merge($query_params, array('page' => $page)));
	};
	?>
	<div class="mt-4 flex items-center justify-between text-sm">
		<span class="text-gray-500">Halaman <?= (int) $page ?> dari <?= (int) $total_page ?></span>
		<div class="flex gap-2">
			<?php if ($page > 1): ?>
				<a href="<?= html_escape($link_params($page - 1)) ?>" class="rounded border border-gray-300 bg-white px-3 py-1 text-gray-700 hover:bg-gray-50">Sebelumnya</a>
			<?php endif; ?>
			<?php if ($page < $total_page): ?>
				<a href="<?= html_escape($link_params($page + 1)) ?>" class="rounded border border-gray-300 bg-white px-3 py-1 text-gray-700 hover:bg-gray-50">Berikutnya</a>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<script>
(function () {
	var metaToken = document.querySelector('meta[name="csrf-token"]');
	var metaName = document.querySelector('meta[name="csrf-token-name"]');
	if (!metaToken || !metaName) { return; }
	var tokenName = metaName.getAttribute('content');

	function applyCsrf(token) {
		if (!token) { return; }
		metaToken.setAttribute('content', token);
		var inputs = document.querySelectorAll('input[name="' + tokenName + '"]');
		for (var i = 0; i < inputs.length; i++) { inputs[i].value = token; }
	}

	function applyLink(openEl, link) {
		if (link) {
			openEl.setAttribute('href', link);
			openEl.setAttribute('target', '_blank');
			openEl.setAttribute('rel', 'noopener noreferrer');
			openEl.setAttribute('data-has-link', '1');
			openEl.setAttribute('title', 'Buka di Google Maps');
			openEl.classList.remove('text-gray-300');
			openEl.classList.add('text-primary', 'hover:bg-green-50');
		} else {
			openEl.setAttribute('href', '#');
			openEl.removeAttribute('target');
			openEl.removeAttribute('rel');
			openEl.setAttribute('data-has-link', '0');
			openEl.setAttribute('title', 'Belum ada tautan/koordinat Maps');
			openEl.classList.add('text-gray-300');
			openEl.classList.remove('text-primary', 'hover:bg-green-50');
		}
	}

	var cells = document.querySelectorAll('.maps-cell');
	for (var c = 0; c < cells.length; c++) {
		(function (cell) {
			var openEl = cell.querySelector('.maps-open');
			var editBtn = cell.querySelector('.maps-edit');
			var editor = cell.querySelector('.maps-editor');
			var input = cell.querySelector('.maps-input');
			var saveBtn = cell.querySelector('.maps-save');
			var cancelBtn = cell.querySelector('.maps-cancel');
			var status = cell.querySelector('.maps-status');

			openEl.addEventListener('click', function (e) {
				if (openEl.getAttribute('data-has-link') !== '1') { e.preventDefault(); }
			});

			function showEditor(show) {
				editor.classList.toggle('hidden', !show);
				editor.classList.toggle('flex', show);
				if (show) { input.focus(); input.select(); }
			}

			function setStatus(msg, cls) {
				status.textContent = msg || '';
				status.className = 'maps-status text-xs ' + (cls || '');
			}

			editBtn.addEventListener('click', function () { showEditor(true); });
			cancelBtn.addEventListener('click', function () {
				input.value = cell.getAttribute('data-maps-url') || '';
				setStatus('');
				showEditor(false);
			});

			function save() {
				var body = new URLSearchParams();
				body.append(tokenName, metaToken.getAttribute('content'));
				body.append('google_maps_url', input.value.trim());

				saveBtn.disabled = true;
				setStatus('Menyimpan...', 'text-gray-500');

				fetch(cell.getAttribute('data-maps-endpoint'), {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'X-Requested-With': 'XMLHttpRequest' },
					body: body
				}).then(function (res) {
					return res.json().then(function (data) { return { ok: res.ok, data: data }; });
				}).then(function (result) {
					var data = result.data || {};
					applyCsrf(data.csrf_token);
					if (result.ok && data.ok) {
						cell.setAttribute('data-maps-url', data.google_maps_url || '');
						applyLink(openEl, data.maps_link);
						setStatus('Tersimpan', 'text-green-600');
						showEditor(false);
						setTimeout(function () { status.textContent = ''; }, 2000);
					} else {
						setStatus(data.message || 'Gagal menyimpan.', 'text-red-600');
					}
				}).catch(function () {
					setStatus('Gagal menyimpan.', 'text-red-600');
				}).then(function () {
					saveBtn.disabled = false;
				});
			}

			saveBtn.addEventListener('click', save);
			input.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') { e.preventDefault(); save(); }
				if (e.key === 'Escape') { cancelBtn.click(); }
			});
		})(cells[c]);
	}
})();
</script>
