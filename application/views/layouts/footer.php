<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
		</main>
	</div>
</div>
<script>
(function () {
	var btn = document.getElementById('menuToggle');
	var sidebar = document.getElementById('sidebar');
	if (btn && sidebar) {
		btn.addEventListener('click', function () {
			var hidden = sidebar.classList.toggle('hidden');
			btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
		});
	}
})();
</script>
</body>
</html>
