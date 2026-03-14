<?php

namespace App\Providers;

use App\Models\HotelMessage;
use App\Models\Notification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->alias('QrCode', \SimpleSoftwareIO\QrCode\Facades\QrCode::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limitation des tentatives de connexion API (brute-force)
        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                return response()->json(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans une minute.'], 429);
            });
        });
        // Limitation des appels tablette (sans auth) par IP
        RateLimiter::for('api-tablet', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())->response(function () {
                return response()->json(['success' => false, 'message' => 'Trop de requêtes. Réessayez plus tard.'], 429);
            });
        });


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

                // Notifications réelles pour le header : uniquement celles de l'entreprise connectée (ou sans entreprise pour compat)
                $notificationQuery = Notification::forUser($user->id);
                if ($user->enterprise_id) {
                    $notificationQuery->where(function ($q) use ($user) {
                        $q->where('enterprise_id', $user->enterprise_id)->orWhereNull('enterprise_id');
                    });
                }
                $headerUnreadNotificationCount = (clone $notificationQuery)->unread()->count();
                $headerNotifications = $notificationQuery
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
