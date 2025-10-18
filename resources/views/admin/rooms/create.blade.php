@extends('admin.layouts.app')

@section('title', isset($room) ? 'Edit Ruangan' : 'Tambah Ruangan')
@section('header', isset($room) ? 'Edit Ruangan' : 'Tambah Ruangan Baru')

@section('content')
<div class="max-w-3xl">
  <div class="bg-white rounded-xl shadow border p-6">
    <form action="{{ isset($room) ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}" method="POST">
      @csrf
      @if(isset($room))
        @method('PUT')
      @endif

      <div class="space-y-6">
        <!-- Nama Ruangan -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan <span class="text-red-500">*</span></label>
          <input type="text" id="name" name="name" value="{{ old('name', $room->name ?? '') }}" required
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('name') border-red-500 @enderror"
                 placeholder="Contoh: Lab Komputer 1">
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Jenis Ruangan -->
        <div>
          <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Ruangan <span class="text-red-500">*</span></label>
          <select id="type" name="type" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('type') border-red-500 @enderror">
            <option value="">Pilih Jenis</option>
            <option value="laboratorium" {{ old('type', $room->type ?? '') == 'laboratorium' ? 'selected' : '' }}>Laboratorium</option>
            <option value="ruang_musik" {{ old('type', $room->type ?? '') == 'ruang_musik' ? 'selected' : '' }}>Ruang Musik</option>
            <option value="audio_visual" {{ old('type', $room->type ?? '') == 'audio_visual' ? 'selected' : '' }}>Audio Visual</option>
            <option value="lapangan_basket" {{ old('type', $room->type ?? '') == 'lapangan_basket' ? 'selected' : '' }}>Lapangan Basket</option>
            <option value="kolam_renang" {{ old('type', $room->type ?? '') == 'kolam_renang' ? 'selected' : '' }}>Kolam Renang</option>
          </select>
          @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Kapasitas -->
        <div>
          <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Kapasitas <span class="text-red-500">*</span></label>
          <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity ?? '') }}" required min="1"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('capacity') border-red-500 @enderror"
                 placeholder="Jumlah orang">
          @error('capacity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Lokasi -->
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
          <input type="text" id="location" name="location" value="{{ old('location', $room->location ?? '') }}"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('location') border-red-500 @enderror"
                 placeholder="Contoh: Lantai 2, Gedung A">
          @error('location')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Fasilitas -->
        <div>
          <label for="facilities" class="block text-sm font-medium text-gray-700 mb-2">Fasilitas</label>
          <textarea id="facilities" name="facilities" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('facilities') border-red-500 @enderror"
                    placeholder="Contoh: Proyektor, AC, Whiteboard, 30 Komputer">{{ old('facilities', $room->facilities ?? '') }}</textarea>
          @error('facilities')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Status Aktif -->
        <div>
          <label class="flex items-center gap-3">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
            <span class="text-sm font-medium text-gray-700">Ruangan Aktif (bisa dipinjam)</span>
          </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 pt-4 border-t">
          <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
            {{ isset($room) ? 'Perbarui' : 'Simpan' }}
          </button>
          <a href="{{ route('admin.rooms.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Batal
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
