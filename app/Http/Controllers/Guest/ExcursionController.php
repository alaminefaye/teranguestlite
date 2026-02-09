<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Excursion;
use App\Models\ExcursionBooking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExcursionController extends Controller
{
    public function index(): View
    {
        $excursions = Excursion::where('enterprise_id', auth()->user()->enterprise_id)
            ->available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('display_order', 'asc')
            ->get();

        return view('pages.guest.excursions.index', [
            'title' => 'Excursions',
            'excursions' => $excursions,
        ]);
    }

    public function show(Excursion $excursion): View
    {
        return view('pages.guest.excursions.show', [
            'title' => $excursion->name,
            'excursion' => $excursion,
        ]);
    }

    public function book(Request $request, Excursion $excursion): RedirectResponse
    {
        $validated = $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        $adults = $validated['number_of_adults'];
        $children = $validated['number_of_children'] ?? 0;
        $totalPrice = ($adults * $excursion->price_adult) + ($children * ($excursion->price_child ?? 0));

        ExcursionBooking::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'excursion_id' => $excursion->id,
            'room_id' => $room->id ?? null,
            'booking_date' => $validated['booking_date'],
            'number_of_adults' => $adults,
            'number_of_children' => $children,
            'total_price' => $totalPrice,
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.excursions.my-bookings')
            ->with('success', 'Votre réservation a été enregistrée avec succès !');
    }

    public function myBookings(): View
    {
        $bookings = ExcursionBooking::where('user_id', auth()->id())
            ->with(['excursion'])
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        return view('pages.guest.excursions.my-bookings', [
            'title' => 'Mes Réservations d\'Excursions',
            'bookings' => $bookings,
        ]);
    }
}
