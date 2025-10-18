<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Admin Dashboard') - Sistem Peminjaman Ruangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-black text-white flex flex-col">
      <div class="px-6 py-4 text-center border-b border-white/10">
        <h1 class="text-xl font-bold">Admin Dashboard</h1>
        <p class="text-sm text-gray-400">Sekolah Palembang Harapan</p>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : '' }} hover:bg-white/20">🏠 Beranda</a>
        <a href="{{ route('admin.bookings.pending') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.bookings.*') ? 'bg-white/10' : '' }} hover:bg-white/10">📋 Peminjaman Pending</a>
        
        <div class="mt-4 text-xs uppercase tracking-wide text-gray-400 px-3">Master Data</div>
        <a href="{{ route('admin.rooms.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.rooms.*') ? 'bg-white/10' : '' }} hover:bg-white/10">🏫 Ruangan</a>
        <a href="{{ route('admin.users.teachers') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.users.teachers') ? 'bg-white/10' : '' }} hover:bg-white/10">👨‍🏫 Guru</a>
        <a href="{{ route('admin.users.students') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.users.students') ? 'bg-white/10' : '' }} hover:bg-white/10">🎓 Siswa</a>
        <a href="{{ route('admin.settings.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.settings.*') ? 'bg-white/10' : '' }} hover:bg-white/10">⚙️ Pengaturan</a>
      </nav>

      <div class="p-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full text-left rounded-lg px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-white/10">Keluar</button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
      <!-- Header -->
      <header class="flex items-center justify-between bg-white border-b px-6 py-4 shadow-sm">
        <h2 class="text-lg font-semibold">@yield('header', 'Dashboard')</h2>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=000000&color=ffffff" class="h-8 w-8 rounded-full" alt="Avatar">
        </div>
      </header>

      <!-- Flash Messages -->
      @if (session('success'))
        <div class="mx-6 mt-4 rounded-lg bg-green-50 border border-green-200 p-4">
          <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
      @endif

      @if (session('error'))
        <div class="mx-6 mt-4 rounded-lg bg-red-50 border border-red-200 p-4">
          <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
      @endif

      <!-- Page Content -->
      <section class="p-6">
        @yield('content')
      </section>
    </main>
  </div>
</body>
</html>
