<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\Room;

class OrderController extends Controller
{
    /**
     * IDs des guests dont l'utilisateur (tablette en chambre) peut voir les commandes :
     * réservation active dans la chambre de l'utilisateur (room_number).
     */
    private function guestIdsForUserRoom(\App\Models\User $user): array
    {
        if (! $user->room_number || ! $user->enterprise_id) {
            return [];
        }
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();
        if (! $room) {
            return [];
        }
        return Reservation::where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->pluck('guest_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Liste des commandes : celles de l'utilisateur connecté (user_id) OU celles du guest de sa chambre (code client).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guestIds = $this->guestIdsForUserRoom($user);
        $query = Order::where(function ($q) use ($user, $guestIds) {
            $q->where('user_id', $user->id);
            if (! empty($guestIds)) {
                $q->orWhereIn('guest_id', $guestIds);
            }
        });

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $orders = $query->withCount('orderItems')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'total_amount' => (float) $order->total,
                    'formatted_total' => $order->formatted_total,
                    'status' => $order->status,
                    'status_label' => $order->status_name,
                    'items_count' => $order->order_items_count,
                    'created_at' => $order->created_at->toISOString(),
                    'estimated_delivery' => $order->created_at->addMinutes(30)->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'from' => $orders->firstItem(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'to' => $orders->lastItem(),
                'total' => $orders->total(),
            ],
        ], 200);
    }

    /**
     * Détails d'une commande (si elle appartient à l'utilisateur connecté ou au guest de sa chambre)
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $guestIds = $this->guestIdsForUserRoom($user);
        $order = Order::with('orderItems.menuItem')
            ->where('id', $id)
            ->where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (! empty($guestIds)) {
                    $q->orWhereIn('guest_id', $guestIds);
                }
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        $itemsWithDetails = $order->orderItems->map(function ($orderItem) {
            $menuItem = $orderItem->menuItem;
            $imageUrl = null;
            if ($menuItem && $menuItem->image) {
                $imageUrl = asset('storage/' . $menuItem->image);
            }
            return [
                'id' => $orderItem->menu_item_id,
                'menu_item' => [
                    'id' => $orderItem->menu_item_id,
                    'name' => $orderItem->item_name,
                    'image' => $imageUrl,
                    'description' => $orderItem->item_description,
                ],
                'quantity' => $orderItem->quantity,
                'unit_price' => (float) $orderItem->unit_price,
                'subtotal' => (float) $orderItem->total_price,
                'special_instructions' => $orderItem->special_requests,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'total_amount' => (float) $order->total,
                'formatted_total' => $order->formatted_total,
                'status' => $order->status,
                'status_label' => $order->status_name,
                'special_instructions' => $order->special_instructions,
                'instructions' => $order->special_instructions,
                'items' => $itemsWithDetails,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
            ],
        ], 200);
    }

    /**
     * Recommander une commande (si elle appartient à l'utilisateur connecté ou au guest de sa chambre)
     */
    public function reorder(Request $request, $id)
    {
        $user = $request->user();
        $guestIds = $this->guestIdsForUserRoom($user);
        $order = Order::with('orderItems')
            ->where('id', $id)
            ->where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (! empty($guestIds)) {
                    $q->orWhereIn('guest_id', $guestIds);
                }
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        // Vérifier la disponibilité des articles
        $unavailableItems = [];
        foreach ($order->orderItems as $orderItem) {
            $menuItem = MenuItem::find($orderItem->menu_item_id);
            if (!$menuItem || !$menuItem->is_available) {
                $unavailableItems[] = $menuItem ? $menuItem->name : $orderItem->item_name;
            }
        }

        if (!empty($unavailableItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Certains articles ne sont plus disponibles',
                'unavailable_items' => $unavailableItems,
            ], 400);
        }

        $user = $request->user();
        $roomId = null;
        if ($user->room_number) {
            $room = \App\Models\Room::where('enterprise_id', $user->enterprise_id)
                ->where('room_number', $user->room_number)
                ->first();
            $roomId = $room?->id;
        }

        $subtotal = $order->orderItems->sum('total_price');
        $tax = 0;
        $deliveryFee = 0;
        $total = $subtotal + $tax + $deliveryFee;

        $newOrder = Order::create([
            'user_id' => $user->id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $roomId,
            'order_number' => $this->generateOrderNumber(),
            'type' => 'room_service',
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'special_instructions' => $order->special_instructions,
        ]);

        foreach ($order->orderItems as $orderItem) {
            $newOrder->orderItems()->create([
                'menu_item_id' => $orderItem->menu_item_id,
                'item_name' => $orderItem->item_name,
                'item_description' => $orderItem->item_description,
                'unit_price' => $orderItem->unit_price,
                'quantity' => $orderItem->quantity,
                'total_price' => $orderItem->total_price,
                'special_requests' => $orderItem->special_requests,
            ]);
        }

        // Notification au client de la chambre
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($newOrder->room_id) {
                $firebaseService->sendNewOrderNotificationToRoom($newOrder);
            }
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande re-passée avec succès',
            'data' => [
                'id' => $newOrder->id,
                'order_number' => $newOrder->order_number,
                'total' => (float) $newOrder->total,
                'total_amount' => (float) $newOrder->total,
                'formatted_total' => $newOrder->formatted_total,
                'status' => $newOrder->status,
            ],
        ], 201);
    }

    /**
     * Annuler une commande (si elle appartient à l'utilisateur ou au guest de sa chambre).
     * Uniquement si statut = pending, confirmed ou preparing.
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $guestIds = $this->guestIdsForUserRoom($user);
        $order = Order::where('id', $id)
            ->where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (! empty($guestIds)) {
                    $q->orWhereIn('guest_id', $guestIds);
                }
            })
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        $cancelAllowed = in_array($order->status, ['pending', 'confirmed', 'preparing'], true);
        if (! $cancelAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être annulée (elle est déjà prête, en livraison ou livrée).',
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ],
        ], 200);
    }

    /**
     * Générer un numéro de commande unique
     */
    private function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())->latest()->first();
        $sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -3)) + 1) : 1;
        
        return 'CMD-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
