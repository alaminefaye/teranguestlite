<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantReservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
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

    public function show(RestaurantReservation $restaurantReservation): View
    {
        $restaurantReservation->load(['restaurant', 'user', 'room']);
        return view('pages.dashboard.restaurant-reservations.show', ['reservation' => $restaurantReservation]);
    }

    public function edit(RestaurantReservation $restaurantReservation): View
    {
        $restaurantReservation->load(['restaurant', 'user', 'room']);
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);
        return view('pages.dashboard.restaurant-reservations.edit', ['reservation' => $restaurantReservation, 'restaurants' => $restaurants, 'rooms' => $rooms]);
    }

    public function update(Request $request, RestaurantReservation $restaurantReservation): RedirectResponse
    {
        if ($restaurantReservation->status === 'cancelled') {
            return redirect()->route('dashboard.restaurant-reservations.show', $restaurantReservation)->with('error', 'Une réservation annulée ne peut pas être modifiée.');
        }
        $validated = $request->validate([
            'reservation_date' => 'required|date',
            'reservation_time' => 'nullable|string|max:10',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:500',
            'status' => 'required|in:pending,confirmed',
        ]);
        $restaurantReservation->update([
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'] ?? $restaurantReservation->reservation_time,
            'number_of_guests' => $validated['number_of_guests'],
            'special_requests' => $validated['special_requests'],
            'status' => $validated['status'],
        ]);
        return redirect()->route('dashboard.restaurant-reservations.show', $restaurantReservation)->with('success', 'Réservation mise à jour.');
    }

    public function cancel(Request $request, RestaurantReservation $restaurantReservation): RedirectResponse
    {
        if ($restaurantReservation->status === 'cancelled') {
            return redirect()->back()->with('info', 'Cette réservation est déjà annulée.');
        }
        $restaurantReservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('cancellation_reason'),
        ]);
        return redirect()->route('dashboard.restaurant-reservations.index')->with('success', 'Réservation annulée.');
    }
}
