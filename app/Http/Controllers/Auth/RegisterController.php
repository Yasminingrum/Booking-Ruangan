<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     * For web interface
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     * Works for both API and Web
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20',
        ], [
            'name.required' => 'Nama lengkap harus diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
        ]);

        // Check validation
        if ($validator->fails()) {
            // For API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // For web request
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Create user
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => User::ROLE_PEMINJAM, // Default role
                'is_active' => true,
            ]);

            // Login user automatically after registration
            Auth::login($user);

            // For API request
            if ($request->expectsJson()) {
                // Create token for API
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil! Selamat datang, ' . $user->name,
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
                ], 201);
            }

            // For web request
            return redirect()->route('dashboard')
                ->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name);

        } catch (\Exception $e) {
            // For API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registrasi gagal. Silakan coba lagi.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // For web request
            return redirect()->back()
                ->with('error', 'Registrasi gagal. Silakan coba lagi.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Register as specific role (Admin only)
     * Only admin can create users with specific roles
     */
    public function registerWithRole(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can create users with specific roles.',
            ], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20',
            'role' => 'required|in:peminjam,admin,kepala_sekolah,cleaning_service',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat dengan role: ' . $request->role,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if email is available
     * Helper endpoint for live validation
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required',
            ], 422);
        }

        $exists = User::where('email', strtolower(trim($email)))->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists
                ? 'Email sudah digunakan'
                : 'Email tersedia',
        ], 200);
    }
}
