<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantReservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RestaurantController extends Controller
{
    public function index(): View
    {
        $restaurants = Restaurant::where('enterprise_id', auth()->user()->enterprise_id)
            ->active()
            ->ordered()
            ->get();

        return view('pages.guest.restaurants.index', [
            'title' => 'Restaurants & Bars',
            'restaurants' => $restaurants,
        ]);
    }

    public function show(Restaurant $restaurant): View
    {
        if (! $restaurant->is_active) {
            abort(404);
        }

        return view('pages.guest.restaurants.show', [
            'title' => $restaurant->name,
            'restaurant' => $restaurant,
        ]);
    }

    public function reserve(Request $request, Restaurant $restaurant): RedirectResponse
    {
        if (! $restaurant->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'number_of_guests' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        RestaurantReservation::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'room_id' => $room->id ?? null,
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'number_of_guests' => $validated['number_of_guests'],
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.restaurants.my-reservations')
            ->with('success', 'Votre réservation a été enregistrée avec succès !');
    }

    public function myReservations(): View
    {
        $reservations = RestaurantReservation::where('user_id', auth()->id())
            ->with(['restaurant'])
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->paginate(10);

        return view('pages.guest.restaurants.my-reservations', [
            'title' => 'Mes Réservations',
            'reservations' => $reservations,
        ]);
    }
}
