@extends('layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
@php
    use App\Models\Notification;

    $statusOptions = [
        'all' => 'Semua Status',
        'unread' => 'Belum Dibaca',
        'read' => 'Sudah Dibaca',
    ];

    $typeLabels = [
        Notification::TYPE_BOOKING_CREATED => 'Pengajuan Baru',
        Notification::TYPE_BOOKING_APPROVED => 'Disetujui',
        Notification::TYPE_BOOKING_REJECTED => 'Ditolak',
        Notification::TYPE_BOOKING_CANCELLED => 'Dibatalkan',
        Notification::TYPE_BOOKING_REMINDER => 'Pengingat',
    ];
@endphp

<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pusat Informasi</p>
                <h1 class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white">Notifikasi</h1>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl">Pantau status pengajuan peminjamanmu dan update terbaru lainnya dari admin. Tandai sudah dibaca atau hapus notifikasi yang tidak diperlukan.</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    <span>Kembali ke Dashboard</span>
                </a>
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
                <form action="{{ route('notifications.delete-read') }}" method="POST" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-500/40 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Hapus yang Sudah Dibaca</span>
                    </button>
                </form>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-300/60 bg-emerald-500/15 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ $summary['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Belum Dibaca</p>
                <p class="mt-3 text-3xl font-semibold text-amber-500">{{ $summary['unread'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sudah Dibaca</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-500">{{ $summary['read'] ?? 0 }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <form method="GET" class="grid gap-4 md:grid-cols-3">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <span>Status</span>
                    <select name="status" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <span>Jenis</span>
                    <select name="type" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50">
                        <option value="">Semua Jenis</option>
                        @foreach($typeLabels as $type => $label)
                            <option value="{{ $type }}" {{ $filters['type'] === $type ? 'selected' : '' }}>
                                {{ $label }}
                                @if($typeCounts[$type] ?? false)
                                    ({{ $typeCounts[$type] }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-yellow-400 px-4 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-yellow-300 transition w-full md:w-auto">
                        <span>Terapkan</span>
                    </button>
                    <a href="{{ route('notifications.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition w-full md:w-auto">
                        Reset
                    </a>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = !$notification->is_read;
                        $badgeClasses = $isUnread
                            ? 'bg-amber-400/20 text-amber-600 border border-amber-300'
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700';
                        $typeBadge = match($notification->type) {
                            Notification::TYPE_BOOKING_APPROVED => 'bg-emerald-500/15 text-emerald-600 border border-emerald-300',
                            Notification::TYPE_BOOKING_REJECTED => 'bg-red-500/15 text-red-600 border border-red-300',
                            Notification::TYPE_BOOKING_CANCELLED => 'bg-slate-500/15 text-slate-600 border border-slate-300',
                            Notification::TYPE_BOOKING_REMINDER => 'bg-blue-500/15 text-blue-600 border border-blue-300',
                            default => 'bg-yellow-500/15 text-yellow-600 border border-yellow-300',
                        };
                    @endphp
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                                    <span class="rounded-full px-3 py-1 {{ $badgeClasses }}">
                                        {{ $isUnread ? 'Belum Dibaca' : 'Sudah Dibaca' }}
                                    </span>
                                    <span class="rounded-full px-3 py-1 {{ $typeBadge }}">
                                        {{ $typeLabels[$notification->type] ?? ucfirst(str_replace('_', ' ', $notification->type)) }}
                                    </span>
                                </div>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $notification->title }}</h2>
                                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $notification->message }}</p>

                                @if($notification->booking)
                                    <div class="mt-3 inline-flex items-center gap-2 rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-2 text-xs text-slate-500 dark:text-slate-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-12 8h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $notification->booking->room->name ?? 'Ruangan' }} • {{ \Illuminate\Support\Carbon::parse($notification->booking->booking_date)->translatedFormat('d M Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-3 text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ $notification->created_at?->diffForHumans() ?? '-' }}</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <form action="{{ route('notifications.toggle-read', $notification) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                            </svg>
                                            <span>{{ $isUnread ? 'Tandai Dibaca' : 'Tandai Belum Dibaca' }}</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-500/40 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-8 text-center text-slate-500 dark:text-slate-400">
                        Tidak ada notifikasi yang cocok dengan filter saat ini.
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="flex items-center justify-between gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <p>Menampilkan {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi.</p>
                    {{ $notifications->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
