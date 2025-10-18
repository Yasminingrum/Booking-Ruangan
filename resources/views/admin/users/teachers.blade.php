@extends('admin.layouts.app')

@section('title', 'Daftar Guru')
@section('header', 'Manajemen Guru')

@section('content')
<div class="space-y-6">
  <!-- Header Actions -->
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-xl font-bold text-gray-900">Daftar Guru</h3>
      <p class="text-sm text-gray-600">Kelola data guru yang terdaftar</p>
    </div>
    <a href="{{ route('admin.users.create', 'peminjam') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
      + Tambah Guru
    </a>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Total Guru</h4>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $teachers->total() }}</p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Guru Aktif</h4>
      <p class="text-3xl font-bold text-green-600 mt-2">{{ $teachers->where('is_active', true)->count() }}</p>
    </div>
  </div>

  <!-- Teachers Table -->
  <div class="bg-white rounded-xl shadow border">
    <div class="px-6 py-4 border-b">
      <h3 class="font-semibold text-gray-800">Semua Guru</h3>
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
          @forelse($teachers as $teacher)
            <tr class="border-b hover:bg-gray-50">
              <td class="px-6 py-4">{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
              <td class="px-6 py-4 font-medium">{{ $teacher->name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $teacher->email }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $teacher->phone ?? '-' }}</td>
              <td class="px-6 py-4">
                @if($teacher->is_active)
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                @else
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Nonaktif</span>
                @endif
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <a href="{{ route('admin.users.edit', $teacher) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                  <form action="{{ route('admin.users.destroy', $teacher) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus guru ini?')">
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
                Belum ada data guru. <a href="{{ route('admin.users.create', 'peminjam') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($teachers->hasPages())
      <div class="px-6 py-4 border-t">
        {{ $teachers->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
