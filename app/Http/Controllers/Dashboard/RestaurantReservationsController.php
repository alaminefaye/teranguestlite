<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RestaurantReservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantReservationsController extends Controller
{
    public function index(Request $request): View
    {
        $query = RestaurantReservation::with(['restaurant', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('restaurant', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc')->paginate(15);

        $stats = [
            'total' => RestaurantReservation::count(),
            'confirmed' => RestaurantReservation::where('status', 'confirmed')->count(),
            'today' => RestaurantReservation::whereDate('reservation_date', today())->count(),
        ];

        return view('pages.dashboard.restaurant-reservations.index', compact('reservations', 'stats'));
    }
}
