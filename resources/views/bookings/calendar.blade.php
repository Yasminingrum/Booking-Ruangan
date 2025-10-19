@extends('layouts.app')

@section('title', 'Kalender Peminjaman')

@section('content')
@php
    use App\Models\Booking;

    $statusOptions = [
        'all' => 'Semua Status',
        Booking::STATUS_APPROVED => 'Approved',
        Booking::STATUS_PENDING => 'Pending',
    ];

    $dayHeaders = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    $prevParams = array_merge($baseQuery, [
        'month' => $navigation['previous']['month'],
        'year' => $navigation['previous']['year'],
    ]);

    $nextParams = array_merge($baseQuery, [
        'month' => $navigation['next']['month'],
        'year' => $navigation['next']['year'],
    ]);

    $todayParams = array_merge($baseQuery, [
        'month' => $navigation['today']['month'],
        'year' => $navigation['today']['year'],
    ]);

    $statusColors = [
        Booking::STATUS_APPROVED => 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300',
        Booking::STATUS_PENDING => 'bg-amber-400/10 border border-amber-400/30 text-amber-700 dark:text-amber-300',
    ];
@endphp

<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-16">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
		<div class="flex flex-wrap items-start justify-between gap-6">
			<div class="space-y-2">
				<div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-white text-xs font-semibold px-3 py-1">
					<span>Kalender Peminjaman</span>
				</div>
				<h1 class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white">{{ $navigation['current']['label'] }}</h1>
				<p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl">Pantau jadwal penggunaan ruangan yang sudah disetujui maupun masih menunggu persetujuan. Pengajuan yang ditolak tidak ditampilkan di sini.</p>
			</div>
			<div class="flex flex-col items-end gap-3 w-full sm:w-auto">
				<a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
					</svg>
					<span>Kembali ke Dashboard</span>
				</a>
				<div class="flex items-center gap-2">
					<a href="{{ route('bookings.calendar', $prevParams) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Bulan sebelumnya">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
						</svg>
					</a>
					<a href="{{ route('bookings.calendar', $todayParams) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Kembali ke bulan ini">Hari ini</a>
					<a href="{{ route('bookings.calendar', $nextParams) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Bulan selanjutnya">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
						</svg>
					</a>
				</div>
				<div class="flex items-center gap-2 text-xs font-semibold text-white">
					<span class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-3 py-1">Approved</span>
					<span class="inline-flex items-center gap-1 rounded-full bg-amber-400 text-slate-900 px-3 py-1">Pending</span>
				</div>
			</div>
		</div>

		<div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
			<div class="flex flex-wrap items-center justify-between gap-4">
				<form method="GET" class="flex flex-wrap items-end gap-4">
					<input type="hidden" name="month" value="{{ $navigation['current']['month'] }}">
					<input type="hidden" name="year" value="{{ $navigation['current']['year'] }}">

					<label class="flex flex-col text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
						<span>Status</span>
						<select name="status" class="mt-2 min-w-[160px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
							@foreach($statusOptions as $value => $label)
								<option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
							@endforeach
						</select>
					</label>

					<label class="flex flex-col text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
						<span>Ruangan</span>
						<select name="room_id" class="mt-2 min-w-[200px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
							<option value="">Semua Ruangan</option>
							@foreach($rooms as $room)
								<option value="{{ $room->id }}" {{ (string) $filters['room_id'] === (string) $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
							@endforeach
						</select>
					</label>

					<button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-yellow-400 px-4 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-yellow-300 transition">
						<span>Terapkan</span>
					</button>
				</form>

				<a href="{{ route('bookings.calendar', ['month' => $navigation['current']['month'], 'year' => $navigation['current']['year']]) }}" class="text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition">Reset filter</a>
			</div>

			<div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
				<span>Total jadwal bulan ini: <strong class="text-slate-700 dark:text-slate-200">{{ $summary['total'] }}</strong></span>
				<span class="inline-flex items-center gap-1">
					<span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
					Disetujui: <strong class="text-slate-700 dark:text-slate-200">{{ $summary['approved'] }}</strong>
				</span>
				<span class="inline-flex items-center gap-1">
					<span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span>
					Menunggu: <strong class="text-slate-700 dark:text-slate-200">{{ $summary['pending'] }}</strong>
				</span>
			</div>

			<div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
				<table class="w-full text-sm">
					<thead class="bg-slate-900 text-white">
						<tr>
							@foreach($dayHeaders as $day)
								<th class="px-4 py-3 text-center font-semibold uppercase tracking-wide">{{ $day }}</th>
							@endforeach
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-slate-950">
						@foreach($weeks as $week)
							<tr>
								@foreach($week as $day)
									<td class="align-top border border-slate-200 dark:border-slate-800 p-3">
										<div @class([
											'flex items-center justify-between text-xs font-semibold',
											'text-slate-900 dark:text-slate-100' => $day['isCurrentMonth'],
											'text-slate-400 dark:text-slate-600' => !$day['isCurrentMonth'],
										])>
											<span>{{ $day['date']->format('j') }}</span>
											@if($day['isToday'])
												<span class="rounded-full bg-yellow-400 px-2 py-0.5 text-[10px] font-bold text-slate-900">Hari ini</span>
											@endif
										</div>

										@if(!empty($day['bookings']))
											<div class="mt-3 space-y-2">
												@foreach($day['bookings'] as $booking)
													<div class="rounded-xl px-3 py-2 text-xs leading-relaxed shadow-sm" @class([
														$statusColors[$booking['status']] ?? 'bg-slate-200/40 border border-slate-300 text-slate-700 dark:bg-slate-800/40 dark:border-slate-700 dark:text-slate-200',
													])>
														<p class="font-semibold">[{{ $booking['room'] }}]</p>
														@if(!empty($booking['purpose']))
															<button type="button" data-purpose-modal-trigger data-purpose="{{ e($booking['purpose']) }}" data-purpose-title="Tujuan · {{ $booking['room'] }}" class="mt-2 inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
																<span>Lihat tujuan</span>
																<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3 w-3">
																	<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
																</svg>
															</button>
														@else
															<p class="mt-2 text-[11px] text-slate-500 dark:text-slate-300">Tujuan tidak diisi.</p>
														@endif
														<p class="mt-1 flex items-center gap-1 text-[11px]">
															<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3 h-3">
																<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
															</svg>
															<span>{{ $booking['time_range'] }}</span>
														</p>
														<p class="text-[11px] text-slate-500 dark:text-slate-300">Pengaju: {{ $booking['user'] }}</p>
													</div>
												@endforeach
											</div>
										@else
											<p class="mt-4 text-[11px] text-slate-400 dark:text-slate-600">Tidak ada jadwal</p>
										@endif
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<p class="text-xs text-slate-500 dark:text-slate-400">Jam operasional: 07:00–21:00 • Tampilan saat ini: Bulanan. Tampilan mingguan akan segera hadir.</p>
		</div>
	</div>
</div>

@include('components.purpose-modal')
@endsection
