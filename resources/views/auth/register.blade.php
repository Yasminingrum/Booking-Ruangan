<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Sistem Peminjaman Ruangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <style> body{ font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full">
  <main class="relative min-h-screen flex items-center justify-center overflow-hidden bg-black text-white">
    <img src="https://proditp.unismuh.ac.id/wp-content/uploads/2023/01/Lab-Komputer-SMP-Negeri-12-Binjai-Gambar-Ilustrasi-768x439.jpg" alt="Lab Komputer Sekolah Palembang Harapan" class="absolute inset-0 h-full w-full object-cover object-center opacity-40" />
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/60 to-black/90"></div>

    <div class="relative z-10 w-full max-w-md rounded-2xl border border-white/10 bg-white/10 backdrop-blur-lg p-8 shadow-2xl">
      <div class="text-center mb-8">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full border border-white/20 bg-white/10 backdrop-blur-sm">
          <span class="text-lg font-semibold tracking-wide">SPH</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Buat Akun Baru</h1>
        <p class="mt-1 text-sm text-gray-300">Sekolah Palembang Harapan</p>
      </div>

      @if (session('status'))
        <div class="mb-4 rounded-md bg-white/10 p-3 text-sm">{{ session('status') }}</div>
      @endif

      @php($currentRole = in_array(old('role'), ['guru','siswa']) ? old('role') : 'siswa')
      <div class="mb-6" role="group" aria-labelledby="role-label">
        <p id="role-label" class="mb-2 text-sm text-gray-300">Daftar sebagai</p>
        <div class="grid grid-cols-2 gap-2">
          @foreach (['guru' => 'Guru', 'siswa' => 'Siswa'] as $key => $label)
            <button type="button" data-role="{{ $key }}" aria-pressed="{{ $currentRole === $key ? 'true' : 'false' }}"
              class="role-btn rounded-xl px-3 py-2 text-sm transition border {{ $currentRole === $key ? 'bg-white/20 border-white/40 text-white' : 'bg-white/5 border-white/10 text-gray-300 hover:bg-white/10' }}">
              {{ $label }}
            </button>
          @endforeach
        </div>
      </div>

      <form id="register-form" action="{{ route('register') }}" method="POST" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" id="role" name="role" value="{{ $currentRole }}">

        <div>
          <label for="name" class="block text-sm font-medium text-gray-200">Nama Lengkap</label>
          <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" placeholder="Nama lengkap" class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('name') ? 'border-red-400' : 'border-white/20' }}">
          @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-200">Alamat Email</label>
          <input id="email" name="email" type="email" inputmode="email" autocomplete="email" required value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('email') ? 'border-red-400' : 'border-white/20' }}">
          @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-200">Kata Sandi</label>
            <button type="button" id="togglePassword" class="text-xs text-gray-400 hover:text-white underline underline-offset-4">Tampilkan</button>
          </div>
          <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="••••••••" class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('password') ? 'border-red-400' : 'border-white/20' }}">
          @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-200">Konfirmasi Kata Sandi</label>
            <button type="button" id="toggleConfirm" class="text-xs text-gray-400 hover:text-white underline underline-offset-4">Tampilkan</button>
          </div>
          <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="••••••••" class="mt-1 w-full rounded-xl border bg-black/30 px-3 py-2 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/70 {{ $errors->has('password_confirmation') ? 'border-red-400' : 'border-white/20' }}">
          @error('password_confirmation')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <button type="submit" id="submit-btn" class="w-full rounded-xl border border-white/30 bg-white/10 px-4 py-2 font-medium text-white transition hover:bg-white/20 focus:ring-4 focus:ring-white/40">Daftar sebagai {{ $currentRole }}</button>

        <p class="text-center text-sm text-gray-400 mt-4">Sudah punya akun? <a href="{{ route('login') }}" class="underline hover:text-white">Masuk</a></p>
      </form>

      <p class="mt-8 text-center text-xs text-gray-500">© {{ date('Y') }} Sekolah Palembang Harapan — Lab Komputer Theme</p>
    </div>
  </main>

  <script>
    (function(){
      const t=document.getElementById('togglePassword');
      const p=document.getElementById('password');
      if(t&&p){t.addEventListener('click',()=>{const h=p.type==='password';p.type=h?'text':'password';t.textContent=h?'Sembunyikan':'Tampilkan';});}
      const tc=document.getElementById('toggleConfirm');
      const pc=document.getElementById('password_confirmation');
      if(tc&&pc){tc.addEventListener('click',()=>{const h=pc.type==='password';pc.type=h?'text':'password';tc.textContent=h?'Sembunyikan':'Tampilkan';});}
    })();

    (function(){
      const roleInput=document.getElementById('role');
      const submitBtn=document.getElementById('submit-btn');
      const roleButtons=document.querySelectorAll('.role-btn');
      function applyRole(r){
        roleInput.value=r; submitBtn.textContent='Daftar sebagai '+r;
        roleButtons.forEach(btn=>{const active=btn.getAttribute('data-role')===r; btn.setAttribute('aria-pressed', active?'true':'false'); btn.className='role-btn rounded-xl px-3 py-2 text-sm transition border '+(active?'bg-white/20 border-white/40 text-white':'bg-white/5 border-white/10 text-gray-300 hover:bg-white/10');});
      }
      roleButtons.forEach(btn=>btn.addEventListener('click',()=>applyRole(btn.getAttribute('data-role'))));
      applyRole(roleInput.value||'siswa');
    })();
  </script>
</body>
</html>