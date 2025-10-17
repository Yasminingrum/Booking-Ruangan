# Aplikasi Peminjaman Ruangan - Sekolah Palembang Harapan

Aplikasi web untuk mengelola peminjaman ruangan sekolah secara digital, menggantikan sistem manual menggunakan Excel.

## 🎯 Fitur Utama

- **Peminjam**: Cari ketersediaan ruangan, ajukan peminjaman, cek status
- **Admin**: Kelola pengajuan, data ruangan, dan pengguna
- **Kepala Sekolah**: Lihat laporan dan statistik penggunaan ruangan

## 🛠️ Teknologi

- **Backend**: Laravel 12
- **Frontend**: Blade Template / React.js
- **Database**: MySQL / MariaDB
- **Web Server**: Nginx / Apache

## 📋 Prasyarat

- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Node.js & NPM (jika pakai React)

## 🚀 Cara Menjalankan

### 1. Clone Repository
```bash
git clone https://github.com/Yasminingrum/Booking-Ruangan.git
cd booking-ruangan
```

### 2. Install Dependencies
```bash
composer install
npm install  # jika pakai React
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` untuk konfigurasi database:
```env
DB_DATABASE=booking-ruangan
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database
```bash
php artisan migrate
php artisan db:seed  # untuk data sample
```

### 5. Jalankan Aplikasi
```bash
# Development
php artisan serve

# Jika pakai React
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

## 👤 Akun Default

**Admin**
- Email: `admin@palembangharapan.sch.id`
- Password: `password`

**Peminjam**
- Email: `budi.santoso@palembangharapan.sch.id`
- Password: `password`

## 📁 Struktur Folder Penting

```
app/
├── Http/Controllers/  # Logic controller
├── Models/           # Database models
├── Services/         # Business logic
database/
├── migrations/       # Database schema
├── seeders/         # Data sample
routes/
├── web.php          # Web routes
├── api.php          # API routes
```

## 📚 Dokumentasi Lengkap

Lihat folder `docs/` untuk dokumentasi detail:
- Models Documentation
- Middleware Documentation
- Request Classes Documentation
- Database Schema (SQL)

## 🐛 Troubleshooting

### Error: "SQLSTATE[HY000] [1049] Unknown database"
```bash
# Buat database terlebih dahulu
mysql -u root -p
CREATE DATABASE `booking-ruangan`;
exit;
```

### Error: "Class 'XXX' not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### Port 8000 sudah digunakan
```bash
php artisan serve --port=8001
```

## 👥 Tim Pengembang

- Andi Pandapotan Purba – 0706012324024
- Refaliano Juan – 0706012324020
- Titi Dwiayu Yasminingrum – 0706012324025

Program Studi Informatika  
Universitas Ciputra Surabaya - 2025

## 📄 Lisensi

Dokumen ini dibuat untuk keperluan akademis.
