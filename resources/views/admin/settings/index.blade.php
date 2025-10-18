@extends('admin.layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan')

@section('content')
<div class="max-w-4xl space-y-6">
  <!-- General Settings -->
  <div class="bg-white rounded-xl shadow border p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Pengaturan Umum</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST">
      @csrf
      
      <div class="space-y-6">
        <!-- School Name -->
        <div>
          <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Sekolah</label>
          <input type="text" id="school_name" name="school_name" value="{{ old('school_name', 'Sekolah Palembang Harapan') }}"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
        </div>

        <!-- Contact Email -->
        <div>
          <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Email Kontak</label>
          <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', 'admin@palembangharapan.sch.id') }}"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
        </div>

        <!-- Phone -->
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
          <input type="text" id="phone" name="phone" value="{{ old('phone', '0711-123456') }}"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
        </div>

        <!-- Address -->
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
          <textarea id="address" name="address" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">{{ old('address', 'Jl. Contoh No. 123, Palembang') }}</textarea>
        </div>
      </div>

      <div class="mt-6 pt-6 border-t">
        <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  <!-- Booking Settings -->
  <div class="bg-white rounded-xl shadow border p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Pengaturan Peminjaman</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST">
      @csrf
      
      <div class="space-y-6">
        <!-- Max Days Advance Booking -->
        <div>
          <label for="max_advance_days" class="block text-sm font-medium text-gray-700 mb-2">
            Maksimal Hari Peminjaman di Muka
          </label>
          <input type="number" id="max_advance_days" name="max_advance_days" value="{{ old('max_advance_days', 30) }}" min="1"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
          <p class="mt-1 text-xs text-gray-500">Berapa hari ke depan user bisa booking ruangan</p>
        </div>

        <!-- Min Booking Duration -->
        <div>
          <label for="min_duration" class="block text-sm font-medium text-gray-700 mb-2">
            Durasi Minimum Peminjaman (Menit)
          </label>
          <input type="number" id="min_duration" name="min_duration" value="{{ old('min_duration', 60) }}" min="15" step="15"
                 class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
        </div>

        <!-- Auto Approve -->
        <div>
          <label class="flex items-center gap-3">
            <input type="checkbox" name="auto_approve" value="1" {{ old('auto_approve', false) ? 'checked' : '' }}
                   class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
            <span class="text-sm font-medium text-gray-700">Otomatis Approve Peminjaman</span>
          </label>
          <p class="mt-1 text-xs text-gray-500 ml-7">Jika diaktifkan, peminjaman akan langsung disetujui tanpa perlu approval admin</p>
        </div>

        <!-- Notifications -->
        <div>
          <label class="flex items-center gap-3">
            <input type="checkbox" name="email_notifications" value="1" {{ old('email_notifications', true) ? 'checked' : '' }}
                   class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
            <span class="text-sm font-medium text-gray-700">Kirim Notifikasi Email</span>
          </label>
        </div>
      </div>

      <div class="mt-6 pt-6 border-t">
        <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  <!-- System Info -->
  <div class="bg-white rounded-xl shadow border p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Sistem</h3>
    <div class="space-y-3 text-sm">
      <div class="flex justify-between py-2 border-b">
        <span class="text-gray-600">Versi Laravel</span>
        <span class="font-medium">{{ app()->version() }}</span>
      </div>
      <div class="flex justify-between py-2 border-b">
        <span class="text-gray-600">Versi PHP</span>
        <span class="font-medium">{{ phpversion() }}</span>
      </div>
      <div class="flex justify-between py-2 border-b">
        <span class="text-gray-600">Database</span>
        <span class="font-medium">{{ config('database.default') }}</span>
      </div>
      <div class="flex justify-between py-2">
        <span class="text-gray-600">Timezone</span>
        <span class="font-medium">{{ config('app.timezone') }}</span>
      </div>
    </div>
  </div>

  <!-- Cache Management -->
  <div class="bg-white rounded-xl shadow border p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Manajemen Cache</h3>
    <p class="text-sm text-gray-600 mb-4">Bersihkan cache aplikasi jika mengalami masalah</p>
    <div class="flex gap-3">
      <button onclick="alert('Fitur ini memerlukan implementasi backend')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
        Clear Cache
      </button>
      <button onclick="alert('Fitur ini memerlukan implementasi backend')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
        Clear Config
      </button>
      <button onclick="alert('Fitur ini memerlukan implementasi backend')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
        Clear Routes
      </button>
    </div>
  </div>
</div>
@endsection
