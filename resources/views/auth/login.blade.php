<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistem Peminjaman Ruangan</title>
  <!-- PROTOTYPE: Tailwind via CDN. Untuk produksi, gunakan Vite + Tailwind build. -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <style> body{ font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full">
  <main class="relative min-h-screen flex items-center justify-center overflow-hidden bg-black text-white">
    <!-- Background image -->
    <img src="https://proditp.unismuh.ac.id/wp-content/uploads/2023/01/Lab-Komputer-SMP-Negeri-12-Binjai-Gambar-Ilustrasi-768x439.jpg" alt="Lab Komputer Sekolah Palembang Harapan" class="absolute inset-0 h-full w-full object-cover object-center opacity-40" />
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/60 to-black/90"></div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-md rounded-2xl border border-white/10 bg-white/10 backdrop-blur-lg p-8 shadow-2xl">
      <div class="text-center mb-8">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full border border-white/20 bg-white/10 backdrop-blur-sm">
          <span class="text-lg font-semibold tracking-wide">SPH</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Sistem Peminjaman Ruangan</h1>
        <p class="mt-1 text-sm text-gray-300">Sekolah Palembang Harapan</p>
      </div>

      @if (session('status'))
        <div class="mb-4 rounded-md bg-white/10 p-3 text-sm">{{ session('status') }}</div>
      @endif

      <!-- Role selector removed: authentication will detect role from email in DB -->

      <!-- Form Login (email-only, tanpa "ingat saya") -->
      <form id="login-form" action="{{ route('login') }}" method="POST" class="space-y-5" novalidate>
        @csrf

        <!-- Email -->
        <div>
     <label for="email" id="email-label" class="block text-sm font-medium text-gray-200">Email</label>
     <input id="email" name="email" type="email" inputmode="email" autocomplete="email" required
       value="{{ old('email') }}"
       placeholder="nama@sekolah.sch.id"
       class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('email') ? 'border-red-400' : 'border-white/20' }}">
          @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <!-- Password dengan toggle -->
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-200">Kata Sandi</label>
            <button type="button" id="togglePassword" class="text-xs text-gray-400 hover:text-white underline underline-offset-4">Tampilkan</button>
          </div>
          <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"
                 class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('password') ? 'border-red-400' : 'border-white/20' }}">
          @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <!-- (Hapus) Ingat saya -->
        <!-- Section ini dihapus sesuai permintaan -->

        <!-- Forgot only -->
        <div class="flex justify-end text-sm">
          <a href="{{ url('/password/reset') }}" class="text-gray-400 hover:text-white underline underline-offset-4">Lupa kata sandi?</a>
        </div>

        <!-- Submit (teks dinamis) -->
  <button type="submit" id="submit-btn"
    class="w-full rounded-xl border border-white/30 bg-white/10 px-4 py-2 font-medium text-white transition hover:bg-white/20 focus:ring-4 focus:ring-white/40">
    Masuk
  </button>

        <p class="text-center text-sm text-gray-400 mt-4">
          Belum punya akun? <a href="{{ route('register') }}" class="underline hover:text-white">Daftar</a>
        </p>
      </form>

      <p class="mt-8 text-center text-xs text-gray-500">© {{ date('Y') }} Sekolah Palembang Harapan — Lab Komputer Theme</p>
    </div>
  </main>

  <script>
    // Toggle password visibility
    (function(){
      const toggle = document.getElementById('togglePassword');
      const pwd = document.getElementById('password');
      if(toggle && pwd){ toggle.addEventListener('click', function(){ const h = pwd.type === 'password'; pwd.type = h ? 'text' : 'password'; toggle.textContent = h ? 'Sembunyikan' : 'Tampilkan'; }); }
    })();

    // Role selector -> update hidden input + email label/placeholder + button styles + submit text
    (function(){
      const roleInput = document.getElementById('role');
      const email = document.getElementById('email');
      const emailLabel = document.getElementById('email-label');
      const roleButtons = document.querySelectorAll('.role-btn');
      const submitBtn = document.getElementById('submit-btn');

      function applyRole(r){
        roleInput.value = r;
        if(r === 'admin'){ emailLabel.textContent = 'Email Admin'; email.placeholder = 'admin@sekolah.sch.id'; }
        else { emailLabel.textContent = 'Email'; email.placeholder = 'nama@sekolah.sch.id'; }
        submitBtn.textContent = 'Masuk sebagai ' + r;

        roleButtons.forEach(btn => {
          const isActive = btn.getAttribute('data-role') === r;
          btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          btn.className = 'role-btn rounded-xl px-3 py-2 text-sm transition border ' + (isActive
            ? 'bg-white/20 border-white/40 text-white'
            : 'bg-white/5 border-white/10 text-gray-300 hover:bg-white/10');
        });
      }

      roleButtons.forEach(btn => btn.addEventListener('click', function(){ applyRole(this.getAttribute('data-role')); }));
      applyRole(roleInput.value || 'siswa');
    })();
  </script>
</body>
</html>