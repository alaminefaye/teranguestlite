<?php

namespace App\Providers;

use App\Models\HotelMessage;
use App\Models\Notification;
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
            $headerNotifications = [];
            $headerUnreadNotificationCount = 0;

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

                // Notifications réelles pour le header (dernières 20, non lues en premier)
                $headerUnreadNotificationCount = Notification::forUser($user->id)->unread()->count();
                $headerNotifications = Notification::forUser($user->id)
                    ->orderByRaw('is_read ASC, created_at DESC')
                    ->limit(20)
                    ->get();
            }

            $view->with('unreadChatCount', $unreadCount);
            $view->with('headerNotifications', $headerNotifications);
            $view->with('headerUnreadNotificationCount', $headerUnreadNotificationCount);
        });
    }
}
