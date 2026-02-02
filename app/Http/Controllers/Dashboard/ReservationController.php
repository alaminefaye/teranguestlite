<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['room', 'user']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reservation_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $reservations = $query->orderBy('check_in', 'desc')->paginate(10);

        // Statistiques
        $stats = [
            'total' => Reservation::count(),
            'pending' => Reservation::pending()->count(),
            'confirmed' => Reservation::confirmed()->count(),
            'active' => Reservation::active()->count(),
            'today_checkins' => Reservation::checkInToday()->count(),
            'today_checkouts' => Reservation::checkOutToday()->count(),
        ];

        // Pour les filtres
        $rooms = Room::orderBy('room_number')->get();

        return view('pages.dashboard.reservations.index', [
            'title' => 'Réservations',
            'reservations' => $reservations,
            'stats' => $stats,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rooms = Room::available()->orderBy('room_number')->get();
        $guests = User::guests()->orderBy('name')->get();

        return view('pages.dashboard.reservations.create', [
            'title' => 'Créer une réservation',
            'rooms' => $rooms,
            'guests' => $guests,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:10',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed',
        ]);

        // Ajouter enterprise_id automatiquement
        $validated['enterprise_id'] = auth()->user()->enterprise_id;

        // Vérifier la disponibilité de la chambre
        $room = Room::findOrFail($validated['room_id']);
        if (!$room->isAvailableForPeriod($validated['check_in'], $validated['check_out'])) {
            return back()
                ->withInput()
                ->with('error', 'Cette chambre n\'est pas disponible pour la période sélectionnée.');
        }

        // Calculer le prix total
        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $validated['total_price'] = $room->price_per_night * $nights;

        // Créer la réservation
        $reservation = Reservation::create($validated);

        // Mettre à jour le statut de la chambre si confirmée
        if ($validated['status'] === 'confirmed') {
            $room->update(['status' => 'reserved']);
        }

        return redirect()->route('dashboard.reservations.index')
            ->with('success', 'Réservation créée avec succès ! Numéro: ' . $reservation->reservation_number);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['room', 'user', 'enterprise']);

        return view('pages.dashboard.reservations.show', [
            'title' => 'Réservation ' . $reservation->reservation_number,
            'reservation' => $reservation,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $rooms = Room::orderBy('room_number')->get();
        $guests = User::guests()->orderBy('name')->get();

        return view('pages.dashboard.reservations.edit', [
            'title' => 'Modifier réservation ' . $reservation->reservation_number,
            'reservation' => $reservation,
            'rooms' => $rooms,
            'guests' => $guests,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:10',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        // Si la chambre a changé, vérifier la disponibilité
        if ($validated['room_id'] != $reservation->room_id) {
            $room = Room::findOrFail($validated['room_id']);
            if (!$room->isAvailableForPeriod($validated['check_in'], $validated['check_out'])) {
                return back()
                    ->withInput()
                    ->with('error', 'Cette chambre n\'est pas disponible pour la période sélectionnée.');
            }
        }

        // Recalculer le prix si les dates ont changé
        $room = Room::findOrFail($validated['room_id']);
        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $validated['total_price'] = $room->price_per_night * $nights;

        $reservation->update($validated);

        return redirect()->route('dashboard.reservations.show', $reservation)
            ->with('success', 'Réservation mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('dashboard.reservations.index')
            ->with('success', 'Réservation supprimée avec succès !');
    }

    /**
     * Check-in action
     */
    public function checkIn(Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'Seules les réservations confirmées peuvent être check-in.');
        }

        $reservation->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        // Mettre à jour le statut de la chambre
        $reservation->room->update(['status' => 'occupied']);

        return back()->with('success', 'Check-in effectué avec succès !');
    }

    /**
     * Check-out action
     */
    public function checkOut(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Seules les réservations avec check-in peuvent être check-out.');
        }

        $reservation->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        // Mettre à jour le statut de la chambre
        $reservation->room->update(['status' => 'available']);

        return back()->with('success', 'Check-out effectué avec succès !');
    }

    /**
     * Cancel action
     */
    public function cancel(Reservation $reservation)
    {
        if ($reservation->status === 'checked_out') {
            return back()->with('error', 'Impossible d\'annuler une réservation déjà terminée.');
        }

        $reservation->update(['status' => 'cancelled']);

        // Mettre à jour le statut de la chambre si elle était réservée
        if ($reservation->room->status === 'reserved') {
            $reservation->room->update(['status' => 'available']);
        }

        return back()->with('success', 'Réservation annulée avec succès !');
    }
}
