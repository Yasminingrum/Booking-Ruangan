@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')
@section('header', 'Selamat Datang, ' . auth()->user()->name)

@section('content')
<div class="space-y-6">
      {{-- Summary cards --}}
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white p-5 shadow border">
          <p class="text-xs text-gray-500">Total Peminjaman</p>
          <p class="mt-2 text-2xl font-bold">{{ $totalPeminjaman ?? 0 }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border">
          <p class="text-xs text-gray-500">Total Ruangan</p>
          <p class="mt-2 text-2xl font-bold">{{ $totalRuangan ?? 0 }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border">
          <p class="text-xs text-gray-500">Total Pengguna</p>
          <p class="mt-2 text-2xl font-bold">{{ $totalUsers ?? 0 }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border">
          <p class="text-xs text-gray-500">Menunggu Persetujuan</p>
          <p class="mt-2 text-2xl font-bold">{{ $pendingCount ?? 0 }}</p>
        </div>
      </div>

      {{-- Status count --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg bg-white p-4 border">
          <div class="text-xs text-gray-500">Approved</div>
          <div class="text-xl font-semibold">{{ $approvedCount ?? 0 }}</div>
        </div>
        <div class="rounded-lg bg-white p-4 border">
          <div class="text-xs text-gray-500">Rejected</div>
          <div class="text-xl font-semibold">{{ $rejectedCount ?? 0 }}</div>
        </div>
        <a href="{{ route('admin.bookings.pending') }}" class="rounded-lg bg-indigo-600 text-white p-4 text-center hover:bg-indigo-700">Kelola Pending →</a>
      </div>

      {{-- Recent bookings --}}
      <div class="rounded-xl bg-white shadow border">
        <div class="px-5 py-4 border-b flex items-center justify-between">
          <h3 class="font-semibold">Peminjaman Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="px-4 py-2 text-left">Peminjam</th>
                <th class="px-4 py-2 text-left">Ruangan</th>
                <th class="px-4 py-2 text-left">Tanggal</th>
                <th class="px-4 py-2 text-left">Waktu</th>
                <th class="px-4 py-2 text-left">Status</th>
              </tr>
            </thead>
            <tbody>
            @forelse($recentBookings ?? [] as $b)
              <tr class="border-t">
                <td class="px-4 py-2">{{ $b->user->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ $b->room->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($b->booking_date)->format('d M Y') }}</td>
                <td class="px-4 py-2">{{ substr($b->start_time,0,5) }}–{{ substr($b->end_time,0,5) }}</td>
                <td class="px-4 py-2">
                  @php
                    $cls = match($b->status){
                      'approved' => 'bg-green-100 text-green-700',
                      'rejected' => 'bg-red-100 text-red-700',
                      'pending'  => 'bg-yellow-100 text-yellow-700',
                      default    => 'bg-gray-100 text-gray-700'
                    };
                  @endphp
                  <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cls }}">{{ ucfirst($b->status) }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
</div>
@endsection
