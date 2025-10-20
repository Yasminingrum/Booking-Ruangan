<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * Display a listing of Staff (admin, kepala_sekolah, cleaning_service).
     */
    public function indexStaff()
    {
        // ✅ Role yang valid sesuai database
        $staff = User::whereIn('role', ['admin', 'kepala_sekolah', 'cleaning_service'])
            ->orderBy('name')
            ->paginate(10);

        $totalStaff = User::whereIn('role', ['admin', 'kepala_sekolah', 'cleaning_service'])->count();
        $activeStaff = User::whereIn('role', ['admin', 'kepala_sekolah', 'cleaning_service'])
            ->where('is_active', true)
            ->count();

        return view('admin.users.staff', compact('staff', 'totalStaff', 'activeStaff'));
    }

    /**
     * Display a listing of peminjam (users who can borrow rooms).
     */
    public function index()
    {
        $borrowers = User::where('role', 'peminjam')
            ->orderBy('name')
            ->paginate(10);

        $totalBorrowers = User::where('role', 'peminjam')->count();
        $activeBorrowers = User::where('role', 'peminjam')
            ->where('is_active', true)
            ->count();

        return view('admin.users.borrowers', compact('borrowers', 'totalBorrowers', 'activeBorrowers'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create($role = null)
    {
        $allowedRoles = ['admin', 'kepala_sekolah', 'cleaning_service', 'peminjam'];

        if (!in_array($role, $allowedRoles)) {
            $role = null;
        }

        return view('admin.users.create', compact('role'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,kepala_sekolah,cleaning_service,peminjam',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.create', $request->role)
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->is_active,
        ]);

        $redirectRoute = in_array($request->role, ['admin', 'kepala_sekolah', 'cleaning_service'])
            ? 'admin.users.staff'
            : 'admin.users.borrowers';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,kepala_sekolah,cleaning_service,peminjam',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // ✅ PERBAIKAN: Redirect logic yang benar
        $redirectRoute = in_array($request->role, ['admin', 'kepala_sekolah', 'cleaning_service'])
            ? 'admin.users.staff'
            : 'admin.users.borrowers';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting users with upcoming bookings
        $hasUpcomingBookings = $user->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->where('booking_date', '>=', now()->toDateString())
            ->exists();

        if ($hasUpcomingBookings) {
            return back()->with('error', 'User tidak dapat dihapus karena masih memiliki peminjaman aktif.');
        }

        $role = $user->role;
        $user->delete();

        // ✅ PERBAIKAN: Redirect logic yang benar
        $redirectRoute = in_array($role, ['admin', 'kepala_sekolah', 'cleaning_service'])
            ? 'admin.users.staff'
            : 'admin.users.borrowers';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'User berhasil dihapus');
    }
}
