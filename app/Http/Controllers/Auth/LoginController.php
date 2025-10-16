<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     * For web interface
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user login
     * Works for both API and Web
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
        ]);

        // Check rate limiting (prevent brute force)
        $this->checkTooManyFailedAttempts($request);

        // Find user
        $user = User::where('email', strtolower(trim($request->email)))->first();

        // Verify credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment failed attempts
            RateLimiter::hit($this->throttleKey($request), 300); // 5 minutes

            // For API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah.',
                ], 401);
            }

            // For web request
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            // For API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ], 403);
            }

            // For web request
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
            ]);
        }

        // role selector removed: login will be based on email/password and DB role

        // Clear rate limiting on successful login
        RateLimiter::clear($this->throttleKey($request));

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Update last login (optional)
        $user->update(['last_login_at' => now()]);

        // For API request
        if ($request->expectsJson()) {
            // Create token for API
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang, ' . $user->name,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        }

        // For web request - redirect based on role
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole(User $user)
    {
        $message = 'Selamat datang, ' . $user->name;

        return match($user->role) {
            User::ROLE_ADMIN => redirect()
                ->route('admin.dashboard')
                ->with('success', $message),

            User::ROLE_KEPALA_SEKOLAH => redirect()
                ->route('reports.index')
                ->with('success', $message),

            User::ROLE_CLEANING_SERVICE => redirect()
                ->route('dashboard')
                ->with('success', $message),

            default => redirect()
                ->route('dashboard')
                ->with('success', $message),
        };
    }

    /**
     * Check for too many failed login attempts
     */
    protected function checkTooManyFailedAttempts(Request $request)
    {
        $key = $this->throttleKey($request);
        $maxAttempts = 5;
        $decayMinutes = 5;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            // For API request
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'email' => ["Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit."],
                ]);
            }

            // For web request
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit."],
            ]);
        }
    }

    /**
     * Get the rate limiting throttle key
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }

    /**
     * Refresh authentication token (API only)
     */
    public function refresh(Request $request)
    {
        // Delete old token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }
}
