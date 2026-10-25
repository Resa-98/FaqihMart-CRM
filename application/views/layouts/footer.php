<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
		</main>
	</div>
</div>
<script>
(function () {
	var btn = document.getElementById('menuToggle');
	var sidebar = document.getElementById('sidebar');
	var overlay = document.getElementById('sidebarOverlay');
	if (btn && sidebar && overlay) {
		function closeSidebar() {
			sidebar.classList.add('hidden');
			overlay.classList.add('hidden');
			btn.setAttribute('aria-expanded', 'false');
			overlay.setAttribute('aria-hidden', 'true');
		}
		function openSidebar() {
			sidebar.classList.remove('hidden');
			overlay.classList.remove('hidden');
			btn.setAttribute('aria-expanded', 'true');
			overlay.setAttribute('aria-hidden', 'false');
		}
		btn.addEventListener('click', function () {
			if (sidebar.classList.contains('hidden')) {
				openSidebar();
			} else {
				closeSidebar();
			}
		});
		overlay.addEventListener('click', closeSidebar);
		// Close on Escape key
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !sidebar.classList.contains('hidden')) {
				closeSidebar();
			}
		});
	}
})();
</script>
<?php if ( ! empty($customer_suggest)): ?>
<script>
(function () {
	'use strict';

	var MIN_CHARS = 2;
	var DEBOUNCE_MS = 250;
	var wraps = document.querySelectorAll('[data-customer-suggest]');
	if (!wraps.length) { return; }

	function normalize(value) {
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function init(wrap, index) {
		var input = wrap.querySelector('[data-suggest-input]');
		var list = wrap.querySelector('[data-suggest-list]');
		var feedback = wrap.querySelector('[data-suggest-feedback]');
		var endpoint = wrap.getAttribute('data-suggest-endpoint') || '';
		if (!input || !list || !endpoint) { return; }

		var view = wrap.getAttribute('data-suggest-view') || '';
		var status = wrap.getAttribute('data-suggest-status') || '';
		var listId = list.id || ('customer-suggest-list-' + index);
		var timer = null;
		var controller = null;
		var requestSeq = 0;
		var items = [];
		var activeIndex = -1;

		list.id = listId;
		list.setAttribute('role', 'listbox');
		list.hidden = true;
		input.setAttribute('role', 'combobox');
		input.setAttribute('aria-autocomplete', 'list');
		input.setAttribute('aria-haspopup', 'listbox');
		input.setAttribute('aria-controls', listId);
		input.setAttribute('aria-expanded', 'false');
		input.setAttribute('autocomplete', 'off');

		function setFeedback(message) {
			if (feedback) { feedback.textContent = message || ''; }
		}

		function close() {
			list.hidden = true;
			list.innerHTML = '';
			items = [];
			activeIndex = -1;
			input.setAttribute('aria-expanded', 'false');
			input.removeAttribute('aria-activedescendant');
		}

		function open() {
			if (!items.length) { return; }
			list.hidden = false;
			input.setAttribute('aria-expanded', 'true');
		}

		function updateActive(next) {
			var options = list.querySelectorAll('[data-suggest-option]');
			activeIndex = next;

			for (var i = 0; i < options.length; i++) {
				var selected = (i === activeIndex);
				options[i].setAttribute('aria-selected', selected ? 'true' : 'false');
			}

			if (activeIndex < 0 || !options[activeIndex]) {
				input.removeAttribute('aria-activedescendant');
				return;
			}

			var option = options[activeIndex];
			input.setAttribute('aria-activedescendant', option.id);

			// Gulir hanya daftar saran, bukan seluruh halaman.
			var top = option.offsetTop;
			var bottom = top + option.offsetHeight;
			if (top < list.scrollTop) {
				list.scrollTop = top;
			} else if (bottom > list.scrollTop + list.clientHeight) {
				list.scrollTop = bottom - list.clientHeight;
			}
		}

		function choose(optionIndex) {
			var item = items[optionIndex];
			if (!item) { return; }

			var keyword = String(item.keyword || '');
			input.value = keyword;
			close();
			setFeedback(keyword ? 'Mencari ' + keyword + '.' : '');

			var form = input.form;
			if (!form) { return; }
			if (typeof form.requestSubmit === 'function') {
				form.requestSubmit();
			} else {
				form.submit();
			}
		}

		function buildOption(item, optionIndex) {
			var option = document.createElement('button');
			option.type = 'button';
			option.id = listId + '-option-' + optionIndex;
			option.className = 'fm-suggest-option';
			option.setAttribute('role', 'option');
			option.setAttribute('aria-selected', 'false');
			option.setAttribute('data-suggest-option', '');

			var type = document.createElement('span');
			type.className = 'fm-suggest-type';
			type.textContent = String(item.type_label || 'Saran');

			var keyword = document.createElement('span');
			keyword.className = 'fm-suggest-keyword';
			keyword.textContent = String(item.label || item.keyword || '');

			option.appendChild(type);
			option.appendChild(keyword);
			option.addEventListener('mousedown', function (event) {
				event.preventDefault();
			});
			option.addEventListener('click', function () {
				choose(optionIndex);
			});

			return option;
		}

		function render(suggestions) {
			close();

			if (!Array.isArray(suggestions) || !suggestions.length) {
				setFeedback('Tidak ada saran kata kunci. Tekan Enter atau tombol Cari untuk mencari.');
				return;
			}

			var fragment = document.createDocumentFragment();
			for (var i = 0; i < suggestions.length; i++) {
				var item = suggestions[i];
				if (!item || typeof item.keyword !== 'string' || item.keyword === '') { continue; }
				items.push(item);
				fragment.appendChild(buildOption(item, items.length - 1));
			}

			if (!items.length) {
				setFeedback('Tidak ada saran kata kunci. Tekan Enter atau tombol Cari untuk mencari.');
				return;
			}

			list.appendChild(fragment);
			open();
			setFeedback(items.length + ' saran kata kunci tersedia.');
		}

		function fetchSuggestions(query) {
			if (controller && typeof controller.abort === 'function') {
				controller.abort();
			}

			var current = ++requestSeq;
			controller = (typeof window.AbortController === 'function') ? new AbortController() : null;

			var params = new URLSearchParams();
			params.set('q', query);
			if (view) { params.set('view', view); }
			if (status) { params.set('status', status); }

			var options = {
				credentials: 'same-origin',
				cache: 'no-store',
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			};
			if (controller) { options.signal = controller.signal; }

			fetch(endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&') + params.toString(), options)
				.then(function (response) {
					return response.json().then(function (data) {
						return { ok: response.ok, data: data };
					});
				})
				.then(function (result) {
					if (current !== requestSeq) { return; }
					if (!result.ok || !result.data || result.data.ok !== true || !Array.isArray(result.data.suggestions)) {
						throw new Error('Respons saran tidak valid.');
					}
					if (normalize(input.value).toLowerCase() !== query.toLowerCase()) { return; }

					render(result.data.suggestions);
				})
				.catch(function (error) {
					if (current !== requestSeq) { return; }
					if (error && error.name === 'AbortError') { return; }
					close();
					setFeedback('Saran tidak tersedia. Tekan Enter atau tombol Cari untuk mencari.');
				});
		}

		function load(delay) {
			var query = normalize(input.value);
			clearTimeout(timer);

			if (query.length < MIN_CHARS) {
				if (controller && typeof controller.abort === 'function') {
					controller.abort();
				}
				requestSeq++;
				close();
				setFeedback('Ketik minimal 2 karakter untuk melihat saran.');
				return;
			}

			timer = setTimeout(function () {
				fetchSuggestions(query);
			}, delay);
		}

		input.addEventListener('input', function () {
			load(DEBOUNCE_MS);
		});

		input.addEventListener('focus', function () {
			load(0);
		});

		input.addEventListener('blur', function () {
			setTimeout(function () {
				if (document.activeElement !== input && !wrap.contains(document.activeElement)) {
					close();
				}
			}, 150);
		});

		input.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				if (!items.length) { return; }
				event.preventDefault();
				if (list.hidden) { open(); }

				var delta = (event.key === 'ArrowDown') ? 1 : -1;
				if (activeIndex < 0) {
					activeIndex = (delta > 0) ? 0 : items.length - 1;
				} else {
					activeIndex = (activeIndex + delta + items.length) % items.length;
				}
				updateActive(activeIndex);
				return;
			}

			if (event.key === 'Enter') {
				if (!list.hidden && activeIndex >= 0) {
					event.preventDefault();
					choose(activeIndex);
				}
				return;
			}

			if (event.key === 'Escape') {
				if (!list.hidden) {
					event.preventDefault();
					close();
					setFeedback('');
				}
				return;
			}

			if (event.key === 'Tab') {
				close();
			}
		});

		input.form && input.form.addEventListener('submit', function () {
			close();
		});

		document.addEventListener('click', function (event) {
			if (!wrap.contains(event.target)) {
				close();
			}
		});
	}

	for (var i = 0; i < wraps.length; i++) {
		init(wraps[i], i);
	}
})();
</script>
<?php endif; ?>
</body>
</html>
