@extends('admin.layouts.app')

@section('title', 'Edit Ruangan')
@section('header', 'Edit Ruangan')

@section('content')
<div class="max-w-3xl">
  <div class="bg-white rounded-xl shadow border p-6">
    <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="space-y-6">
        <!-- Nama Ruangan -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan <span class="text-red-500">*</span></label>
          <input type="text" id="name" name="name" value="{{ old('name', $room->name) }}" required
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('name') border-red-500 @enderror">
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Kapasitas -->
        <div>
          <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Kapasitas <span class="text-red-500">*</span></label>
          <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" required min="1"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('capacity') border-red-500 @enderror">
          @error('capacity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Lokasi -->
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
          <input type="text" id="location" name="location" value="{{ old('location', $room->location) }}"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('location') border-red-500 @enderror">
          @error('location')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Fasilitas -->
        <div>
          <label for="facilities" class="block text-sm font-medium text-gray-700 mb-2">Fasilitas</label>
          <textarea id="facilities" name="facilities" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent @error('facilities') border-red-500 @enderror">{{ old('facilities', $room->facilities) }}</textarea>
          @error('facilities')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Status Ketersediaan -->
        <div>
          <label class="flex items-center gap-3">
            <input type="checkbox" name="is_available" value="1" {{ old('is_available', $room->is_available) ? 'checked' : '' }}
                   class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
            <span class="text-sm font-medium text-gray-700">Ruangan Tersedia untuk Dipinjam</span>
          </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 pt-4 border-t">
          <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
            Perbarui
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
