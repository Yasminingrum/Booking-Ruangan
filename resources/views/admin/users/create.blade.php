@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah User</h1>
        <p class="text-gray-600 mt-1">Tambahkan user baru ke sistem</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('admin.users.store', $role ?? '') }}" method="POST">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('name'),
                        'border-gray-300' => !$errors->has('name')
                    ])"
                    required
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('email'),
                        'border-gray-300' => !$errors->has('email')
                    ])"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('password'),
                        'border-gray-300' => !$errors->has('password')
                    ])"
                    required
                    minlength="8"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
            </div>

            <!-- Password Confirmation -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Konfirmasi Password <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                    minlength="8"
                >
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    No. Telepon
                </label>
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="{{ old('phone') }}"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('phone'),
                        'border-gray-300' => !$errors->has('phone')
                    ])"
                >
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select
                    name="role"
                    id="role"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('role'),
                        'border-gray-300' => !$errors->has('role')
                    ])"
                    required
                >
                    <option value="">Pilih Role</option>
                    <option value="kepala_sekolah" {{ (old('role', $role ?? '') == 'kepala_sekolah') ? 'selected' : '' }}>Kepala Sekolah</option>
                    <option value="guru" {{ (old('role', $role ?? '') == 'guru') ? 'selected' : '' }}>Guru</option>
                    <option value="cleaning_service" {{ (old('role', $role ?? '') == 'cleaning_service') ? 'selected' : '' }}>Cleaning Service</option>
                    <option value="peminjam" {{ (old('role', $role ?? '') == 'peminjam') ? 'selected' : '' }}>Peminjam</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    name="is_active"
                    id="is_active"
                    class="@class([
                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        'border-red-500' => $errors->has('status'),
                        'border-gray-300' => !$errors->has('status')
                    ])"
                    required
                >
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active') === 0 ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3">
                <a
                    href="{{ url()->previous() }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
