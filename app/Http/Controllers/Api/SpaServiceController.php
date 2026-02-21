<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpaService;
use App\Models\SpaReservation;
use App\Services\GuestReservationHelper;
use Illuminate\Support\Facades\Log;

class SpaServiceController extends Controller
{
    /**
     * Liste des services spa
     */
    public function index(Request $request)
    {
        $query = SpaService::query();

        // Filtrer par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtrer par disponibilité
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'category_label' => $service->category_label,
                    'description' => $service->description,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'duration' => $service->duration,
                    'duration_text' => $service->duration_text,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'features' => $service->features,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    /**
     * Détails d'un service spa
     */
    public function show($id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service spa non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'category_label' => $service->category_label,
                'description' => $service->description,
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'duration' => $service->duration,
                'duration_text' => $service->duration_text,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'features' => $service->features,
                'is_available' => $service->is_available,
            ],
        ], 200);
    }

    /**
     * Réserver un service spa
     */
    public function reserve(Request $request, $id)
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service spa non trouvé',
            ], 404);
        }

        if (!$service->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ce service n\'est pas disponible actuellement',
            ], 400);
        }

        $user = $request->user();
        $stay = GuestReservationHelper::requireValidCodeOrActiveStay($user, $request->input('client_code'));
        if (! $stay) {
            $message = $request->filled('client_code') && trim((string) $request->input('client_code')) !== ''
                ? GuestReservationHelper::MESSAGE_CLIENT_CODE_INVALID_OR_EXPIRED
                : GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT;
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        $reservation = SpaReservation::create([
            'user_id' => $user->id,
            'guest_id' => $stay['guest_id'],
            'spa_service_id' => $id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $stay['room_id'],
            'reservation_date' => $request->date,
            'reservation_time' => $request->time,
            'special_requests' => $request->special_requests,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);

        // Notification au client de la chambre et au staff
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($reservation->room_id) {
                $firebaseService->sendReservationConfirmationToRoom(
                    $reservation->room_id,
                    $reservation->id,
                    $service->name
                );
            }

            $firebaseService->sendToStaff(
                $user->enterprise_id,
                'Nouvelle réservation spa',
                "Nouvelle réservation spa {$service->name} le " . $reservation->reservation_date->format('d/m/Y') . " à " . \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                [
                    'type' => 'spa_reservation',
                    'reservation_id' => (string) $reservation->id,
                    'screen' => 'AdminSpaReservations',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation spa confirmée',
            'data' => [
                'id' => $reservation->id,
                'spa_service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration' => $service->duration,
                ],
                'date' => $reservation->reservation_date->format('Y-m-d'),
                'time' => \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                'price' => (float) $reservation->price,
                'formatted_price' => number_format($reservation->price, 0, '', ' ') . ' FCFA',
                'status' => $reservation->status,
            ],
        ], 201);
    }

    /**
     * Mes réservations spa
     */
    public function myReservations(Request $request)
    {
        $user = $request->user();
        $isStaffOrAdmin = method_exists($user, 'isAdmin') && method_exists($user, 'isStaff')
            ? ($user->isAdmin() || $user->isStaff())
            : false;

        $query = SpaReservation::with(['spaService', 'room', 'guest']);

        if (! $isStaffOrAdmin) {
            $query->where('user_id', $user->id);
        }

        $reservations = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($res) {
                return [
                    'id' => $res->id,
                    'spa_service' => [
                        'id' => $res->spaService->id,
                        'name' => $res->spaService->name,
                        'duration' => $res->spaService->duration,
                    ],
                    'date' => $res->reservation_date->format('Y-m-d'),
                    'time' => \Carbon\Carbon::parse($res->reservation_time)->format('H:i'),
                    'price' => (float) $res->price,
                    'formatted_price' => number_format($res->price, 0, '', ' ') . ' FCFA',
                    'status' => $res->status,
                    'room_number' => $res->room ? $res->room->room_number : null,
                    'guest_name' => $res->guest ? $res->guest->name : null,
                    'created_at' => $res->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'total' => $reservations->total(),
            ],
        ], 200);
    }

    public function updateReservationStatus(Request $request, $id)
    {
        $user = $request->user();

        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

        $reservation = SpaReservation::with(['spaService', 'room', 'guest'])->find($id);

        if (! $reservation || $reservation->enterprise_id != $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée',
            ], 404);
        }

        $action = $request->input('action');
        $validActions = ['confirm', 'cancel', 'reschedule'];

        if (!in_array($action, $validActions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action invalide',
            ], 400);
        }

        if ($action === 'reschedule') {
            $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
            ]);

            if (!in_array($reservation->status, ['pending', 'confirmed'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La replanification est uniquement possible pour les réservations en attente ou confirmées',
                ], 400);
            }

            $reservation->reservation_date = $request->input('date');
            $reservation->reservation_time = $request->input('time');

            if ($reservation->status === 'pending') {
                $reservation->status = 'confirmed';
            }

            if ($reservation->status === 'confirmed' && ! $reservation->confirmed_at) {
                $reservation->confirmed_at = now();
            }

            $reservation->save();
        } else {
            $statusTransitions = [
                'confirm' => ['pending' => 'confirmed'],
                'cancel' => ['pending' => 'cancelled', 'confirmed' => 'cancelled'],
            ];

            if (!isset($statusTransitions[$action][$reservation->status])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transition de statut non autorisée',
                ], 400);
            }

            $nextStatus = $statusTransitions[$action][$reservation->status];
            $reservation->status = $nextStatus;

            if ($nextStatus === 'confirmed' && ! $reservation->confirmed_at) {
                $reservation->confirmed_at = now();
            }

            if ($nextStatus === 'cancelled' && ! $reservation->cancelled_at) {
                $reservation->cancelled_at = now();
            }

            $reservation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'spa_service' => [
                    'id' => $reservation->spaService->id,
                    'name' => $reservation->spaService->name,
                    'duration' => $reservation->spaService->duration,
                ],
                'date' => $reservation->reservation_date->format('Y-m-d'),
                'time' => \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                'price' => (float) $reservation->price,
                'formatted_price' => number_format($reservation->price, 0, '', ' ') . ' FCFA',
                'status' => $reservation->status,
                'room_number' => $reservation->room ? $reservation->room->room_number : null,
                'guest_name' => $reservation->guest ? $reservation->guest->name : null,
                'created_at' => $reservation->created_at->toISOString(),
            ],
        ], 200);
    }

    /**
     * Annuler une réservation spa (si > 24h avant la date/heure)
     */
    public function cancelReservation(Request $request, $id)
    {
        $reservation = SpaReservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Réservation non trouvée'], 404);
        }

        if ($reservation->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Réservation déjà annulée'], 400);
        }

        $reservationDateTime = \Carbon\Carbon::parse(
            $reservation->reservation_date->format('Y-m-d') . ' ' . $reservation->reservation_time
        );
        if ($reservationDateTime->lte(now()->addHours(24))) {
            return response()->json([
                'success' => false,
                'message' => 'L\'annulation n\'est possible que plus de 24h avant la réservation',
            ], 400);
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée',
            'data' => ['id' => $reservation->id, 'status' => $reservation->status],
        ], 200);
    }
}
