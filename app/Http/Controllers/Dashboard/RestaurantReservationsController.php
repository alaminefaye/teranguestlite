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
        $query = RestaurantReservation::with(['restaurant', 'user', 'guest', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('restaurant', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('room', fn ($q2) => $q2->where('room_number', 'like', '%' . $search . '%'));
            });
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

    public function show(RestaurantReservation $restaurantReservation): View
    {
        $restaurantReservation->load(['restaurant', 'user', 'guest', 'room']);
        return view('pages.dashboard.restaurant-reservations.show', ['reservation' => $restaurantReservation]);
    }
}
