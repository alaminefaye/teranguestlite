<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Excursion;
use App\Models\ExcursionBooking;
use App\Models\Guest;
use App\Models\Room;
use App\Http\Helpers\TranslatableApiHelper;
use App\Services\GuestReservationHelper;
use Illuminate\Support\Facades\Log;

class ExcursionController extends Controller
{
    /**
     * Liste des excursions
     */
    public function index(Request $request)
    {
        $query = Excursion::query()->active();

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par disponibilité
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $excursions = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $excursions->map(function ($excursion) {
                return [
                    'id' => $excursion->id,
                    'name' => TranslatableApiHelper::translationsFor($excursion, 'name'),
                    'type' => $excursion->type,
                    'type_label' => $excursion->type_label,
                    'description' => TranslatableApiHelper::translationsFor($excursion, 'description'),
                    'price_adult' => $excursion->price_adult,
                    'price_child' => $excursion->price_child,
                    'formatted_price_adult' => $excursion->formatted_price_adult,
                    'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                    'duration_hours' => $excursion->duration_hours,
                    'departure_time' => $excursion->departure_time,
                    'schedule_description' => $excursion->schedule_description,
                    'children_age_range' => $excursion->children_age_range,
                    'image' => $excursion->image ? asset('storage/' . $excursion->image) : null,
                    'min_participants' => $excursion->min_participants,
                    'max_participants' => $excursion->max_participants,
                    'included' => $excursion->included,
                    'not_included' => $excursion->not_included,
                    'is_available' => $excursion->is_available,
                    'is_featured' => $excursion->is_featured,
                ];
            }),
        ], 200);
    }

    /**
     * Détails d'une excursion
     */
    public function show($id)
    {
        $excursion = Excursion::active()->find($id);

        if (!$excursion) {
            return response()->json([
                'success' => false,
                'message' => 'Excursion non trouvée',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $excursion->id,
                'name' => TranslatableApiHelper::translationsFor($excursion, 'name'),
                'type' => $excursion->type,
                'type_label' => $excursion->type_label,
                'description' => TranslatableApiHelper::translationsFor($excursion, 'description'),
                'price_adult' => $excursion->price_adult,
                'price_child' => $excursion->price_child,
                'formatted_price_adult' => $excursion->formatted_price_adult,
                'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                'duration_hours' => $excursion->duration_hours,
                'departure_time' => $excursion->departure_time,
                'schedule_description' => $excursion->schedule_description,
                'children_age_range' => $excursion->children_age_range,
                'image' => $excursion->image ? asset('storage/' . $excursion->image) : null,
                'min_participants' => $excursion->min_participants,
                'max_participants' => $excursion->max_participants,
                'included' => $excursion->included,
                'not_included' => $excursion->not_included,
                'is_available' => $excursion->is_available,
            ],
        ], 200);
    }

    /**
     * Réserver une excursion
     */
    public function book(Request $request, $id)
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $excursion = Excursion::active()->find($id);

        if (!$excursion) {
            return response()->json([
                'success' => false,
                'message' => 'Excursion non trouvée',
            ], 404);
        }

        if (!$excursion->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Cette excursion n\'est pas disponible',
            ], 400);
        }

        $adults = $request->adults;
        $children = $request->children ?? 0;
        $totalParticipants = $adults + $children;

        // Vérifier min/max participants
        if ($totalParticipants < $excursion->min_participants) {
            return response()->json([
                'success' => false,
                'message' => "Minimum {$excursion->min_participants} participants requis",
            ], 400);
        }

        if ($totalParticipants > $excursion->max_participants) {
            return response()->json([
                'success' => false,
                'message' => "Maximum {$excursion->max_participants} participants autorisés",
            ], 400);
        }

        // Calculer le prix total
        $totalPrice = ($adults * $excursion->price_adult) + ($children * $excursion->price_child);

        $user = $request->user();
        $stay = GuestReservationHelper::requireValidCodeOrActiveStay($user, $request->input('client_code'));
        if (!$stay) {
            $message = $request->filled('client_code') && trim((string) $request->input('client_code')) !== ''
                ? GuestReservationHelper::MESSAGE_CLIENT_CODE_INVALID_OR_EXPIRED
                : GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT;
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        $booking = ExcursionBooking::create([
            'user_id' => $user->id,
            'guest_id' => $stay['guest_id'],
            'excursion_id' => (int) $id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $stay['room_id'],
            'booking_date' => $request->date,
            'number_of_adults' => $adults,
            'number_of_children' => $children,
            'total_price' => $totalPrice,
            'special_requests' => $request->special_requests,
            'status' => 'pending',
        ]);

        // Notification au client de la chambre et au staff
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($booking->room_id) {
                $firebaseService->sendReservationConfirmationToRoom(
                    $booking->room_id,
                    (string) $booking->id,
                    $excursion->name
                );
            }

            $firebaseService->sendToStaffForSection(
                $user->enterprise_id,
                \App\Helpers\StaffSection::EXCURSIONS,
                'Nouvelle réservation excursion',
                "Nouvelle excursion {$excursion->name} le " . \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y'),
                [
                    'type' => 'excursion_booking',
                    'booking_id' => (string) $booking->id,
                    'screen' => 'AdminExcursionBookings',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Excursion réservée avec succès',
            'data' => [
                'id' => $booking->id,
                'excursion' => [
                    'id' => $excursion->id,
                    'name' => $excursion->name,
                    'departure_time' => $excursion->departure_time,
                ],
                'date' => \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'adults' => $booking->number_of_adults,
                'children' => $booking->number_of_children,
                'total_price' => $booking->total_price,
                'formatted_total' => number_format((float) $booking->total_price, 0, '', ' ') . ' FCFA',
                'status' => $booking->status,
            ],
        ], 201);
    }

    /**
     * Mes réservations d'excursions
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();
        $isStaffOrAdmin = method_exists($user, 'isAdmin') && method_exists($user, 'isStaff')
            ? ($user->isAdmin() || $user->isStaff())
            : false;

        $query = ExcursionBooking::with(['excursion', 'room', 'guest']);

        if (!$isStaffOrAdmin) {
            // Si un client_code est fourni, ne montrer QUE les réservations de ce guest précis
            $clientCode = trim((string) $request->input('client_code', ''));
            if ($clientCode !== '') {
                $guestId = Guest::withoutGlobalScope('enterprise')
                    ->where('enterprise_id', $user->enterprise_id)
                    ->where('access_code', $clientCode)
                    ->value('id');
                if ($guestId) {
                    $query->where('guest_id', $guestId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->where('user_id', $user->id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('booking_date', today());
        } elseif ($period === 'week') {
            $query->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $perPage = (int) $request->input('per_page', 15);

        $bookings = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'excursion' => [
                        'id' => $booking->excursion->id,
                        'name' => $booking->excursion->name,
                        'type' => $booking->excursion->type,
                    ],
                    'date' => $booking->booking_date?->format('Y-m-d'),
                    'adults' => $booking->number_of_adults,
                    'children' => $booking->number_of_children,
                    'total_price' => $booking->total_price,
                    'formatted_total' => number_format($booking->total_price, 0, '', ' ') . ' FCFA',
                    'status' => $booking->status,
                    'room_number' => $booking->room ? $booking->room->room_number : null,
                    'guest_name' => $booking->guest ? $booking->guest->name : null,
                    'created_at' => $booking->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'from' => $bookings->firstItem(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'to' => $bookings->lastItem(),
                'total' => $bookings->total(),
            ],
        ], 200);
    }

    public function updateBookingStatus(Request $request, $id)
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

        $booking = ExcursionBooking::with(['excursion', 'room', 'guest'])->find($id);

        if (!$booking || $booking->enterprise_id != $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée',
            ], 404);
        }

        $validated = $request->validate([
            'action' => 'required|string|in:confirm,complete,cancel',
            'reason' => 'nullable|string|max:255',
        ]);

        $action = $validated['action'];
        $validActions = ['confirm', 'complete', 'cancel'];

        if (!in_array($action, $validActions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action invalide',
            ], 400);
        }

        $statusTransitions = [
            'confirm' => ['pending' => 'confirmed'],
            'complete' => ['confirmed' => 'completed'],
            'cancel' => ['pending' => 'cancelled', 'confirmed' => 'cancelled'],
        ];

        if (!isset($statusTransitions[$action][$booking->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Transition de statut non autorisée',
            ], 400);
        }

        $nextStatus = $statusTransitions[$action][$booking->status];
        $booking->status = $nextStatus;

        if ($nextStatus === 'confirmed' && !$booking->confirmed_at) {
            $booking->confirmed_at = now();
        }

        if ($nextStatus === 'cancelled' && !$booking->cancelled_at) {
            $booking->cancelled_at = now();
            $reason = $validated['reason'] ?? '';
            if ($reason !== '') {
                $booking->cancellation_reason = $reason;
            }
        }

        $booking->save();

        try {
            if ($booking->room_id) {
                $firebaseService = app(\App\Services\FirebaseNotificationService::class);
                $excursionName = $booking->excursion->name ?? 'Excursion';
                $dateStr = \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y');
                $reason = $booking->cancellation_reason ?? '';

                $statusMessages = [
                    'confirmed' => "Votre réservation excursion « {$excursionName} » est confirmée pour le {$dateStr}.",
                    'cancelled' => "Votre réservation excursion « {$excursionName} » a été annulée.",
                    'completed' => "Votre excursion « {$excursionName} » du {$dateStr} a été honorée.",
                ];

                $title = 'Réservation Excursions & Activités';
                $body = $statusMessages[$nextStatus] ?? 'Statut de votre réservation excursion mis à jour.';

                if ($nextStatus === 'cancelled' && $reason !== '') {
                    $body .= ' Motif : ' . $reason;
                }

                $data = [
                    'type' => 'excursion_booking_status',
                    'booking_id' => (string) $booking->id,
                    'status' => $nextStatus,
                    'screen' => 'MyExcursionBookings',
                    'excursion_name' => $excursionName,
                    'date' => $dateStr,
                ];

                if ($reason !== '') {
                    $data['reason'] = $reason;
                }

                $firebaseService->sendToClientOfRoom(
                    $booking->room_id,
                    $title,
                    $body,
                    $data
                );
            }
        } catch (\Exception $e) {
            Log::error(
                'Firebase notification error (excursion booking status): ' . $e->getMessage()
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->id,
                'excursion' => [
                    'id' => $booking->excursion->id,
                    'name' => $booking->excursion->name,
                    'type' => $booking->excursion->type,
                ],
                'date' => \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'adults' => $booking->number_of_adults,
                'children' => $booking->number_of_children,
                'total_price' => $booking->total_price,
                'formatted_total' => number_format($booking->total_price, 0, '', ' ') . ' FCFA',
                'status' => $booking->status,
                'room_number' => $booking->room ? $booking->room->room_number : null,
                'guest_name' => $booking->guest ? $booking->guest->name : null,
                'created_at' => $booking->created_at->toISOString(),
            ],
        ], 200);
    }
}
