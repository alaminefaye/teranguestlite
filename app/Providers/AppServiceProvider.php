<?php

namespace App\Providers;

use App\Models\HotelMessage;
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
            $unreadCount = 0;

            if (Auth::check()) {
                $user = Auth::user();
                $isAdmin = $user->role === 'admin';
                $isStaff = $user->role === 'staff';

                if (($isAdmin || $isStaff) && $user->enterprise_id) {
                    $enterpriseId = $user->enterprise_id;

                    $unreadCount = HotelMessage::whereNull('read_at')
                        ->where('sender_type', 'guest')
                        ->whereHas('conversation', function ($query) use ($enterpriseId) {
                            $query->where('enterprise_id', $enterpriseId);
                        })
                        ->count();
                }
            }

            $view->with('unreadChatCount', $unreadCount);
        });
    }
}
