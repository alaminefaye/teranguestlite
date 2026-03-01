<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\ActivityLogger;
use App\Models\ReservationSettlement;
use App\Models\Room;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['room', 'user', 'guest']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }
        if ($request->filled('check_in_from')) {
            $query->whereDate('check_in', '>=', $request->check_in_from);
        }
        if ($request->filled('check_in_to')) {
            $query->whereDate('check_in', '<=', $request->check_in_to);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reservation_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('guest', function ($g) use ($request) {
                      $g->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Tri
        $sort = $request->get('sort', 'check_in_desc');
        match ($sort) {
            'check_in_asc' => $query->orderBy('check_in', 'asc'),
            'check_out_desc' => $query->orderBy('check_out', 'desc'),
            'check_out_asc' => $query->orderBy('check_out', 'asc'),
            'total_price_desc' => $query->orderBy('total_price', 'desc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('check_in', 'desc'),
        };

        $reservations = $query->paginate(10);

        // Statistiques
        $stats = [
            'total' => Reservation::count(),
            'pending' => Reservation::pending()->count(),
            'confirmed' => Reservation::confirmed()->count(),
            'active' => Reservation::active()->count(),
            'today_checkins' => Reservation::checkInToday()->count(),
            'today_checkouts' => Reservation::checkOutToday()->count(),
        ];

        $rooms = Room::orderBy('room_number')->get();
        $guests = Guest::orderBy('name')->get();

        return view('pages.dashboard.reservations.index', [
            'title' => 'Réservations',
            'reservations' => $reservations,
            'stats' => $stats,
            'rooms' => $rooms,
            'guests' => $guests,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rooms = Room::available()->orderBy('room_number')->get();
        $guests = Guest::orderBy('name')->get();

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
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:10',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);

        // Vérifier la disponibilité de la chambre
        $room = Room::findOrFail($validated['room_id']);
        if (!$room->isAvailableForPeriod($checkIn, $checkOut)) {
            return back()
                ->withInput()
                ->with('error', 'Cette chambre n\'est pas disponible pour la période sélectionnée.');
        }

        $nights = $checkIn->diffInDays($checkOut, false) ?: 1;
        $validated['total_price'] = $room->price_per_night * $nights;

        $reservation = Reservation::create($validated);

        ActivityLogger::log('reservation_created', 'Réservation ' . $reservation->reservation_number . ' créée (chambre ' . $room->room_number . ')', $reservation);

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
        $reservation->load(['room', 'user', 'guest', 'enterprise', 'settlements']);
        $roomBillOrders = $reservation->roomBillOrdersUnsettled()->with('orderItems')->orderBy('created_at')->get();
        $totalRoomBill = $roomBillOrders->sum('total');

        return view('pages.dashboard.reservations.show', [
            'title' => 'Réservation ' . $reservation->reservation_number,
            'reservation' => $reservation,
            'roomBillOrders' => $roomBillOrders,
            'totalRoomBill' => $totalRoomBill,
        ]);
    }

    /**
     * Afficher la facture / reçu (réservation en check-out) : logo entreprise, infos, détail, possibilité d'imprimer.
     */
    public function invoice(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_out') {
            return redirect()->route('dashboard.reservations.show', $reservation)
                ->with('error', 'La facture est disponible uniquement pour les réservations avec statut Check-out effectué.');
        }

        $reservation->load(['room', 'user', 'guest', 'enterprise', 'settlements']);
        $roomBillOrders = $reservation->roomBillOrders()->with('orderItems')->orderBy('created_at')->get();
        $totalRoomBill = $roomBillOrders->sum('total');

        return view('pages.dashboard.reservations.invoice', [
            'title' => 'Facture ' . $reservation->reservation_number,
            'reservation' => $reservation,
            'roomBillOrders' => $roomBillOrders,
            'totalRoomBill' => $totalRoomBill,
        ]);
    }

    /**
     * Télécharger la facture en PDF (réservation check-out).
     */
    public function invoicePdf(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_out') {
            return redirect()->route('dashboard.reservations.show', $reservation)
                ->with('error', 'La facture PDF est disponible uniquement pour les réservations avec statut Check-out effectué.');
        }

        $reservation->load(['room', 'user', 'guest', 'enterprise', 'settlements']);
        $roomBillOrders = $reservation->roomBillOrders()->with('orderItems')->orderBy('created_at')->get();
        $totalConsos = $roomBillOrders->sum('total');
        $grandTotal = (float) $reservation->total_price + $totalConsos;

        $logoBase64 = null;
        $logoMime = 'image/png';
        if ($reservation->enterprise && $reservation->enterprise->logo) {
            $path = Storage::disk('public')->path($reservation->enterprise->logo);
            if (is_file($path)) {
                $logoBase64 = base64_encode(file_get_contents($path));
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $logoMime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
            }
        }

        $pdf = Pdf::loadView('pages.dashboard.reservations.invoice-pdf', [
            'reservation' => $reservation,
            'roomBillOrders' => $roomBillOrders,
            'grandTotal' => $grandTotal,
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'emittedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'facture-' . $reservation->reservation_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Régler la note de chambre (facture) : Wave, Orange Money, Espèce, Carte bancaire
     */
    public function settle(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|in:wave,orange_money,cash,card',
            'notes' => 'nullable|string|max:500',
        ]);

        $orders = $reservation->roomBillOrdersUnsettled()->get();
        $amount = $orders->sum('total');

        if ($amount <= 0) {
            return back()->with('error', 'Aucun montant à régler sur la note de chambre.');
        }

        ReservationSettlement::create([
            'reservation_id' => $reservation->id,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'paid_at' => now(),
            'notes' => $request->notes,
        ]);

        foreach ($orders as $order) {
            $order->update(['settled_at' => now()]);
        }

        return back()->with('success', 'Note de chambre réglée : ' . number_format($amount, 0, ',', ' ') . ' FCFA (' . (ReservationSettlement::paymentMethodLabels()[$request->payment_method] ?? $request->payment_method) . ').');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $rooms = Room::orderBy('room_number')->get();
        $guests = Guest::orderBy('name')->get();

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
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:10',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);

        if ($validated['room_id'] != $reservation->room_id) {
            $room = Room::findOrFail($validated['room_id']);
            if (!$room->isAvailableForPeriod($checkIn, $checkOut)) {
                return back()
                    ->withInput()
                    ->with('error', 'Cette chambre n\'est pas disponible pour la période sélectionnée.');
            }
        }

        $room = Room::findOrFail($validated['room_id']);
        $nights = $checkIn->diffInDays($checkOut, false) ?: 1;
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

        ActivityLogger::log('reservation_check_in', 'Check-in réservation ' . $reservation->reservation_number, $reservation);

        // Mettre à jour le statut de la chambre
        $reservation->room->update(['status' => 'occupied']);

        return back()->with('success', 'Check-in effectué avec succès !');
    }

    /**
     * Check-out — autorisé seulement si la note de chambre est réglée.
     */
    public function checkOut(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Seules les réservations avec check-in peuvent être check-out.');
        }

        $orders = $reservation->roomBillOrdersUnsettled()->get();
        $totalRoomBill = $orders->sum(fn ($o) => (float) $o->total);
        if ($totalRoomBill > 0) {
            return back()->with('error', 'La note de chambre doit être réglée avant le check-out. Réglez la facture dans la section « Note de chambre » ci-dessous, puis réessayez.');
        }

        $reservation->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        ActivityLogger::log('reservation_check_out', 'Check-out réservation ' . $reservation->reservation_number, $reservation);

        // Norme : après check-out, la chambre redevient disponible jusqu'à la prochaine réservation (check-in).
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

        ActivityLogger::log('reservation_cancelled', 'Réservation ' . $reservation->reservation_number . ' annulée', $reservation);

        // Remettre la chambre disponible : si elle était réservée (confirmée) ou occupée (check-in).
        if (in_array($reservation->room->status, ['reserved', 'occupied'], true)) {
            $reservation->room->update(['status' => 'available']);
        }

        return back()->with('success', 'Réservation annulée avec succès !');
    }
}
