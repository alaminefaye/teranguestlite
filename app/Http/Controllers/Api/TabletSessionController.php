<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Validation du code client sur la tablette en chambre.
 * Pas d'auth utilisateur : la tablette envoie code + room_id pour lier la session au client et à la chambre.
 */
class TabletSessionController extends Controller
{
    /**
     * Valide le code et retourne la session (guest + room + reservation) si le séjour est valide.
     * POST /api/tablet/validate-code
     * Body: { "code": "123456", "room_id": 1 } ou { "code": "123456", "room_number": "101" }
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20',
            'room_id' => 'nullable|exists:rooms,id',
            'room_number' => 'nullable|string|max:20',
        ]);

        $code = trim($request->input('code'));
        $room = null;

        if ($request->filled('room_id')) {
            $room = Room::find($request->room_id);
        } elseif ($request->filled('room_number')) {
            $room = Room::withoutGlobalScope('enterprise')
                ->where('room_number', $request->room_number)
                ->first();
        }

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Chambre non trouvée. Indiquez room_id ou room_number.',
            ], 400);
        }

        $enterpriseId = $room->enterprise_id;

        $guest = Guest::withoutGlobalScope('enterprise')
            ->where('enterprise_id', $enterpriseId)
            ->where('access_code', $code)
            ->first();

        if (!$guest) {
            return response()->json([
                'success' => false,
                'message' => 'Le code saisi est incorrect. Vérifiez le code à 6 chiffres reçu à l\'enregistrement.',
            ], 401);
        }

        $reservation = Reservation::withoutGlobalScope('enterprise')
            ->where('guest_id', $guest->id)
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Votre réservation est terminée ou n\'est pas encore active. Vérifiez vos dates de séjour avec la réception.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'guest_id' => $guest->id,
                'guest_name' => $guest->name,
                'guest_phone' => $guest->phone,
                'guest_email' => $guest->email,
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'validated_at' => now()->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Vérifie que la session (guest + room + réservation) est encore valide.
     * Si le code client a été régénéré (guest.updated_at > validated_at), rejette la session.
     * POST /api/tablet/validate-session
     * Body: { "guest_id": 1, "room_id": 1, "reservation_id": 1, "validated_at": "2025-02-09T12:00:00.000000Z" }
     */
    public function validateSession(Request $request): JsonResponse
    {
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'reservation_id' => 'required|exists:reservations,id',
            'validated_at' => 'nullable|string|date',
        ]);

        $guest = Guest::withoutGlobalScope('enterprise')->find($request->guest_id);
        $room = Room::withoutGlobalScope('enterprise')->find($request->room_id);
        if (!$guest || !$room || $room->enterprise_id !== $guest->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour invalide ou expiré. Entrez à nouveau votre code.',
            ], 403);
        }

        // Si le client a été modifié (ex: code régénéré) après la validation, rejeter la session
        $validatedAt = $request->input('validated_at');
        if ($validatedAt !== null && $validatedAt !== '') {
            try {
                $validatedAtCarbon = \Carbon\Carbon::parse($validatedAt);
                if ($guest->updated_at->gt($validatedAtCarbon)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Code client régénéré ou modifié. Entrez à nouveau votre code.',
                    ], 403);
                }
            } catch (\Exception $e) {
                // Si la date est invalide, on continue la vérification normale
            }
        }

        $reservation = Reservation::withoutGlobalScope('enterprise')
            ->where('id', $request->reservation_id)
            ->where('guest_id', $guest->id)
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour invalide ou expiré. Entrez à nouveau votre code.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'guest_id' => $guest->id,
                'guest_name' => $guest->name,
                'guest_phone' => $guest->phone,
                'guest_email' => $guest->email,
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'validated_at' => $request->input('validated_at'),
            ],
        ], 200);
    }

    /**
     * Passer une commande room service depuis la tablette (session client).
     * POST /api/tablet/checkout
     * Body: guest_id, room_id, reservation_id (pour revalidation), items[], special_instructions
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'reservation_id' => 'required|exists:reservations,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,room_bill,wave,orange_money',
        ]);

        $guest = Guest::withoutGlobalScope('enterprise')->find($request->guest_id);
        $room = Room::withoutGlobalScope('enterprise')->find($request->room_id);
        if (!$guest || !$room || $room->enterprise_id !== $guest->enterprise_id) {
            return response()->json(['success' => false, 'message' => 'Session invalide.'], 403);
        }

        $reservation = Reservation::withoutGlobalScope('enterprise')
            ->where('id', $request->reservation_id)
            ->where('guest_id', $guest->id)
            ->where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Séjour invalide ou expiré. Entrez à nouveau votre code.',
            ], 403);
        }

        $subtotal = 0;
        $itemsData = [];
        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            if (!$menuItem || !$menuItem->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => "L'article {$menuItem->name} n'est plus disponible.",
                ], 400);
            }
            $qty = (int) $item['quantity'];
            $total = $menuItem->price * $qty;
            $subtotal += $total;
            $itemsData[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'item_description' => $menuItem->description,
                'unit_price' => $menuItem->price,
                'quantity' => $qty,
                'total_price' => $total,
                'special_requests' => $item['special_instructions'] ?? null,
            ];
        }

        $tax = 0;
        $deliveryFee = 0;
        $total = $subtotal + $tax + $deliveryFee;

        $order = Order::withoutGlobalScope('enterprise')->create([
            'enterprise_id' => $room->enterprise_id,
            'user_id' => null,
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'order_number' => $this->generateOrderNumber(),
            'type' => 'room_service',
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'special_instructions' => $request->special_instructions,
            'payment_method' => $request->payment_method,
        ]);

        foreach ($itemsData as $itemData) {
            $order->orderItems()->create($itemData);
        }

        // Notification push au client de la chambre (tablette)
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendNewOrderNotificationToRoom($order);
        } catch (\Exception $e) {
            \Log::error('Firebase notification error (tablet checkout): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande enregistrée.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'formatted_total' => number_format($order->total, 0, ',', ' ') . ' FCFA',
                'status' => $order->status,
            ],
        ], 201);
    }

    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Order::withoutGlobalScope('enterprise')->whereDate('created_at', today())->latest()->first();
        $seq = $last ? (int) substr($last->order_number, -3) + 1 : 1;
        return 'CMD-' . $date . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
