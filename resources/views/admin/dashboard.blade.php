<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Sistem Peminjaman Ruangan</title>
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
        <a href="#" class="block rounded-lg px-3 py-2 bg-white/10 hover:bg-white/20">🏠 Beranda</a>
        <a href="#" class="block rounded-lg px-3 py-2 hover:bg-white/10">📋 Data Peminjaman</a>
        <a href="#" class="block rounded-lg px-3 py-2 hover:bg-white/10">🏫 Ruangan</a>
        <a href="#" class="block rounded-lg px-3 py-2 hover:bg-white/10">👨‍🏫 Guru</a>
        <a href="#" class="block rounded-lg px-3 py-2 hover:bg-white/10">🎓 Siswa</a>
        <a href="#" class="block rounded-lg px-3 py-2 hover:bg-white/10">⚙️ Pengaturan</a>
      </nav>

      <div class="p-4 border-t border-white/10">
        <form method="POST" action="{{ url('/logout') }}">
          @csrf
          <button type="submit" class="w-full text-left rounded-lg px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-white/10">Keluar</button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
      <header class="flex items-center justify-between bg-white border-b px-6 py-4 shadow-sm">
        <h2 class="text-lg font-semibold">Selamat Datang, Admin</h2>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">{{ Auth::user()->name ?? 'Admin' }}</span>
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=000000&color=ffffff" class="h-8 w-8 rounded-full" alt="Avatar">
        </div>
      </header>

      <!-- Dashboard content -->
      <section class="p-6 space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="rounded-xl bg-white p-6 shadow border border-gray-100">
            <h3 class="text-sm font-medium text-gray-500">Total Peminjaman</h3>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalPeminjaman ?? 0 }}</p>
          </div>

          <div class="rounded-xl bg-white p-6 shadow border border-gray-100">
            <h3 class="text-sm font-medium text-gray-500">Total Ruangan</h3>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalRuangan ?? 0 }}</p>
          </div>

          <div class="rounded-xl bg-white p-6 shadow border border-gray-100">
            <h3 class="text-sm font-medium text-gray-500">Total Pengguna</h3>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalUsers ?? 0 }}</p>
          </div>
        </div>

        <!-- Recent Borrowings Table -->
        <div class="bg-white rounded-xl shadow border border-gray-100">
          <div class="px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-800">Peminjaman Terbaru</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-100 text-gray-700">
                <tr>
                  <th class="px-4 py-2 text-left">Nama Peminjam</th>
                  <th class="px-4 py-2 text-left">Ruangan</th>
                  <th class="px-4 py-2 text-left">Tanggal</th>
                  <th class="px-4 py-2 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentBookings ?? [] as $booking)
                  <tr class="border-t">
                    <td class="px-4 py-2">{{ $booking->user->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $booking->room->nama ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $booking->tanggal ?? '-' }}</td>
                    <td class="px-4 py-2">
                      <span class="px-2 py-1 rounded-full text-xs font-medium {{ $booking->status == 'Disetujui' ? 'bg-green-100 text-green-700' : ($booking->status == 'Ditolak' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ $booking->status }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada data peminjaman.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
