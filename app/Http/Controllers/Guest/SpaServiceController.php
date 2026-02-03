<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\SpaService;
use App\Models\SpaReservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SpaServiceController extends Controller
{
    public function index(): View
    {
        $services = SpaService::where('enterprise_id', auth()->user()->enterprise_id)
            ->available()
            ->ordered()
            ->get()
            ->groupBy('category');

        return view('pages.guest.spa.index', [
            'title' => 'Spa & Bien-être',
            'services' => $services,
        ]);
    }

    public function show(SpaService $spaService): View
    {
        return view('pages.guest.spa.show', [
            'title' => $spaService->name,
            'service' => $spaService,
        ]);
    }

    public function reserve(Request $request, SpaService $spaService): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        SpaReservation::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'spa_service_id' => $spaService->id,
            'room_id' => $room->id ?? null,
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'special_requests' => $validated['special_requests'] ?? null,
            'price' => $spaService->price,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.spa.my-reservations')
            ->with('success', 'Votre réservation spa a été enregistrée avec succès !');
    }

    public function myReservations(): View
    {
        $reservations = SpaReservation::where('user_id', auth()->id())
            ->with(['spaService'])
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->paginate(10);

        return view('pages.guest.spa.my-reservations', [
            'title' => 'Mes Réservations Spa',
            'reservations' => $reservations,
        ]);
    }
}
