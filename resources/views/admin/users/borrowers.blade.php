@extends('admin.layouts.app')

@section('title', 'Daftar Peminjam')
@section('header', 'Manajemen Peminjam')

@section('content')
<div class="space-y-6">
  <!-- Header Actions -->
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-xl font-bold text-gray-900">Daftar Peminjam</h3>
      <p class="text-sm text-gray-600">Kelola data peminjam yang terdaftar</p>
    </div>
    <a href="{{ route('admin.users.create', 'peminjam') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
      + Tambah Peminjam
    </a>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Total Peminjam</h4>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $borrowers->total() }}</p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow border">
      <h4 class="text-sm font-medium text-gray-500">Peminjam Aktif</h4>
      <p class="text-3xl font-bold text-green-600 mt-2">{{ $borrowers->where('is_active', true)->count() }}</p>
    </div>
  </div>

  <!-- Borrowers Table -->
  <div class="bg-white rounded-xl shadow border">
    <div class="px-6 py-4 border-b">
      <h3 class="font-semibold text-gray-800">Semua Pmeinjam</h3>
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
          @forelse($borrowers as $borrower)
            <tr class="border-b hover:bg-gray-50">
              <td class="px-6 py-4">{{ $loop->iteration + ($borrowers->currentPage() - 1) * $borrowers->perPage() }}</td>
              <td class="px-6 py-4 font-medium">{{ $borrower->name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $borrower->email }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $borrower->phone ?? '-' }}</td>
              <td class="px-6 py-4">
                @if($borrower->is_active)
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                @else
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Nonaktif</span>
                @endif
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <a href="{{ route('admin.users.edit', $borrower) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                  <form action="{{ route('admin.users.destroy', $borrower) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus peminjam ini?')">
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
                Belum ada data peminjam. <a href="{{ route('admin.users.create', 'peminjam') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($borrowers->hasPages())
      <div class="px-6 py-4 border-t">
        {{ $borrowers->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
