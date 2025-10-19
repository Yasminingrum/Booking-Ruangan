@extends('layouts.headmaster')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<div class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950/90 pb-16">
    <div class="mx-auto w-full max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-blue-300">Ringkasan Penggunaan Fasilitas</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">Dashboard Kepala Sekolah</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-300">Monitor statistik peminjaman ruangan, tingkat utilisasi, dan laporan periode tertentu untuk mengambil keputusan strategis.</p>
            </div>
            <form method="GET" action="{{ route('reports.index') }}" class="w-full max-w-xl rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col text-xs font-semibold uppercase tracking-wide text-slate-300">
                        <span>Dari</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/30" />
                    </label>
                    <label class="flex flex-col text-xs font-semibold uppercase tracking-wide text-slate-300">
                        <span>Sampai</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/30" />
                    </label>
                    <label class="flex flex-col text-xs font-semibold uppercase tracking-wide text-slate-300 sm:col-span-2">
                        <span>Kategori Ruangan</span>
                        <select name="category" class="mt-2 w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/30">
                            @foreach($categoryOptions as $value => $label)
                                <option value="{{ $value }}" @selected($value === ($filters['category'] ?? '')) class="bg-white text-slate-900">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                        Terapkan
                    </button>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                        Reset
                    </a>
                    <span class="ml-auto text-xs text-slate-400">Periode: {{ $periodLabel }}</span>
                </div>
            </form>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-4">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Total Booking</p>
                <p class="mt-3 text-3xl font-bold text-white">{{ $totalBookings }}</p>
                <p class="mt-1 text-xs text-slate-400">Periode {{ $periodLabel }}</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Pending Approval</p>
                <p class="mt-3 text-3xl font-bold text-white">{{ $pendingApproval }}</p>
                <p class="mt-1 text-xs text-slate-400">Menunggu persetujuan admin</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Ruangan Terpopuler</p>
                <p class="mt-3 text-lg font-semibold text-white">{{ optional($popularRoom?->room)->name ?? 'Belum ada data' }}</p>
                <p class="mt-1 text-xs text-slate-400">Paling sering dipakai</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Utilisasi Rata-rata</p>
                <p class="mt-3 text-3xl font-bold text-white">{{ $averageUtilization }}%</p>
                <p class="mt-1 text-xs text-slate-400">Seluruh ruangan aktif</p>
            </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Utilisasi per Ruangan</h3>
                    <button type="button" class="text-xs font-semibold text-blue-300 hover:text-blue-200">CSV</button>
                </div>
                <canvas id="utilizationChart" class="mt-6 h-64 w-full"></canvas>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Distribusi Jam Pakai</h3>
                    <button type="button" class="text-xs font-semibold text-blue-300 hover:text-blue-200">PNG</button>
                </div>
                <canvas id="hourDistributionChart" class="mt-6 h-64 w-full"></canvas>
            </div>
        </div>

        <div class="mt-10 rounded-3xl border border-white/10 bg-white/5 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 px-6 py-4">
                <div class="flex items-center gap-3 text-sm font-semibold text-slate-200">
                    <button type="button" class="rounded-xl bg-blue-500 px-3 py-1 text-xs font-semibold text-white">Tabel Detail</button>
                    <button type="button" class="rounded-xl px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/10">Rekap per Kategori</button>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <form method="POST" action="{{ route('reports.export.pdf') }}" class="inline">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                        <input type="hidden" name="type" value="detail">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-3 py-1.5 font-semibold text-slate-200 transition hover:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16h8M8 12h8m-7-8h6a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg>
                            Export PDF
                        </button>
                    </form>
                    <form method="POST" action="{{ route('reports.export.excel') }}" class="inline">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-3 py-1.5 font-semibold text-slate-200 transition hover:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l-8 8m0-8l8 8" />
                            </svg>
                            Export Excel
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-b-3xl">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-6 py-3 text-left">Ruangan</th>
                            <th class="px-6 py-3 text-left">Kategori</th>
                            <th class="px-6 py-3 text-left">Jumlah Booking</th>
                            <th class="px-6 py-3 text-left">Total Jam</th>
                            <th class="px-6 py-3 text-left">Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($roomStats as $item)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 font-semibold text-white">{{ $item['room']->name }}</td>
                                <td class="px-6 py-4 text-slate-300">{{ \App\Http\Controllers\ReportController::ROOM_TYPE_LABELS[$item['room']->type] ?? ucfirst(str_replace('_', ' ', $item['room']->type ?? 'Lainnya')) }}</td>
                                <td class="px-6 py-4">{{ $item['total_bookings'] }}</td>
                                <td class="px-6 py-4">{{ $item['total_hours'] }} jam</td>
                                <td class="px-6 py-4">{{ $item['utilization'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada data pemakaian pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="px-6 py-4 text-xs text-slate-400">*Angka dan grafik menyesuaikan periode & filter. Tombol export menyiapkan file dengan format resmi sekolah.</p>
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('reports.export.pdf') }}" class="inline">
                @csrf
                <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                <input type="hidden" name="type" value="summary">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    Export Laporan (PDF)
                </button>
            </form>
            <form method="POST" action="{{ route('reports.export.excel') }}" class="inline">
                @csrf
                <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-8.954 8.955a2.25 2.25 0 01-3.182 0L3.75 11.09" />
                    </svg>
                    Export (Excel)
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const utilizationData = @json($utilizationChart);
    const hourDistributionData = @json($hourDistribution);

    const hexToRgba = (hex, alpha = 1) => {
        const sanitized = hex.replace('#', '');
        const bigint = parseInt(sanitized, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    const palette = ['#60a5fa', '#38bdf8', '#818cf8', '#f472b6', '#f87171', '#facc15', '#34d399'];

    const utilizationCtx = document.getElementById('utilizationChart');
    if (utilizationCtx) {
        new Chart(utilizationCtx, {
            type: 'bar',
            data: {
                labels: utilizationData.map(item => item.name),
                datasets: [{
                    label: 'Utilisasi (%)',
                    data: utilizationData.map(item => item.value),
                    backgroundColor: utilizationData.map((_, idx) => hexToRgba(palette[idx % palette.length], 0.7)),
                    borderRadius: 14,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#cbd5f5',
                            callback: value => `${value}%`
                        },
                        grid: { color: 'rgba(148, 163, 184, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#cbd5f5' },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    const hourCtx = document.getElementById('hourDistributionChart');
    if (hourCtx) {
        new Chart(hourCtx, {
            type: 'doughnut',
            data: {
                labels: hourDistributionData.map(item => `${item.label}`),
                datasets: [{
                    data: hourDistributionData.map(item => item.percentage),
                    backgroundColor: hourDistributionData.map((_, idx) => hexToRgba(palette[idx % palette.length], 0.85)),
                    borderWidth: 2,
                    borderColor: '#0f172a'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#cbd5f5', boxWidth: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: ${ctx.raw}%`
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
