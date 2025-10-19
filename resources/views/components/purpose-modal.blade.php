@once
<div id="purpose-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
	<div data-purpose-modal-overlay class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
	<div class="relative z-10 flex min-h-full items-center justify-center p-4">
		<div class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
			<div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4 dark:border-slate-800">
				<div>
					<p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-400 dark:text-slate-500">Tujuan</p>
					<h3 data-purpose-modal-title class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">Detail Tujuan</h3>
				</div>
				<button type="button" data-purpose-modal-close class="rounded-full bg-slate-100 p-2 text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Tutup">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
			<div class="px-6 py-5">
				<p data-purpose-modal-text class="max-h-60 overflow-y-auto whitespace-pre-wrap break-words text-sm leading-6 text-slate-600 dark:text-slate-300">Tujuan peminjaman belum diisi.</p>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script>
(() => {
	if (window.__purposeModalInitialized) {
		return;
	}
	window.__purposeModalInitialized = true;

	document.addEventListener('DOMContentLoaded', () => {
		const modal = document.getElementById('purpose-modal');
		if (!modal) {
			return;
		}

		const overlay = modal.querySelector('[data-purpose-modal-overlay]');
		const titleEl = modal.querySelector('[data-purpose-modal-title]');
		const textEl = modal.querySelector('[data-purpose-modal-text]');
		const closeButtons = modal.querySelectorAll('[data-purpose-modal-close]');

		const openModal = ({ title, text }) => {
			if (titleEl) {
				titleEl.textContent = title || 'Detail Tujuan Peminjaman';
			}
			if (textEl) {
				textEl.textContent = text || 'Tujuan peminjaman belum diisi.';
			}
			modal.classList.remove('hidden');
			document.body.classList.add('overflow-hidden');
		};

		const closeModal = () => {
			modal.classList.add('hidden');
			document.body.classList.remove('overflow-hidden');
		};

		document.addEventListener('click', (event) => {
			const trigger = event.target.closest('[data-purpose-modal-trigger]');
			if (trigger) {
				event.preventDefault();
				const title = trigger.getAttribute('data-purpose-title') || 'Detail Tujuan Peminjaman';
				const text = trigger.getAttribute('data-purpose') || '';
				openModal({ title, text });
				return;
			}

			if (event.target.closest('[data-purpose-modal-close]')) {
				event.preventDefault();
				closeModal();
			}
		});

		overlay?.addEventListener('click', closeModal);
		closeButtons.forEach((button) => button.addEventListener('click', closeModal));
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
				closeModal();
			}
		});
	});
})();
</script>
@endpush
@endonce
