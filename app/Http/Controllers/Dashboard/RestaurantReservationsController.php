<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantReservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantReservationsController extends Controller
{
    public function index(Request $request): View
    {
        $query = RestaurantReservation::with(['restaurant', 'user', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('reservation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reservation_date', '<=', $request->date_to);
        }
        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('restaurant', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                    ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc')->paginate(15);

        $stats = [
            'total' => RestaurantReservation::count(),
            'confirmed' => RestaurantReservation::where('status', 'confirmed')->count(),
            'today' => RestaurantReservation::whereDate('reservation_date', today())->count(),
        ];

        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return view('pages.dashboard.restaurant-reservations.index', compact('reservations', 'stats', 'restaurants', 'rooms'));
    }
}
