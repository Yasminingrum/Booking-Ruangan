<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();

            $unreadCount = 0;

            if ($user) {
                $unreadCount = $user->notifications()
                    ->where('is_read', false)
                    ->count();
            }

            $view->with('headerUnreadNotifications', $unreadCount);
        });
    }
}
