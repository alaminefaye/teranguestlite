<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Excursion;
use App\Models\ExcursionBooking;
use App\Models\Room;
use App\Services\GuestReservationHelper;

class ExcursionController extends Controller
{
    /**
     * Liste des excursions
     */
    public function index(Request $request)
    {
        $query = Excursion::query();

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
                    'name' => $excursion->name,
                    'type' => $excursion->type,
                    'type_label' => $excursion->type_label,
                    'description' => $excursion->description,
                    'price_adult' => $excursion->price_adult,
                    'price_child' => $excursion->price_child,
                    'formatted_price_adult' => $excursion->formatted_price_adult,
                    'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                    'duration_hours' => $excursion->duration_hours,
                    'departure_time' => $excursion->departure_time,
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
        $excursion = Excursion::find($id);

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
                'name' => $excursion->name,
                'type' => $excursion->type,
                'type_label' => $excursion->type_label,
                'description' => $excursion->description,
                'price_adult' => $excursion->price_adult,
                'price_child' => $excursion->price_child,
                'formatted_price_adult' => $excursion->formatted_price_adult,
                'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                'duration_hours' => $excursion->duration_hours,
                'departure_time' => $excursion->departure_time,
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

        $excursion = Excursion::find($id);

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
        if (! $stay) {
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
            'status' => 'confirmed',
        ]);

        // Notification au client (guest) uniquement
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendReservationConfirmationToGuest($stay['guest_id'], $booking, 'EXC-' . $booking->id);
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
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
                'date' => $booking->booking_date?->format('Y-m-d'),
                'adults' => $booking->number_of_adults,
                'children' => $booking->number_of_children,
                'total_price' => $booking->total_price,
                'formatted_total' => number_format($booking->total_price, 0, '', ' ') . ' FCFA',
                'status' => $booking->status,
            ],
        ], 201);
    }

    /**
     * Mes réservations d'excursions
     */
    public function myBookings(Request $request)
    {
        $bookings = ExcursionBooking::with('excursion')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

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
                    'created_at' => $booking->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'total' => $bookings->total(),
            ],
        ], 200);
    }
}
