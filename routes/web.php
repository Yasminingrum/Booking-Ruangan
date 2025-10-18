<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminSettingController;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;

Route::get('/', function () {
    return view('welcome');
});

// Show login form (kept as closure to preserve role selector query)
Route::get('/login', function () {
    $currentRole = request('role', 'user'); // Default role is 'user' if not specified
    return view('auth.login', compact('currentRole'));
})->name('login');

// Handle login POST
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

// Registration (show + submit)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Logout
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Admin routes - protected by role middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin dashboard
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Bookings management
    Route::get('/admin/bookings/pending', [AdminController::class, 'pending'])->name('admin.bookings.pending');
    Route::post('/admin/bookings/{booking}/approve', [AdminController::class, 'approve'])->name('admin.bookings.approve');
    Route::post('/admin/bookings/{booking}/reject', [AdminController::class, 'reject'])->name('admin.bookings.reject');

    // RUANGAN (CRUD)
    Route::resource('admin/rooms', AdminRoomController::class)->names('admin.rooms');

    // GURU & SISWA: pakai User dengan filter role
    Route::get('/admin/users/teachers', [AdminUserController::class, 'indexTeachers'])->name('admin.users.teachers');
    Route::get('/admin/users/students', [AdminUserController::class, 'indexStudents'])->name('admin.users.students');
    Route::get('/admin/users/create/{role}', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users/store/{role}', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // PENGATURAN
    Route::get('/admin/settings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/settings/clear-cache', [AdminSettingController::class, 'clearCache'])->name('admin.settings.clear-cache');
});