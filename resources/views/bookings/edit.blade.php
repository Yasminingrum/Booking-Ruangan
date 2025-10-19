@extends('layouts.app')

@section('title', 'Edit Pengajuan Peminjaman')

@section('content')
@php
    $selectedRoomId = (int) ($defaults['room_id'] ?? 0);
    $selectedRoom = $rooms->firstWhere('id', $selectedRoomId);
    $roomsPayload = $rooms->map(fn ($room) => [
        'id' => $room->id,
        'name' => $room->name,
        'location' => $room->location,
        'type' => ucfirst(str_replace('_', ' ', $room->type ?? 'umum')),
        'capacity' => $room->capacity,
        'facilities' => $room->facilities,
        'is_active' => (bool) $room->is_active,
    ])->values();
@endphp

<div class="bg-slate-100 dark:bg-slate-950 min-h-screen pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Perbarui Pengajuan</p>
                <h1 class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white">Edit Pengajuan Peminjaman</h1>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl">Ubah jadwal, ruangan, atau rincian kegiatan sebelum disetujui admin. Pengajuan yang sudah disetujui tidak dapat diubah dari halaman ini.</p>
            </div>
            <a href="{{ route('bookings.history') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span>Kembali ke Riwayat</span>
            </a>
        </div>

        @if($errors->has('booking'))
            <div class="rounded-2xl border border-red-300/60 bg-red-500/15 px-4 py-3 text-sm text-red-700 dark:text-red-200">
                {{ $errors->first('booking') }}
            </div>
        @endif

        @if($errors->any() && !$errors->has('booking'))
            <div class="rounded-2xl border border-red-300/60 bg-red-500/15 px-4 py-3 text-sm text-red-700 dark:text-red-200">
                Terdapat beberapa kesalahan pada formulir. Silakan periksa kembali isianmu.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 space-y-4">
                <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm" id="room-summary">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Info Ruangan</h2>
                    @if($selectedRoom)
                        <div class="mt-4 space-y-4 text-sm text-slate-600 dark:text-slate-300" data-room-summary="true">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Nama</p>
                                <p class="font-medium text-slate-900 dark:text-white" data-room-name>{{ $selectedRoom->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Lokasi</p>
                                <p data-room-location>{{ $selectedRoom->location ?? 'Lokasi belum diatur' }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Tipe</p>
                                    <p data-room-type>{{ ucfirst(str_replace('_', ' ', $selectedRoom->type ?? 'umum')) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Kapasitas</p>
                                    <p data-room-capacity>{{ $selectedRoom->capacity }} orang</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Fasilitas</p>
                                <div class="mt-2 space-y-1" data-room-facilities>
                                    @if($selectedRoom->facilities)
                                        @foreach(preg_split('/[,;]+/', $selectedRoom->facilities) as $facility)
                                            @if(trim($facility) !== '')
                                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs mr-2 mb-2">{{ trim($facility) }}</span>
                                            @endif
                                        @endforeach
                                    @else
                                        <p>Fasilitas belum diatur.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Pilih ruangan pada formulir di samping untuk melihat detailnya.</p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2">
                <form action="{{ route('bookings.update', $booking) }}" method="POST" class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Ruangan</span>
                            <select name="room_id" id="room-selector" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
                                <option value="" disabled {{ $selectedRoom ? '' : 'selected' }}>Pilih ruangan</option>
                                @foreach($rooms as $roomOption)
                                    <option value="{{ $roomOption->id }}" {{ (int) $defaults['room_id'] === $roomOption->id ? 'selected' : '' }}>
                                        {{ $roomOption->name }} — {{ $roomOption->location }} ({{ $roomOption->capacity }} orang)
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </label>
                        <label class="block">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tanggal</span>
                            <input type="date" name="booking_date" value="{{ $defaults['booking_date'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
                            @error('booking_date')
                                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jam Mulai</span>
                            <input type="time" name="start_time" value="{{ $defaults['start_time'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
                            @error('start_time')
                                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </label>
                        <label class="block">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jam Selesai</span>
                            <input type="time" name="end_time" value="{{ $defaults['end_time'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
                            @error('end_time')
                                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jumlah Peserta</span>
                        <input type="number" name="participants" min="1" value="{{ $defaults['participants'] }}" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>
                        @error('participants')
                            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tujuan Peminjaman</span>
                        <textarea name="purpose" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950/60 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50" required>{{ $defaults['purpose'] }}</textarea>
                        @error('purpose')
                            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perubahan akan menggantikan pengajuan sebelumnya dan tetap menunggu persetujuan admin.</p>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-yellow-400 px-5 py-3 text-sm font-semibold text-slate-900 shadow hover:bg-yellow-300 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rooms = @json($roomsPayload);
        const selector = document.getElementById('room-selector');
        const nameEl = document.querySelector('[data-room-name]');
        const locationEl = document.querySelector('[data-room-location]');
        const typeEl = document.querySelector('[data-room-type]');
        const capacityEl = document.querySelector('[data-room-capacity]');
        const facilitiesContainer = document.querySelector('[data-room-facilities]');

        const renderFacilities = (facilities) => {
            if (!facilitiesContainer) return;
            facilitiesContainer.innerHTML = '';

            if (!facilities) {
                facilitiesContainer.innerHTML = '<p>Fasilitas belum diatur.</p>';
                return;
            }

            facilities.split(/[,;]+/).map(item => item.trim()).filter(Boolean).forEach(item => {
                const pill = document.createElement('span');
                pill.className = 'inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs mr-2 mb-2';
                pill.textContent = item;
                facilitiesContainer.appendChild(pill);
            });
        };

        const updateSummary = (roomId) => {
            const room = rooms.find(r => r.id === Number(roomId));
            if (!room) return;

            if (nameEl) nameEl.textContent = room.name;
            if (locationEl) locationEl.textContent = room.location ?? 'Lokasi belum diatur';
            if (typeEl) typeEl.textContent = room.type ?? 'Umum';
            if (capacityEl) capacityEl.textContent = `${room.capacity} orang`;
            renderFacilities(room.facilities);
        };

        if (selector) {
            selector.addEventListener('change', (event) => {
                updateSummary(event.target.value);
            });
        }
    });
</script>
@endpush
@endsection
