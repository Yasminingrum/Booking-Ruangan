<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Kepala Sekolah') - Sistem Peminjaman Ruangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-white/10 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950/80">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/20 backdrop-blur text-lg font-semibold text-blue-300">KS</div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-blue-300">Dashboard Kepala Sekolah</p>
                        <h1 class="mt-1 text-xl font-semibold text-white">Sistem Peminjaman Fasilitas</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div class="hidden text-right sm:block">
                        <p class="text-[11px] uppercase tracking-wide text-slate-400">Masuk sebagai</p>
                        <p class="font-semibold text-white">{{ auth()->user()->name ?? '-' }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m0 0l3 3m-3-3l3-3" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-emerald-500/10 text-emerald-200 border border-emerald-500/30">
                <div class="mx-auto max-w-7xl px-4 py-3 text-sm sm:px-6 lg:px-8">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 text-red-200 border border-red-500/30">
                <div class="mx-auto max-w-7xl px-4 py-3 text-sm sm:px-6 lg:px-8">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="mt-12 border-t border-white/5 bg-slate-950/90 py-6 text-xs text-slate-500">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} Sekolah Palembang Harapan. Hak cipta dilindungi.</p>
                <p>Dashboard Kepala Sekolah • Ringkasan penggunaan fasilitas & laporan periode tertentu.</p>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
