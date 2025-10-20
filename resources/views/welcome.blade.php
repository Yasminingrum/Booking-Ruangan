<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Aplikasi Peminjaman Ruangan - Sekolah Palembang Harapan</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .feature-card {
            transition: all 0.3s ease;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-black text-white h-full">

    <!-- Header/Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-black/50 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo dan Nama Aplikasi -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/10 backdrop-blur-sm">
                            <span class="text-sm font-semibold tracking-wide">SPH</span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-bold text-white">Peminjaman Ruangan</h1>
                        <p class="text-xs text-gray-400">Sekolah Palembang Harapan</p>
                    </div>
                </div>

                <!-- Tombol Login -->
                <div>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-6 py-2.5 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white font-medium rounded-xl transition duration-200 border border-white/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Background image -->
        <img src="https://proditp.unismuh.ac.id/wp-content/uploads/2023/01/Lab-Komputer-SMP-Negeri-12-Binjai-Gambar-Ilustrasi-768x439.jpg"
             alt="Lab Komputer Sekolah Palembang Harapan"
             class="absolute inset-0 h-full w-full object-cover object-center opacity-40" />
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/60 to-black/90"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 fade-in">
            <div class="text-center">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 backdrop-blur-sm">
                    <span class="text-2xl font-semibold tracking-wide">SPH</span>
                </div>
                <h2 class="text-4xl md:text-6xl font-extrabold mb-6">
                    Sistem Peminjaman Ruangan Digital
                </h2>
                <p class="text-xl md:text-2xl mb-10 text-gray-300">
                    Solusi modern untuk mengelola peminjaman fasilitas sekolah
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-xl hover:bg-white/20 transition duration-200 border-2 border-white/30 text-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Mulai Sekarang
                    </a>
                    <a href="#features"
                       class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition duration-200 text-lg">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-black relative">
        <div class="absolute inset-0 bg-gradient-to-b from-black via-gray-900 to-black opacity-50"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Fitur Unggulan
                </h3>
                <p class="text-xl text-gray-400">
                    Kemudahan dalam mengelola peminjaman ruangan sekolah
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Pencarian Real-Time</h4>
                    <p class="text-gray-400">
                        Cek ketersediaan ruangan secara langsung berdasarkan tanggal dan waktu yang diinginkan
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Approval Otomatis</h4>
                    <p class="text-gray-400">
                        Sistem persetujuan yang cepat dan terstruktur dengan notifikasi otomatis
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Cegah Konflik Jadwal</h4>
                    <p class="text-gray-400">
                        Validasi otomatis untuk mencegah double booking pada ruangan yang sama
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Laporan Lengkap</h4>
                    <p class="text-gray-400">
                        Dashboard dan laporan penggunaan ruangan untuk evaluasi dan pengambilan keputusan
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Notifikasi</h4>
                    <p class="text-gray-400">
                        Dapatkan notifikasi real-time untuk setiap update status peminjaman Anda
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card glass-card rounded-2xl p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/10 border border-white/20 rounded-xl mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Akses Mobile</h4>
                    <p class="text-gray-400">
                        Responsive design yang dapat diakses dari desktop, tablet, atau smartphone
                    </p>
                </div>
            </div>
        </div>
    </section>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Rooms Preview -->
    <section class="py-20 bg-gradient-to-b from-black to-gray-900 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Ruangan Yang Tersedia
                </h3>
                <p class="text-xl text-gray-400">
                    Berbagai fasilitas modern untuk mendukung kegiatan sekolah
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Room 1 -->
                <div class="glass-card rounded-2xl p-6 hover:bg-white/15 transition">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/10 border border-white/20 text-white rounded-xl mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-2">Lab Komputer</h5>
                    <p class="text-sm text-gray-400">Kapasitas: 35 siswa</p>
                </div>

                <!-- Room 2 -->
                <div class="glass-card rounded-2xl p-6 hover:bg-white/15 transition">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/10 border border-white/20 text-white rounded-xl mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-2">Ruang Musik</h5>
                    <p class="text-sm text-gray-400">Kapasitas: 30 siswa</p>
                </div>

                <!-- Room 3 -->
                <div class="glass-card rounded-2xl p-6 hover:bg-white/15 transition">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/10 border border-white/20 text-white rounded-xl mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-2">Audio Visual</h5>
                    <p class="text-sm text-gray-400">Kapasitas: 100 orang</p>
                </div>

                <!-- Room 4 -->
                <div class="glass-card rounded-2xl p-6 hover:bg-white/15 transition">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/10 border border-white/20 text-white rounded-xl mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-2">Lapangan Basket</h5>
                    <p class="text-sm text-gray-400">Outdoor (3 lapangan)</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About -->
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">Tentang Sistem</h4>
                    <p class="text-sm text-gray-400">
                        Aplikasi Peminjaman Ruangan dirancang untuk memudahkan pengelolaan fasilitas di Sekolah Palembang Harapan.
                    </p>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            admin@palembangharapan.sch.id
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            (021) 1234-5678
                        </li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">Akses Cepat</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
                        </li>
                        <li>
                            <a href="#features" class="hover:text-white transition">Fitur</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-white transition">Panduan Pengguna</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                <p>&copy; 2025 Sekolah Palembang Harapan. All rights reserved.</p>
                <p class="mt-2 text-gray-500">Developed by Tim IT Sekolah Palembang Harapan</p>
            </div>
        </div>
    </footer>

</body>
</html>
