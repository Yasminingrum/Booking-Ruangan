<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Handle user logout
     * Works for both API and Web
     */
    public function logout(Request $request)
    {
        $userName = Auth::user()->name ?? 'User';

        // For API request - Revoke token
        if ($request->expectsJson()) {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil. Sampai jumpa, ' . $userName,
            ], 200);
        }

        // For web request - Logout from session
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah logout. Sampai jumpa, ' . $userName);
    }

    /**
     * Logout from all devices (Revoke all tokens)
     * API only
     */
    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dari semua perangkat',
        ], 200);
    }

    /**
     * Logout from specific device (Revoke specific token)
     * API only
     */
    public function logoutDevice(Request $request, $tokenId)
    {
        $token = $request->user()
            ->tokens()
            ->where('id', $tokenId)
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dari perangkat tersebut',
        ], 200);
    }

    /**
     * Get all active sessions/tokens
     * API only
     */
    public function sessions(Request $request)
    {
        $tokens = $request->user()
            ->tokens()
            ->select('id', 'name', 'created_at', 'last_used_at')
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($token) use ($request) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at->format('d M Y H:i'),
                    'last_used_at' => $token->last_used_at
                        ? $token->last_used_at->format('d M Y H:i')
                        : 'Never',
                    'is_current' => $token->id === $request->user()->currentAccessToken()->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ], 200);
    }

    /**
     * Check if user is logged in
     * For API
     */
    public function check()
    {
        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'authenticated' => false,
        ], 401);
    }
}
