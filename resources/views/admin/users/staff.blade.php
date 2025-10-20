@extends('admin.layouts.app')

@section('title', 'Daftar Staff')
@section('header', 'Manajemen Staff')

@section('content')
<div class="space-y-6">
  <!-- Header Actions -->
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-xl font-bold text-gray-900">Daftar Staff</h3>
      <p class="text-sm text-gray-600">Kelola data staff yang terdaftar</p>
    </div>
    <a href="{{ route('admin.users.create', 'kepala_sekolah') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
      + Tambah Staff
    </a>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Total Staff</h4>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $staff->total() }}</p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Staff Aktif</h4>
      <p class="text-3xl font-bold text-green-600 mt-2">{{ $staff->where('is_active', true)->count() }}</p>
    </div>
  </div>

  <!-- Staff Table -->
  <div class="bg-white rounded-xl shadow border">
    <div class="px-6 py-4 border-b">
      <h3 class="font-semibold text-gray-800">Semua Staff</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 border-b">
          <tr>
            <th class="px-6 py-3 text-left font-medium">#</th>
            <th class="px-6 py-3 text-left font-medium">Nama</th>
            <th class="px-6 py-3 text-left font-medium">Email</th>
            <th class="px-6 py-3 text-left font-medium">No. Telepon</th>
            <th class="px-6 py-3 text-left font-medium">Status</th>
            <th class="px-6 py-3 text-center font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($staff as $Staff)
            <tr class="border-b hover:bg-gray-50">
              <td class="px-6 py-4">{{ $loop->iteration + ($staff->currentPage() - 1) * $staff->perPage() }}</td>
              <td class="px-6 py-4 font-medium">{{ $Staff->name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $Staff->email }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $Staff->phone ?? '-' }}</td>
              <td class="px-6 py-4">
                @if($Staff->is_active)
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                @else
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Nonaktif</span>
                @endif
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <a href="{{ route('admin.users.edit', $staff) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                  <form action="{{ route('admin.users.destroy', $staff) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus staff ini?')">
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
                Belum ada data guru. <a href="{{ route('admin.users.create', 'kepala_sekolah') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($staff->hasPages())
      <div class="px-6 py-4 border-t">
        {{ $staff->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
