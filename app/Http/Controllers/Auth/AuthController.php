<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Hubungi admin.'],
            ]);
        }

        // Create token for API
        $token = $user->createToken('auth_token')->plainTextToken;

        // Login for web session
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'peminjam', // Default role
            'is_active' => true,
        ]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Login
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        // Logout from web session
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ], 200);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|min:3|max:255',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20',
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Check current password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Password saat ini tidak cocok.'],
                ]);
            }

            $user->password = Hash::make($request->password);
        }

        // Update other fields
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ], 200);
    }
}
