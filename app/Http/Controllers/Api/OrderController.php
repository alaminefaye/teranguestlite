<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * IDs des guests dont l'utilisateur (tablette en chambre) peut voir les commandes :
     * réservation active dans la chambre liée à l'utilisateur (room_id ou room_number).
     */
    private function guestIdsForUserRoom(\App\Models\User $user): array
    {
        if (!$user->enterprise_id) {
            return [];
        }
        $room = null;
        if ($user->room_id) {
            $room = Room::where('enterprise_id', $user->enterprise_id)->where('id', $user->room_id)->first();
        }
        if (!$room && $user->room_number) {
            $room = Room::where('enterprise_id', $user->enterprise_id)
                ->where('room_number', $user->room_number)
                ->first();
        }
        if (!$room) {
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
     * Liste des commandes.
     * - Pour un guest (tablette en chambre) : commandes de l'utilisateur connecté OU du guest de sa chambre.
     * - Pour un admin/staff : toutes les commandes Room Service de l'entreprise (grâce au scope multi-tenant).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isStaffOrAdmin = $user->isAdmin() || $user->isStaff();

        if ($isStaffOrAdmin) {
            $query = Order::byType('room_service');
        } else {
            $guestIds = $this->guestIdsForUserRoom($user);
            $query = Order::where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (!empty($guestIds)) {
                    $q->orWhereIn('guest_id', $guestIds);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $orders = $query
            ->withCount('orderItems')
            ->with(['room', 'guest'])
            ->paginate($perPage);

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
                    'room_number' => $order->room ? $order->room->room_number : null,
                    'guest_name' => $order->guest ? $order->guest->name : null,
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
     * Détails d'une commande.
     * - Pour un guest (tablette en chambre) : uniquement ses commandes / celles du guest de sa chambre.
     * - Pour un admin/staff : n'importe quelle commande de l'entreprise.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $isStaffOrAdmin = $user->isAdmin() || $user->isStaff();

        $query = Order::with(['orderItems.menuItem', 'room', 'guest']);

        if (!$isStaffOrAdmin) {
            $guestIds = $this->guestIdsForUserRoom($user);
            $query->where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (!empty($guestIds)) {
                    $q->orWhereIn('guest_id', $guestIds);
                }
            });
        }

        $order = $query->where('id', $id)->first();

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
                'room_number' => $order->room ? $order->room->room_number : null,
                'guest_name' => $order->guest ? $order->guest->name : null,
                'guest_phone' => $order->guest ? $order->guest->phone : null,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'delivered_at' => $order->delivered_at ? $order->delivered_at->toISOString() : null,
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
                if (!empty($guestIds)) {
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
            Log::error('Firebase notification error: ' . $e->getMessage());
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
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $reason = trim($validated['reason']);

        $user = $request->user();
        $guestIds = $this->guestIdsForUserRoom($user);
        $order = Order::where('id', $id)
            ->where(function ($q) use ($user, $guestIds) {
                $q->where('user_id', $user->id);
                if (!empty($guestIds)) {
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

        $cancelAllowed = in_array($order->status, ['pending', 'confirmed', 'preparing'], true);
        if (!$cancelAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être annulée (elle est déjà prête, en livraison ou livrée).',
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        try {
            $firebaseService = app(FirebaseNotificationService::class);

            $serviceName = $order->type === 'room_service' ? 'Room Service' : $order->typeName;
            $statusLabel = $order->statusName;
            $roomNumber = $order->room ? $order->room->room_number : null;
            $guestName = $order->guest ? $order->guest->name : null;

            $body = "Commande #{$order->order_number} annulée par le client";
            if ($roomNumber) {
                $body .= " (Chambre {$roomNumber})";
            }
            if ($guestName) {
                $body .= " – {$guestName}";
            }
            if ($reason !== '') {
                $body .= ' Motif : ' . $reason;
            }

            $data = [
                'type' => 'order_status',
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $statusLabel,
                'service_name' => $serviceName,
                'screen' => 'AdminOrders',
                'room_number' => $roomNumber,
                'guest_name' => $guestName,
            ];

            if ($reason !== '') {
                $data['reason'] = $reason;
            }

            // Cibler tous les admins + staffs non "Service en chambre"
            // (pas de filtre sur managed_sections pour garantir la réception)
            $enterpriseId = $order->enterprise_id ?? $user->enterprise_id;
            $recipients = \App\Models\User::where('enterprise_id', $enterpriseId)
                ->where(function ($q) {
                    $q->where('role', 'admin')
                        ->orWhere(function ($q2) {
                            $q2->where('role', 'staff')
                                ->where(function ($q3) {
                                    $q3->whereNull('department')
                                        ->orWhere('department', '!=', 'Service en chambre');
                                });
                        });
                })
                ->has('fcmTokens')
                ->get();

            foreach ($recipients as $recipient) {
                $firebaseService->sendToUser($recipient, 'Commande annulée par le client', $body, $data);
            }

            Log::info("order_cancelled: notification envoyée à {$recipients->count()} staff(s)/admin(s). Commande #{$order->order_number}");
        } catch (\Exception $e) {
            Log::error('Firebase notification error (order cancel API): ' . $e->getMessage(), ['order_id' => $order->id]);
        }

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

    public function updateStatus(Request $request, $id)
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

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:confirm,prepare,mark_ready,deliver,complete'],
        ]);

        $order = Order::where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        if ($order->enterprise_id !== $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable pour cet établissement',
            ], 404);
        }

        $action = $validated['action'];

        if ($action === 'confirm') {
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes en attente peuvent être confirmées.',
                ], 400);
            }
            $order->status = 'confirmed';
        } elseif ($action === 'prepare') {
            if ($order->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes confirmées peuvent être préparées.',
                ], 400);
            }
            $order->status = 'preparing';
        } elseif ($action === 'mark_ready') {
            if ($order->status !== 'preparing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes en préparation peuvent être marquées comme prêtes.',
                ], 400);
            }
            $order->status = 'ready';
        } elseif ($action === 'deliver') {
            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes prêtes peuvent être mises en livraison.',
                ], 400);
            }
            $order->status = 'delivering';
        } elseif ($action === 'complete') {
            if ($order->status !== 'delivering') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes en livraison peuvent être marquées comme livrées.',
                ], 400);
            }
            $order->status = 'delivered';
            $order->delivered_at = now();
        }

        $order->save();

        try {
            if (!empty($order->room_id)) {
                app(FirebaseNotificationService::class)->sendOrderStatusNotificationToRoom($order);
            }

            // Quand la commande est prête en cuisine : notifier admins + cuisine SEULEMENT
            // (le service en chambre sera notifié séparément via le bouton "Transférer")
            if ($action === 'mark_ready' && $order->type === 'room_service') {
                $roomNumber = $order->room ? $order->room->room_number : null;
                $guestName = $order->guest ? $order->guest->name : null;

                $body = "Commande #{$order->order_number} prête en cuisine.";
                if ($roomNumber) {
                    $body .= " (Chambre {$roomNumber})";
                }

                $data = [
                    'type' => 'order_status',
                    'order_id' => (string) $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'screen' => 'AdminOrders',
                    'room_number' => $roomNumber ?? '',
                    'guest_name' => $guestName ?? '',
                ];

                app(FirebaseNotificationService::class)->sendToKitchenStaff(
                    $order->enterprise_id ?? $user->enterprise_id,
                    '✅ Commande prête',
                    $body,
                    $data
                );
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification error (order status API): ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut de la commande mis à jour.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_name,
            ],
        ], 200);
    }

    /**
     * Notifier explicitement le personnel "Service en chambre" qu'une commande est prête à livrer.
     * Déclenché par un bouton dédié dans l'app mobile (statut doit être 'ready').
     */
    public function notifyRoomService(Request $request, $id)
    {
        $user = $request->user();
        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l\'hôtel',
            ], 403);
        }

        $order = Order::with(['room', 'guest'])->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        if ($order->enterprise_id !== $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable pour cet établissement',
            ], 404);
        }

        if ($order->status !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est disponible que pour les commandes avec le statut "Prête".',
            ], 400);
        }

        $roomNumber = $order->room ? $order->room->room_number : null;
        $guestName = $order->guest ? $order->guest->name : null;

        $title = '🛎 Livraison à effectuer';
        $body = "Commande #{$order->order_number} prête.";
        if ($roomNumber) {
            $body .= " → Chambre {$roomNumber}";
        }
        if ($guestName) {
            $body .= " ({$guestName})";
        }

        $data = [
            'type' => 'room_service_transfer',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'screen' => 'AdminOrders',
            'room_number' => $roomNumber ?? '',
            'guest_name' => $guestName ?? '',
        ];

        try {
            $firebaseService = app(FirebaseNotificationService::class);

            // Cibler UNIQUEMENT les admins + staff "Service en chambre"
            // (pas la cuisine, pas les autres départements)
            $recipients = \App\Models\User::where('enterprise_id', $order->enterprise_id)
                ->where(function ($q) {
                    $q->where('role', 'admin')
                        ->orWhere(function ($q2) {
                            $q2->where('role', 'staff')
                                ->where('department', 'Service en chambre');
                        });
                })
                ->get();

            // Envoi FCM à chaque destinataire
            foreach ($recipients as $recipient) {
                $firebaseService->sendToUser($recipient, $title, $body, $data);
            }

            // Stockage en base (fallback polling garanti)
            foreach ($recipients as $recipient) {
                \App\Models\Notification::create([
                    'user_id' => $recipient->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'room_service_transfer',
                    'data' => $data,
                    'is_read' => false,
                ]);
            }

            Log::info("room_service_transfer: envoyé à {$recipients->count()} destinataire(s) (service en chambre + admins). Commande #{$order->order_number}");
        } catch (\Exception $e) {
            Log::error('Firebase notification error (notify-room-service): ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Le service en chambre a été notifié.',
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
