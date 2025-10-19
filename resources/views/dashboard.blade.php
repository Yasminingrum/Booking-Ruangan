@extends('layouts.app')

@section('title', 'Dashboard Peminjaman')

@section('content')
<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-16">
	<div class="relative bg-slate-900 text-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
			<div class="grid lg:grid-cols-12 gap-8 items-start">
				<div class="lg:col-span-8 space-y-6">
					<div>
						<p class="text-sm uppercase tracking-wider text-slate-300">Cari Ketersediaan Ruangan</p>
						<h1 class="mt-2 text-3xl sm:text-4xl font-semibold">Halo, {{ auth()->user()->name }} 👋</h1>
						<p class="text-slate-200 mt-2">Pilih tanggal dan jam, lalu filter jenis ruangan sesuai kebutuhanmu.</p>
					</div>

					@if(session('success'))
						<div class="rounded-2xl border border-emerald-300/60 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-100 shadow">
							{{ session('success') }}
						</div>
					@endif

					@if($errors->has('booking'))
						<div class="rounded-2xl border border-red-300/60 bg-red-500/20 px-4 py-3 text-sm text-red-100 shadow">
							{{ $errors->first('booking') }}
						</div>
					@endif

					<form action="{{ route('dashboard') }}" method="GET" class="bg-white/10 backdrop-blur rounded-2xl border border-white/10 p-6 shadow-xl space-y-5">
						<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Tanggal</span>
								<input type="date" name="date" value="{{ $filters['date'] }}" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white placeholder-slate-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60" />
							</label>
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Mulai</span>
								<input type="time" name="start_time" value="{{ $filters['start_time'] }}" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white placeholder-slate-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60" />
							</label>
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Selesai</span>
								<input type="time" name="end_time" value="{{ $filters['end_time'] }}" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white placeholder-slate-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60" />
							</label>
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Jenis Ruangan</span>
								<select name="type" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60">
									@foreach($roomTypes as $value => $label)
										<option value="{{ $value }}" {{ $filters['type'] === $value ? 'selected' : '' }} class="text-slate-900">{{ $label }}</option>
									@endforeach
								</select>
							</label>
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Kapasitas Minimum</span>
								<input type="number" name="min_capacity" min="0" value="{{ $filters['min_capacity'] }}" placeholder="0" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white placeholder-slate-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60" />
							</label>
							<label class="block">
								<span class="block text-xs font-semibold uppercase tracking-wide text-slate-300">Kata Kunci</span>
								<input type="text" name="keyword" value="{{ $filters['keyword'] }}" placeholder="Nama / Gedung / Tipe" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-white placeholder-slate-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/60" />
							</label>
						</div>

						<div class="flex flex-wrap items-center justify-between gap-3">
							<label class="flex items-center gap-2 text-sm text-slate-200">
								<input type="checkbox" name="available_only" value="1" {{ $filters['available_only'] ? 'checked' : '' }} class="h-4 w-4 rounded border-white/30 bg-white/10 text-yellow-400 focus:ring-yellow-300" />
								Tampilkan yang tersedia saja
							</label>

							<button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-yellow-400 px-5 py-2 text-slate-900 font-semibold shadow hover:bg-yellow-300 transition">
								<span>Cek Ketersediaan</span>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
								</svg>
							</button>
						</div>
					</form>
				</div>

				<div class="lg:col-span-4">
					<div class="rounded-3xl bg-slate-800/80 border border-white/10 backdrop-blur p-6 space-y-5 shadow-xl">
						<h2 class="text-lg font-semibold text-white">Ringkasan Hari Ini</h2>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div class="rounded-2xl bg-yellow-400/90 px-5 py-6 text-slate-900 shadow flex flex-col items-center text-center">
								<p class="text-xs font-semibold uppercase tracking-wide">Tersedia</p>
								<p class="mt-3 text-3xl font-bold">{{ $summary['available'] }}</p>
							</div>
							<div class="rounded-2xl bg-yellow-400/90 px-5 py-6 text-slate-900 shadow flex flex-col items-center text-center">
								<p class="text-xs font-semibold uppercase tracking-wide">Terpesan</p>
								<p class="mt-3 text-3xl font-bold">{{ $summary['booked'] }}</p>
							</div>
							<div class="rounded-2xl bg-yellow-400/90 px-5 py-6 text-slate-900 shadow flex flex-col items-center text-center">
								<p class="text-xs font-semibold uppercase tracking-wide">Jumlah Ruangan</p>
								<p class="mt-3 text-3xl font-bold">{{ $summary['total_rooms'] }}</p>
							</div>
							<div class="rounded-2xl bg-yellow-400/90 px-5 py-6 text-slate-900 shadow flex flex-col items-center text-center">
								<p class="text-xs font-semibold uppercase tracking-wide">Pengajuan Saya</p>
								<p class="mt-3 text-3xl font-bold">{{ $summary['my_requests'] }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 space-y-12">
		<section class="space-y-4">
			<div class="flex flex-wrap items-end justify-between gap-4">
				<div>
					<h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Hasil Pencarian</h2>
					<p class="text-slate-500 dark:text-slate-400">{{ $roomsFound }} ruangan ditemukan</p>
				</div>
			</div>

			@if($roomsFound === 0)
				<div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-8 text-center text-slate-500 dark:text-slate-400">
					Belum ada ruangan yang cocok dengan filter yang kamu pilih.
				</div>
			@else
				<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
					@foreach($rooms as $room)
						<div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm hover:shadow-lg transition">
							<div class="flex items-start justify-between gap-4">
								<div>
									<h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $room->name }}</h3>
									<p class="text-sm text-slate-500 dark:text-slate-400">{{ $room->location ?? 'Lokasi tidak tersedia' }}</p>
									<p class="text-xs uppercase tracking-wide text-slate-400">{{ ucfirst(str_replace('_', ' ', $room->type ?? 'Umum')) }}</p>
								</div>
								<span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $room->availability_badge }}">
									{{ $room->availability_label }}
								</span>
							</div>

							<div class="flex flex-wrap gap-2 mt-6 text-xs text-slate-600 dark:text-slate-300">
								<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1">Kapasitas {{ $room->capacity }}</span>
								@foreach($room->facility_list->take(5) as $facility)
									<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1">{{ $facility }}</span>
								@endforeach
								@if($room->facility_list->count() === 0)
									<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1">Fasilitas belum diatur</span>
								@endif
							</div>

							@php
								$bookingQuery = array_filter([
									'date' => $filters['date'] ?? null,
									'start_time' => $filters['start_time'] ?? null,
									'end_time' => $filters['end_time'] ?? null,
								], fn ($value) => filled($value));
								$bookingUrl = route('bookings.create', array_merge(['room' => $room->id], $bookingQuery));
							@endphp

							<div class="mt-6 flex items-center gap-3">
								<a href="{{ $bookingUrl }}" class="flex-1 inline-flex justify-center rounded-xl bg-yellow-400 px-4 py-2 font-semibold text-slate-900 shadow hover:bg-yellow-300 transition" title="Ajukan peminjaman untuk ruangan ini">
									Ajukan Peminjaman
								</a>
								<button type="button" class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800" title="Detail ruangan segera hadir">
									Detail
								</button>
							</div>
						</div>
					@endforeach
				</div>
			@endif
		</section>

		<section class="space-y-4">
			<div class="flex items-center justify-between">
				<div>
					<h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Pengajuan Terbaru Saya</h2>
					<p class="text-slate-500 dark:text-slate-400 text-sm">Pantau status pengajuan peminjaman terakhir kamu.</p>
				</div>
			</div>

			<div class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
				<table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
					<thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-300">
						<tr>
							<th class="px-6 py-3 text-left font-medium">ID</th>
							<th class="px-6 py-3 text-left font-medium">Ruangan</th>
							<th class="px-6 py-3 text-left font-medium">Tanggal</th>
							<th class="px-6 py-3 text-left font-medium">Waktu</th>
							<th class="px-6 py-3 text-left font-medium">Tujuan</th>
							<th class="px-6 py-3 text-left font-medium">Status</th>
							<th class="px-6 py-3 text-left font-medium">Aksi</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-200 dark:divide-slate-800">
						@forelse($recentBookings as $booking)
							<tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
								<td class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-200">#{{ $booking->id }}</td>
								<td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ $booking->room->name ?? '-' }}</td>
								<td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
								<td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ substr($booking->start_time,0,5) }}–{{ substr($booking->end_time,0,5) }}</td>
								<td class="px-6 py-3 text-slate-600 dark:text-slate-300">
									@if($booking->purpose)
										<button type="button" data-purpose-modal-trigger data-purpose="{{ e($booking->purpose) }}" data-purpose-title="Tujuan · Pengajuan #{{ $booking->id }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
											<span>Lihat tujuan</span>
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
											</svg>
										</button>
									@else
										<span class="text-xs text-slate-400 dark:text-slate-500">-</span>
									@endif
								</td>
								<td class="px-6 py-3">
									@php
										$statusColors = [
											'pending' => 'bg-yellow-100 text-yellow-800',
											'approved' => 'bg-green-100 text-green-700',
											'rejected' => 'bg-red-100 text-red-700',
											'cancelled' => 'bg-slate-100 text-slate-600',
										];
										$badgeClass = $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-600';
									@endphp
									<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
										{{ ucfirst($booking->status) }}
									</span>
								</td>
								<td class="px-6 py-3 text-slate-600 dark:text-slate-300">
									<div class="flex flex-wrap items-center gap-2">
										@if($booking->status === 'pending')
											<a href="{{ route('bookings.edit', $booking) }}" class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Edit</a>
										@endif
										@if(in_array($booking->status, ['pending', 'approved']))
											<form action="{{ route('bookings.destroy', $booking) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="inline-flex items-center rounded-xl border border-red-200 dark:border-red-500/40 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-500/10 transition">Batalkan</button>
											</form>
										@else
											<span class="text-xs text-slate-400 dark:text-slate-500">-</span>
										@endif
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="7" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada pengajuan peminjaman.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

@include('components.purpose-modal')
@endsection
