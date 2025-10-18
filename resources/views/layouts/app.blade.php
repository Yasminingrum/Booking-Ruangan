<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Pengguna') - Sistem Peminjaman Ruangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style>
        body{font-family:'Inter',sans-serif;}
    </style>
    <script>
        (() => {
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-slate-100 dark:bg-slate-950 min-h-screen text-slate-900 dark:text-slate-100">
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white font-semibold">SPH</div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Peminjaman Ruangan</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Sekolah Palembang Harapan</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <a href="#" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <span>Notifikasi</span>
                    </a>
                    <a href="#" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <span>Riwayat</span>
                    </a>
                    <button type="button" id="darkmode-toggle" class="hidden md:inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle dark mode">
                        <span class="sr-only">Toggle dark mode</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5 dark:hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5 hidden dark:block">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m15.364-6.364l-1.06 1.06M6.696 17.303l-1.06 1.06m0-12.728l1.06 1.06m11.668 11.668l1.06 1.06M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
                        </svg>
                    </button>
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs uppercase tracking-wide text-slate-400">Masuk sebagai</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name ?? '' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-white font-semibold hover:bg-slate-800 transition">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('darkmode-toggle');
            if (!toggle) return;

            const setTheme = (isDark) => {
                document.documentElement.classList.toggle('dark', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                toggle.setAttribute('aria-pressed', String(isDark));
            };

            const currentTheme = localStorage.getItem('theme');
            toggle.setAttribute('aria-pressed', String(document.documentElement.classList.contains('dark')));

            toggle.addEventListener('click', () => {
                const willBeDark = !document.documentElement.classList.contains('dark');
                setTheme(willBeDark);
            });

            if (currentTheme) {
                setTheme(currentTheme === 'dark');
            }
        });
    </script>
</body>
</html>
