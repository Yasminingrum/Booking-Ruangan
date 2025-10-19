@extends('admin.layouts.app')

@section('title', 'Pending Approval')
@section('header', 'Pengajuan Menunggu Persetujuan')

@section('content')
<div class="space-y-6">
  <!-- Header Actions -->
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-xl font-bold text-gray-900">Pending Approval</h3>
      <p class="text-sm text-gray-600">Kelola pengajuan peminjaman ruangan</p>
    </div>
  </div>

  <!-- Pending Bookings Table -->
  <div class="bg-white rounded-xl shadow border overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Peminjam</th>
            <th class="px-4 py-2 text-left">Ruangan</th>
            <th class="px-4 py-2 text-left">Tanggal</th>
            <th class="px-4 py-2 text-left">Waktu</th>
            <th class="px-4 py-2 text-left">Tujuan</th>
            <th class="px-4 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse($bookings as $b)
          <tr class="border-t">
            <td class="px-4 py-2">#{{ $b->id }}</td>
            <td class="px-4 py-2">{{ $b->user->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $b->room->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($b->booking_date)->format('d M Y') }}</td>
            <td class="px-4 py-2">{{ substr($b->start_time,0,5) }}–{{ substr($b->end_time,0,5) }}</td>
            <td class="px-4 py-2">
              @if($b->purpose)
                <button type="button" data-purpose-modal-trigger data-purpose="{{ e($b->purpose) }}" data-purpose-title="Tujuan · Pengajuan #{{ $b->id }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <span>Lihat tujuan</span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                  </svg>
                </button>
              @else
                <span class="text-xs text-gray-400">-</span>
              @endif
            </td>
            <td class="px-4 py-2">
              <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.bookings.approve', $b) }}">
                  @csrf
                  <button class="px-3 py-1 rounded-md bg-green-600 text-white text-xs hover:bg-green-700">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.bookings.reject', $b) }}" onsubmit="return confirm('Yakin tolak?')">
                  @csrf
                  <input type="text" name="reason" required placeholder="Alasan" class="border rounded px-2 py-1 text-xs">
                  <button class="px-3 py-1 rounded-md bg-red-600 text-white text-xs hover:bg-red-700">Reject</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada pengajuan pending.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
      {{ $bookings->links() }}
    </div>
  </div>
</div>
@endsection

@include('components.purpose-modal')
