<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        
        // Statistiques pour le guest
        $stats = [
            'room_number' => $user->room_number ?? 'N/A',
            'department' => $user->department ?? 'Guest',
        ];

        return view('pages.guest.dashboard', [
            'title' => 'Bienvenue',
            'stats' => $stats,
        ]);
    }
}
