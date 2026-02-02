<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Statistiques chambres
        $totalRooms = Room::count();
        $availableRooms = Room::available()->count();
        $occupiedRooms = Room::occupied()->count();
        $maintenanceRooms = Room::maintenance()->count();

        // Statistiques réservations
        $totalReservations = Reservation::count();
        $checkInsToday = Reservation::checkInToday()->count();
        $checkOutsToday = Reservation::checkOutToday()->count();
        $pendingReservations = Reservation::pending()->count();
        $confirmedReservations = Reservation::confirmed()->count();

        // Réservations récentes
        $recentReservations = Reservation::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Chambres par type
        $roomsByType = Room::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        return view('pages.dashboard.index', [
            'title' => 'Dashboard',
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'totalReservations' => $totalReservations,
            'checkInsToday' => $checkInsToday,
            'checkOutsToday' => $checkOutsToday,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'recentReservations' => $recentReservations,
            'roomsByType' => $roomsByType,
        ]);
    }
}
