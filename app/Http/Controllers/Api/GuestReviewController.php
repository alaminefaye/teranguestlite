<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\ExcursionBooking;
use App\Models\LaundryRequest;
use App\Models\PalaceRequest;
use Illuminate\Http\Request;

class GuestReviewController extends Controller
{
    /**
     * Liste des éléments éligibles pour un avis (non encore notés par l'utilisateur).
     * À afficher après : commande livrée, check-out, demande traitée, réservation excursion terminée.
     */
    public function pending(Request $request)
    {
        $user = $request->user();
        $enterpriseId = $user->enterprise_id;

        $reviewedIds = GuestReview::where('user_id', $user->id)
            ->get()
            ->keyBy(fn ($r) => $r->reviewable_type . '#' . $r->reviewable_id);

        $pending = [];

        // Commandes livrées
        $orders = Order::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->orderByDesc('delivered_at')
            ->limit(5)
            ->get();
        foreach ($orders as $order) {
            if ($reviewedIds->has(Order::class . '#' . $order->id)) {
                continue;
            }
            $pending[] = [
                'reviewable_type' => 'order',
                'reviewable_id' => $order->id,
                'label' => 'Commande ' . $order->order_number,
                'completed_at' => $order->delivered_at?->toIso8601String(),
            ];
        }

        // Réservations check-out
        $reservations = Reservation::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->where('status', 'checked_out')
            ->orderByDesc('checked_out_at')
            ->limit(5)
            ->get();
        foreach ($reservations as $reservation) {
            if ($reviewedIds->has(Reservation::class . '#' . $reservation->id)) {
                continue;
            }
            $pending[] = [
                'reviewable_type' => 'reservation',
                'reviewable_id' => $reservation->id,
                'label' => 'Séjour ' . $reservation->reservation_number,
                'completed_at' => $reservation->checked_out_at?->toIso8601String(),
            ];
        }

        // Réservations excursion terminées
        $excursionBookings = ExcursionBooking::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('excursion')
            ->orderByDesc('booking_date')
            ->limit(5)
            ->get();
        foreach ($excursionBookings as $booking) {
            if ($reviewedIds->has(ExcursionBooking::class . '#' . $booking->id)) {
                continue;
            }
            $pending[] = [
                'reviewable_type' => 'excursion_booking',
                'reviewable_id' => $booking->id,
                'label' => 'Excursion ' . ($booking->excursion->name ?? ''),
                'completed_at' => $booking->booking_date?->toIso8601String(),
            ];
        }

        // Demandes blanchisserie livrées
        $laundry = LaundryRequest::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
        foreach ($laundry as $req) {
            if ($reviewedIds->has(LaundryRequest::class . '#' . $req->id)) {
                continue;
            }
            $pending[] = [
                'reviewable_type' => 'laundry_request',
                'reviewable_id' => $req->id,
                'label' => 'Blanchisserie ' . $req->request_number,
                'completed_at' => $req->updated_at?->toIso8601String(),
            ];
        }

        // Demandes palace terminées
        $palace = PalaceRequest::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
        foreach ($palace as $req) {
            if ($reviewedIds->has(PalaceRequest::class . '#' . $req->id)) {
                continue;
            }
            $pending[] = [
                'reviewable_type' => 'palace_request',
                'reviewable_id' => $req->id,
                'label' => 'Service ' . $req->request_number,
                'completed_at' => $req->updated_at?->toIso8601String(),
            ];
        }

        // Trier par date décroissante
        usort($pending, fn ($a, $b) => strcmp($b['completed_at'] ?? '', $a['completed_at'] ?? ''));

        return response()->json([
            'success' => true,
            'data' => array_slice($pending, 0, 10),
        ], 200);
    }

    /**
     * Soumettre un avis.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|string|in:order,reservation,excursion_booking,laundry_request,palace_request',
            'reviewable_id' => 'required|integer|min:1',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $enterpriseId = $user->enterprise_id;

        $typeMap = [
            'order' => Order::class,
            'reservation' => Reservation::class,
            'excursion_booking' => ExcursionBooking::class,
            'laundry_request' => LaundryRequest::class,
            'palace_request' => PalaceRequest::class,
        ];
        $modelClass = $typeMap[$validated['reviewable_type']];
        $id = (int) $validated['reviewable_id'];

        $exists = GuestReview::where('reviewable_type', $modelClass)
            ->where('reviewable_id', $id)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Un avis a déjà été enregistré pour cet élément.',
            ], 422);
        }

        $item = $modelClass::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('user_id', $user->id)
            ->find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Élément non trouvé ou non éligible pour un avis.',
            ], 404);
        }

        $review = GuestReview::create([
            'enterprise_id' => $enterpriseId,
            'user_id' => $user->id,
            'guest_id' => $item->guest_id ?? null,
            'room_id' => $item->room_id ?? null,
            'reviewable_type' => $modelClass,
            'reviewable_id' => $id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre avis !',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ],
        ], 201);
    }

    /**
     * Liste des avis déjà donnés par l'utilisateur (optionnel, pour une rubrique « Mes avis »).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $reviews = GuestReview::where('user_id', $user->id)
            ->with('reviewable')
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        $reviews->getCollection()->loadMorph('reviewable', [
            ExcursionBooking::class => ['excursion'],
        ]);

        $data = $reviews->map(function (GuestReview $r) {
            $label = class_basename($r->reviewable_type);
            if ($r->reviewable) {
                if ($r->reviewable_type === Order::class) {
                    $label = 'Commande ' . $r->reviewable->order_number;
                } elseif ($r->reviewable_type === Reservation::class) {
                    $label = 'Séjour ' . $r->reviewable->reservation_number;
                } elseif ($r->reviewable_type === ExcursionBooking::class) {
                    $label = 'Excursion ' . ($r->reviewable->excursion->name ?? '');
                } elseif ($r->reviewable_type === LaundryRequest::class) {
                    $label = 'Blanchisserie ' . $r->reviewable->request_number;
                } elseif ($r->reviewable_type === PalaceRequest::class) {
                    $label = 'Service ' . $r->reviewable->request_number;
                }
            }
            return [
                'id' => $r->id,
                'reviewable_type' => class_basename($r->reviewable_type),
                'reviewable_id' => $r->reviewable_id,
                'label' => $label,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ], 200);
    }
}
