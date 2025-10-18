@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')

@section('content')
<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-16">
	<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
		<a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 mr-2">
				<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
			</svg>
			Kembali ke Dashboard
		</a>

		<div class="space-y-3">
			<h1 class="text-3xl font-semibold text-slate-900 dark:text-white">Ajukan Peminjaman Ruangan</h1>
			<p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Lengkapi formulir berikut untuk mengajukan peminjaman ruangan. Pastikan informasi yang kamu isi sesuai dengan kebutuhan kegiatan.</p>
		</div>

		@if($errors->has('booking'))
			<div class="rounded-xl border border-red-300 bg-red-50 text-red-700 dark:border-red-500 dark:bg-red-500/10 dark:text-red-200 px-4 py-3 text-sm">
				{{ $errors->first('booking') }}
			</div>
		@endif

		@if($errors->any() && !$errors->has('booking'))
			<div class="rounded-xl border border-red-300 bg-red-50 text-red-700 dark:border-red-500 dark:bg-red-500/10 dark:text-red-200 px-4 py-3 text-sm">
				Terdapat beberapa kesalahan pada formulir. Silakan periksa kembali isianmu.
			</div>
		@endif

		<div class="grid gap-6 md:grid-cols-5">
			<div class="md:col-span-2 space-y-4">
				<div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
					<h2 class="text-lg font-semibold text-slate-900 dark:text-white">Info Ruangan</h2>
					<div class="mt-4 space-y-4 text-sm text-slate-600 dark:text-slate-300">
						<div>
							<p class="text-xs uppercase tracking-wide text-slate-400">Nama</p>
							<p class="font-medium text-slate-900 dark:text-white">{{ $room->name }}</p>
						</div>
						<div>
							<p class="text-xs uppercase tracking-wide text-slate-400">Lokasi</p>
							<p>{{ $room->location ?? 'Lokasi belum diatur' }}</p>
						</div>
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs uppercase tracking-wide text-slate-400">Tipe</p>
								<p>{{ ucfirst(str_replace('_', ' ', $room->type ?? 'umum')) }}</p>
							</div>
							<div class="text-right">
								<p class="text-xs uppercase tracking-wide text-slate-400">Kapasitas</p>
								<p>{{ $room->capacity }} orang</p>
							</div>
						</div>
						<div>
							<p class="text-xs uppercase tracking-wide text-slate-400">Fasilitas</p>
							@if($room->facilities)
								<ul class="mt-2 space-y-1">
									@foreach(preg_split('/[,;]+/', $room->facilities) as $facility)
										@if(trim($facility) !== '')
											<li class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs mr-2 mb-2">{{ trim($facility) }}</li>
										@endif
									@endforeach
								</ul>
							@else
								<p>Fasilitas belum diatur.</p>
							@endif
						</div>
					</div>
				</div>
			</div>

			<div class="md:col-span-3">
				<form action="{{ route('bookings.store') }}" method="POST" class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-5">
					@csrf
					<input type="hidden" name="room_id" value="{{ $room->id }}">

					<div class="grid sm:grid-cols-2 gap-4">
						<div>
							<label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tanggal</label>
							<input type="date" name="booking_date" value="{{ old('booking_date', $prefill['date']) }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
							@error('booking_date')
								<p class="mt-2 text-xs text-red-500">{{ $message }}</p>
							@enderror
						</div>
						<div class="grid grid-cols-2 gap-4">
							<div>
								<label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Mulai</label>
								<input type="time" name="start_time" value="{{ old('start_time', $prefill['start_time']) }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
								@error('start_time')
									<p class="mt-2 text-xs text-red-500">{{ $message }}</p>
								@enderror
							</div>
							<div>
								<label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Selesai</label>
								<input type="time" name="end_time" value="{{ old('end_time', $prefill['end_time']) }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
								@error('end_time')
									<p class="mt-2 text-xs text-red-500">{{ $message }}</p>
								@enderror
							</div>
						</div>
					</div>

					<div>
						<label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jumlah Peserta</label>
						<input type="number" min="1" name="participants" value="{{ old('participants') }}" placeholder="Contoh: 25" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
						@error('participants')
							<p class="mt-2 text-xs text-red-500">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tujuan Peminjaman</label>
						<textarea name="purpose" rows="4" placeholder="Jelaskan kegiatan yang akan dilaksanakan" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>{{ old('purpose') }}</textarea>
						@error('purpose')
							<p class="mt-2 text-xs text-red-500">{{ $message }}</p>
						@enderror
					</div>

					<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
						<p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan akan ditinjau oleh admin. Kamu akan menerima notifikasi setelah disetujui atau ditolak.</p>
						<button type="submit" class="inline-flex items-center justify-center rounded-xl bg-yellow-400 px-5 py-3 text-sm font-semibold text-slate-900 shadow hover:bg-yellow-300 transition">
							Ajukan Sekarang
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
