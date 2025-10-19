@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
		<div class="flex flex-wrap items-start justify-between gap-6">
			<div class="space-y-2">
				<p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Navigasi</p>
				<h1 class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white">Riwayat Peminjaman</h1>
				<p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl">Lihat seluruh riwayat pengajuan peminjamanmu, lengkap dengan status, jadwal, dan catatan dari admin.</p>
			</div>
			<a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
				<span>Kembali ke Dashboard</span>
			</a>
		</div>

		<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
			@php
				$statCards = [
					['label' => 'Total', 'value' => $statistics['total'] ?? 0],
					['label' => 'Menunggu', 'value' => $statistics['pending'] ?? 0],
					['label' => 'Disetujui', 'value' => $statistics['approved'] ?? 0],
					['label' => 'Ditolak', 'value' => $statistics['rejected'] ?? 0],
					['label' => 'Dibatalkan', 'value' => $statistics['cancelled'] ?? 0],
					['label' => 'Selesai', 'value' => $statistics['completed'] ?? 0],
				];
			@endphp

			@foreach($statCards as $card)
				<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
					<p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
					<p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ $card['value'] }}</p>
				</div>
			@endforeach
		</section>

		<section class="space-y-6">
			<form method="GET" class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-5">
				<div class="grid gap-4 md:grid-cols-4">
					<label class="block">
						<span class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</span>
						<select name="status" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
							@foreach($statusOptions as $value => $label)
								<option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
							@endforeach
						</select>
					</label>
					<label class="block">
						<span class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tanggal Mulai</span>
						<input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
					</label>
					<label class="block">
						<span class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tanggal Akhir</span>
						<input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
					</label>
					<div class="flex items-end">
						<button type="submit" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-yellow-400 px-4 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-yellow-300 transition">
							<span>Terapkan Filter</span>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-10.5 6h10.5m-7.5 6h7.5" />
							</svg>
						</button>
					</div>
				</div>

				@if(array_filter($filters))
					<div class="flex items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
						<p>Menampilkan riwayat berdasarkan filter yang dipilih.</p>
						<a href="{{ route('bookings.history') }}" class="inline-flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
								<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m-15 0h15v15" />
							</svg>
							<span>Reset</span>
						</a>
					</div>
				@endif

				@error('date_to')
					<p class="text-xs text-red-500">{{ $message }}</p>
				@enderror
			</form>

			<div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
				<table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
					<thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-300">
						<tr>
							<th class="px-6 py-3 text-left font-medium">ID</th>
							<th class="px-6 py-3 text-left font-medium">Ruangan</th>
							<th class="px-6 py-3 text-left font-medium">Jadwal</th>
							<th class="px-6 py-3 text-left font-medium">Peserta</th>
							<th class="px-6 py-3 text-left font-medium">Status</th>
							<th class="px-6 py-3 text-left font-medium">Catatan</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-200 dark:divide-slate-800">
						@php
							$statusColors = [
								'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-300/10 dark:text-yellow-300',
								'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
								'rejected' => 'bg-red-100 text-red-700 dark:bg-red-400/10 dark:text-red-300',
								'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
								'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300',
							];
						@endphp

						@forelse($bookings as $booking)
							<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/70 transition">
								<td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">#{{ $booking->id }}</td>
								<td class="px-6 py-4 text-slate-600 dark:text-slate-300 space-y-2">
									<p class="font-medium text-slate-800 dark:text-slate-100">{{ $booking->room->name ?? 'Ruangan tidak tersedia' }}</p>
									@if($booking->purpose)
										<button type="button" data-purpose-modal-trigger data-purpose="{{ e($booking->purpose) }}" data-purpose-title="Tujuan · Pengajuan #{{ $booking->id }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
											<span>Lihat tujuan</span>
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
											</svg>
										</button>
									@else
										<p class="text-xs text-slate-500 dark:text-slate-400">Tujuan tidak diisi.</p>
									@endif
								</td>
								<td class="px-6 py-4 text-slate-600 dark:text-slate-300">
									<p class="font-medium">{{ \Illuminate\Support\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</p>
									<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</p>
								</td>
								<td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $booking->participants }} orang</td>
								<td class="px-6 py-4">
									@php
										$badgeClass = $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300';
									@endphp
									<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
										{{ ucfirst($booking->status) }}
									</span>
								</td>
								<td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 space-y-1">
									@if($booking->status === 'rejected' && $booking->rejection_reason)
										<p>Alasan penolakan: <span class="font-medium text-red-600 dark:text-red-300">{{ $booking->rejection_reason }}</span></p>
									@endif
									@if($booking->status === 'approved' && $booking->approved_at)
										<p>Disetujui pada {{ \Illuminate\Support\Carbon::parse($booking->approved_at)->translatedFormat('d M Y \p\u\k\u\l H:i') }}</p>
									@endif
									@if($booking->status === 'cancelled')
										<p>Pengajuan dibatalkan olehmu.</p>
									@endif
									@if($booking->status === 'pending')
										<p>Menunggu persetujuan admin.</p>
									@endif
									@if($booking->status === 'completed')
										<p>Kegiatan telah selesai dilaksanakan.</p>
									@endif
									<p>Diajukan pada {{ \Illuminate\Support\Carbon::parse($booking->created_at)->translatedFormat('d M Y \p\u\k\u\l H:i') }}</p>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">Belum ada riwayat peminjaman yang cocok dengan filter saat ini.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			@if($bookings->hasPages())
				<div class="flex items-center justify-between gap-3 text-sm text-slate-600 dark:text-slate-300">
					<p>Menampilkan {{ $bookings->firstItem() }}-{{ $bookings->lastItem() }} dari {{ $bookings->total() }} data.</p>
					{{ $bookings->onEachSide(1)->links() }}
				</div>
			@endif
		</section>
	</div>
</div>

@include('components.purpose-modal')
@endsection
