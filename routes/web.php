<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
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

// Admin dashboard - protected by role middleware alias 'role'
Route::get('/admin', function () {
    // Basic stats for dashboard view
    $totalUsers = User::count();
    $totalRuangan = Room::count();
    $totalPeminjaman = Booking::count();
    $recentBookings = Booking::with(['user', 'room'])->latest()->take(8)->get();

    return view('admin.dashboard', compact('totalUsers', 'totalRuangan', 'totalPeminjaman', 'recentBookings'));
})->name('admin.dashboard')->middleware('role:admin');


Route::get('/dashboard', function () {
    if (view()->exists('dashboard')) {
        return view('dashboard');
    }
    // Fallback to welcome view if dashboard view is missing
    return view('welcome');
})->middleware(['auth'])->name('dashboard');