<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Restaurant;
use App\Models\RestaurantReservation;
use App\Models\Room;
use App\Services\GuestReservationHelper;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('open_now')) {
            $query->open();
        }

        $restaurants = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants->map(function ($restaurant) {
                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'type' => $restaurant->type,
                    'type_label' => $restaurant->type_label,
                    'description' => $restaurant->description,
                    'cuisine_type' => $restaurant->cuisine_type,
                    'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                    'capacity' => $restaurant->capacity,
                    'opening_hours' => $restaurant->opening_hours,
                    'is_open_now' => $restaurant->is_open_now,
                    'today_hours' => $restaurant->today_hours,
                ];
            }),
        ], 200);
    }

    public function show($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'type' => $restaurant->type,
                'type_label' => $restaurant->type_label,
                'description' => $restaurant->description,
                'cuisine_type' => $restaurant->cuisine_type,
                'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                'capacity' => $restaurant->capacity,
                'opening_hours' => $restaurant->opening_hours,
                'is_open_now' => $restaurant->is_open_now,
                'today_hours' => $restaurant->today_hours,
            ],
        ], 200);
    }

    public function reserve(Request $request, $id)
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'guests' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
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

        $reservation = RestaurantReservation::create([
            'user_id' => $user->id,
            'guest_id' => $stay['guest_id'],
            'restaurant_id' => $id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $stay['room_id'],
            'reservation_date' => $request->date,
            'reservation_time' => $request->time,
            'number_of_guests' => $request->guests,
            'special_requests' => $request->special_requests,
            'status' => 'confirmed',
        ]);

        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($reservation->room_id) {
                $firebaseService->sendReservationConfirmationToRoom(
                    $reservation->room_id,
                    (string) $reservation->id,
                    $restaurant->name
                );
            }

            $firebaseService->sendToStaff(
                $user->enterprise_id,
                'Nouvelle réservation restaurant',
                "Nouvelle réservation au restaurant {$restaurant->name} le " . $reservation->reservation_date->format('d/m/Y') . " à " . \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                [
                    'type' => 'restaurant_reservation',
                    'reservation_id' => (string) $reservation->id,
                    'screen' => 'AdminRestaurantReservations',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée',
            'data' => [
                'id' => $reservation->id,
                'restaurant' => ['id' => $restaurant->id, 'name' => $restaurant->name],
                'date' => $reservation->reservation_date->format('Y-m-d'),
                'time' => \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                'guests' => $reservation->number_of_guests,
                'status' => $reservation->status,
            ],
        ], 201);
    }

    public function myReservations(Request $request)
    {
        $user = $request->user();
        $isStaffOrAdmin = method_exists($user, 'isAdmin') && method_exists($user, 'isStaff')
            ? ($user->isAdmin() || $user->isStaff())
            : false;

        $query = RestaurantReservation::with(['restaurant', 'room', 'guest']);

        if (! $isStaffOrAdmin) {
            $query->where('user_id', $user->id);
        }

        $reservations = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($res) {
                return [
                    'id' => $res->id,
                    'restaurant' => ['id' => $res->restaurant->id, 'name' => $res->restaurant->name],
                    'date' => $res->reservation_date->format('Y-m-d'),
                    'time' => \Carbon\Carbon::parse($res->reservation_time)->format('H:i'),
                    'guests' => $res->number_of_guests,
                    'status' => $res->status,
                    'room_number' => $res->room ? $res->room->room_number : null,
                    'guest_name' => $res->guest ? $res->guest->name : null,
                    'created_at' => $res->created_at->toISOString(),
                ];
            }),
            'meta' => ['current_page' => $reservations->currentPage(), 'total' => $reservations->total()],
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

        $reservation = RestaurantReservation::find($id);

        if (!$reservation || $reservation->enterprise_id != $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée',
            ], 404);
        }

        $action = $request->input('action');
        $validActions = ['confirm', 'cancel', 'honor'];

        if (!in_array($action, $validActions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action invalide',
            ], 400);
        }

        $reason = trim((string) ($request->input('reason') ?? ''));

        if ($action === 'cancel') {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);
        }

        $statusTransitions = [
            'confirm' => ['pending' => 'confirmed'],
            'cancel' => ['pending' => 'cancelled', 'confirmed' => 'cancelled'],
            'honor' => ['confirmed' => 'honored'],
        ];

        if (!isset($statusTransitions[$action][$reservation->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Transition de statut non autorisée',
            ], 400);
        }

        $nextStatus = $statusTransitions[$action][$reservation->status];

        $reservation->status = $nextStatus;

        if ($nextStatus === 'confirmed' && !$reservation->confirmed_at) {
            $reservation->confirmed_at = now();
        }

        if ($nextStatus === 'cancelled' && !$reservation->cancelled_at) {
            $reservation->cancelled_at = now();
        }

        $reservation->save();

        try {
            if ($reservation->room_id) {
                $firebaseService = app(\App\Services\FirebaseNotificationService::class);
                $restaurantName = $reservation->restaurant->name ?? 'Restaurant';
                $dateStr = $reservation->reservation_date->format('d/m/Y');
                $timeStr = \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i');

                $statusMessages = [
                    'confirmed' => "Votre réservation au restaurant {$restaurantName} a été confirmée pour le {$dateStr} à {$timeStr}.",
                    'cancelled' => "Votre réservation au restaurant {$restaurantName} a été annulée.",
                    'honored' => "Votre réservation au restaurant {$restaurantName} a été honorée.",
                ];

                $title = 'Réservation restaurant';
                $body = $statusMessages[$reservation->status] ?? 'Statut de votre réservation restaurant mis à jour.';

                if ($reservation->status === 'cancelled' && $reason !== '') {
                    $body .= ' Motif : ' . $reason;
                }

                $data = [
                    'type' => 'restaurant_reservation_status',
                    'reservation_id' => (string) $reservation->id,
                    'status' => $reservation->status,
                    'screen' => 'MyRestaurantReservations',
                    'restaurant_name' => $restaurantName,
                    'date' => $dateStr,
                    'time' => $timeStr,
                ];

                if ($reason !== '') {
                    $data['reason'] = $reason;
                }

                $firebaseService->sendToClientOfRoom(
                    $reservation->room_id,
                    $title,
                    $body,
                    $data
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Firebase notification error (restaurant reservation status): ' . $e->getMessage()
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'restaurant' => [
                    'id' => $reservation->restaurant->id,
                    'name' => $reservation->restaurant->name,
                ],
                'date' => $reservation->reservation_date->format('Y-m-d'),
                'time' => \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i'),
                'guests' => $reservation->number_of_guests,
                'status' => $reservation->status,
                'room_number' => $reservation->room ? $reservation->room->room_number : null,
                'guest_name' => $reservation->guest ? $reservation->guest->name : null,
                'created_at' => $reservation->created_at->toISOString(),
            ],
        ], 200);
    }

    /**
     * Annuler une réservation (si > 24h avant la date/heure)
     */
    public function cancelReservation(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $reason = trim((string) ($request->input('reason') ?? ''));

        $reservation = RestaurantReservation::where('id', $id)
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

        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);

            $restaurantName = $reservation->restaurant->name ?? 'Restaurant';
            $dateStr = $reservation->reservation_date->format('d/m/Y');
            $timeStr = \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i');
            $roomNumber = $reservation->room ? $reservation->room->room_number : null;
            $guestName = $reservation->guest ? $reservation->guest->name : null;

            $body = "Réservation restaurant {$restaurantName} le {$dateStr} à {$timeStr} annulée par le client";
            if ($roomNumber) {
                $body .= " (Chambre {$roomNumber})";
            }

             if ($reason !== '') {
                 $body .= ' Motif : ' . $reason;
             }

            $data = [
                'type' => 'restaurant_reservation_status',
                'reservation_id' => (string) $reservation->id,
                'status' => $reservation->status,
                'screen' => 'AdminRestaurantReservations',
                'restaurant_name' => $restaurantName,
                'date' => $dateStr,
                'time' => $timeStr,
                'room_number' => $roomNumber,
                'guest_name' => $guestName,
            ];

            if ($reason !== '') {
                $data['reason'] = $reason;
            }

            $firebaseService->sendToStaff(
                $reservation->enterprise_id,
                'Réservation restaurant annulée par le client',
                $body,
                $data
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Firebase notification error (restaurant reservation cancel): ' . $e->getMessage()
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée',
            'data' => ['id' => $reservation->id, 'status' => $reservation->status],
        ], 200);
    }
}
