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
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
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
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 sm:hidden" data-mobile-nav-toggle aria-expanded="false" aria-label="Buka menu navigasi">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" data-mobile-nav-icon="open">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="hidden h-5 w-5" data-mobile-nav-icon="close">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @php($notificationActive = request()->routeIs('notifications.*'))
                    <a href="{{ route('notifications.index') }}" @class([
                        'hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 transition relative',
                        'bg-slate-900 text-white hover:bg-slate-800' => $notificationActive,
                        'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' => !$notificationActive,
                    ])>
                        <span>Notifikasi</span>
                        @if(($headerUnreadNotifications ?? 0) > 0)
                            <span class="absolute -top-2 -right-2 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-semibold text-white">
                                {{ $headerUnreadNotifications > 99 ? '99+' : $headerUnreadNotifications }}
                            </span>
                        @endif
                    </a>
                    @php($calendarActive = request()->routeIs('bookings.calendar'))
                    <a href="{{ route('bookings.calendar') }}" @class([
                        'hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 transition',
                        'bg-slate-900 text-white hover:bg-slate-800' => $calendarActive,
                        'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' => !$calendarActive,
                    ])>
                        <span>Kalender</span>
                    </a>
                    @php($historyActive = request()->routeIs('bookings.history'))
                    <a href="{{ route('bookings.history') }}" @class([
                        'hidden sm:inline-flex items-center gap-2 rounded-xl px-4 py-2 transition',
                        'bg-slate-900 text-white hover:bg-slate-800' => $historyActive,
                        'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' => !$historyActive,
                    ])>
                        <span>Riwayat</span>
                    </a>
                    <button type="button" data-darkmode-toggle class="hidden md:inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle dark mode" aria-pressed="false">
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
        <div id="mobile-nav" class="sm:hidden hidden border-t border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <div class="px-4 py-4 space-y-3 text-sm">
                <div class="flex flex-col gap-1 text-slate-500 dark:text-slate-400">
                    <span class="text-[11px] uppercase tracking-wide">Navigasi</span>
                </div>
                <a href="{{ route('notifications.index') }}" @class([
                    'flex items-center justify-between rounded-xl border px-4 py-3 font-semibold',
                    'border-slate-900 bg-slate-900 text-white' => $notificationActive,
                    'border-slate-200 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800' => !$notificationActive,
                ])>
                    <span>Notifikasi</span>
                    @if(($headerUnreadNotifications ?? 0) > 0)
                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-semibold text-white">
                            {{ $headerUnreadNotifications > 99 ? '99+' : $headerUnreadNotifications }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('bookings.calendar') }}" @class([
                    'flex items-center justify-between rounded-xl border px-4 py-3 font-semibold',
                    'border-slate-900 bg-slate-900 text-white' => $calendarActive,
                    'border-slate-200 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800' => !$calendarActive,
                ])>
                    <span>Kalender</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25L15 9.75" />
                    </svg>
                </a>
                <a href="{{ route('bookings.history') }}" @class([
                    'flex items-center justify-between rounded-xl border px-4 py-3 font-semibold',
                    'border-slate-900 bg-slate-900 text-white' => $historyActive,
                    'border-slate-200 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800' => !$historyActive,
                ])>
                    <span>Riwayat</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                    </svg>
                </a>
                <button type="button" data-darkmode-toggle class="flex w-full items-center justify-between rounded-xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" aria-pressed="false">
                    <span>Mode Gelap</span>
                    <span class="flex items-center gap-2 text-xs" data-darkmode-state>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 dark:hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="hidden h-4 w-4 dark:block">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m15.364-6.364l-1.06 1.06M6.696 17.303l-1.06 1.06m0-12.728l1.06 1.06m11.668 11.668l1.06 1.06M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
                        </svg>
                        <span data-darkmode-label>Off</span>
                    </span>
                </button>
                <div class="flex items-center justify-between rounded-xl border border-dashed border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <span>Masuk sebagai</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name ?? '' }}</span>
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const darkModeToggles = document.querySelectorAll('[data-darkmode-toggle]');

            const setTheme = (isDark) => {
                document.documentElement.classList.toggle('dark', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                darkModeToggles.forEach((btn) => btn.setAttribute('aria-pressed', String(isDark)));
                document.querySelectorAll('[data-darkmode-label]').forEach((label) => {
                    label.textContent = isDark ? 'On' : 'Off';
                });
            };

            if (darkModeToggles.length > 0) {
                const storedTheme = localStorage.getItem('theme');
                const initialIsDark = storedTheme ? storedTheme === 'dark' : document.documentElement.classList.contains('dark');
                setTheme(initialIsDark);

                darkModeToggles.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const willBeDark = !document.documentElement.classList.contains('dark');
                        setTheme(willBeDark);
                    });
                });
            }

            const mobileToggle = document.querySelector('[data-mobile-nav-toggle]');
            const mobilePanel = document.getElementById('mobile-nav');

            if (mobileToggle && mobilePanel) {
                const openIcon = mobileToggle.querySelector('[data-mobile-nav-icon="open"]');
                const closeIcon = mobileToggle.querySelector('[data-mobile-nav-icon="close"]');

                const setMobileMenu = (open) => {
                    if (open) {
                        mobilePanel.classList.remove('hidden');
                        mobileToggle.setAttribute('aria-expanded', 'true');
                        openIcon?.classList.add('hidden');
                        closeIcon?.classList.remove('hidden');
                    } else {
                        mobilePanel.classList.add('hidden');
                        mobileToggle.setAttribute('aria-expanded', 'false');
                        openIcon?.classList.remove('hidden');
                        closeIcon?.classList.add('hidden');
                    }
                };

                mobileToggle.addEventListener('click', () => {
                    const willOpen = mobilePanel.classList.contains('hidden');
                    setMobileMenu(willOpen);
                });

                document.addEventListener('click', (event) => {
                    if (!mobilePanel.contains(event.target) && !mobileToggle.contains(event.target)) {
                        if (!mobilePanel.classList.contains('hidden')) {
                            setMobileMenu(false);
                        }
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !mobilePanel.classList.contains('hidden')) {
                        setMobileMenu(false);
                    }
                });
            }
        });
    </script>
</body>
</html>
