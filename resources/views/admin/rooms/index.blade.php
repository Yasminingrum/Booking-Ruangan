@extends('admin.layouts.app')

@section('title', 'Daftar Ruangan')
@section('header', 'Manajemen Ruangan')

@section('content')
<div class="space-y-6">
  <!-- Header Actions -->
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-xl font-bold text-gray-900">Daftar Ruangan</h3>
      <p class="text-sm text-gray-600">Kelola semua ruangan yang tersedia untuk dipinjam</p>
    </div>
    <a href="{{ route('admin.rooms.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
      + Tambah Ruangan
    </a>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Total Ruangan</h4>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $rooms->total() }}</p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Ruangan Tersedia</h4>
      <p class="text-3xl font-bold text-green-600 mt-2">{{ $rooms->where('is_available', true)->count() }}</p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Tidak Tersedia</h4>
      <p class="text-3xl font-bold text-red-600 mt-2">{{ $rooms->where('is_available', false)->count() }}</p>
    </div>
  </div>

  <!-- Rooms Table -->
  <div class="bg-white rounded-xl shadow border">
    <div class="px-6 py-4 border-b">
      <h3 class="font-semibold text-gray-800">Semua Ruangan</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 border-b">
          <tr>
            <th class="px-6 py-3 text-left font-medium">#</th>
            <th class="px-6 py-3 text-left font-medium">Nama Ruangan</th>
            <th class="px-6 py-3 text-left font-medium">Kapasitas</th>
            <th class="px-6 py-3 text-left font-medium">Fasilitas</th>
            <th class="px-6 py-3 text-left font-medium">Status</th>
            <th class="px-6 py-3 text-center font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rooms as $room)
            <tr class="border-b hover:bg-gray-50">
              <td class="px-6 py-4">{{ $loop->iteration + ($rooms->currentPage() - 1) * $rooms->perPage() }}</td>
              <td class="px-6 py-4 font-medium">{{ $room->name }}</td>
              <td class="px-6 py-4">{{ $room->capacity }} orang</td>
              <td class="px-6 py-4">
                <span class="text-xs text-gray-600">{{ Str::limit($room->facilities ?? '-', 30) }}</span>
              </td>
              <td class="px-6 py-4">
                @if($room->is_available)
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Tersedia</span>
                @else
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Tidak Tersedia</span>
                @endif
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <a href="{{ route('admin.rooms.show', $room) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                  <a href="{{ route('admin.rooms.edit', $room) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                  <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus ruangan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                Belum ada ruangan. <a href="{{ route('admin.rooms.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($rooms->hasPages())
      <div class="px-6 py-4 border-t">
        {{ $rooms->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
