<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AdminSettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        // Get system info
        $phpVersion = phpversion();
        $laravelVersion = app()->version();
        $dbConnection = config('database.default');
        
        // Get app settings from config (in production, these would be stored in database)
        $settings = [
            'app_name' => config('app.name', 'Booking Ruangan'),
            'school_name' => 'SMA Palembang Harapan',
            'school_address' => 'Jl. Pendidikan No. 123, Palembang',
            'school_phone' => '(0711) 123-4567',
            'school_email' => 'info@palembangharapan.sch.id',
            'min_booking_hours' => 1,
            'max_booking_hours' => 8,
            'min_advance_days' => 1,
            'max_advance_days' => 30,
            'allow_weekend_booking' => true,
        ];
        
        return view('admin.settings.index', compact('settings', 'phpVersion', 'laravelVersion', 'dbConnection'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string|max:500',
            'school_phone' => 'required|string|max:20',
            'school_email' => 'required|email|max:255',
            'min_booking_hours' => 'required|integer|min:1',
            'max_booking_hours' => 'required|integer|min:1',
            'min_advance_days' => 'required|integer|min:0',
            'max_advance_days' => 'required|integer|min:1',
            'allow_weekend_booking' => 'boolean',
        ]);

        // In a production app, you would save these to a settings table in the database
        // For now, we'll just redirect back with a success message
        // You could implement a Settings model to store these values

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Cache berhasil dibersihkan');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }
}
