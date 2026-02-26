<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Room;
use App\Services\FirebaseNotificationService;
use App\Services\GuestReservationHelper;

class RoomServiceController extends Controller
{
    /**
     * Liste des catégories de menu
     */
    public function categories(Request $request)
    {
        $query = MenuCategory::with(['menuItems' => function($q) {
            $q->where('is_available', true);
        }])->withCount('menuItems');

        // Filtrer seulement les catégories actives
        $query->where('status', 'active');

        // Recherche
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'display_order' => $category->display_order,
                    'is_available' => $category->status === 'active',
                    'items_count' => $category->menu_items_count,
                ];
            }),
        ], 200);
    }

    /**
     * Liste des articles de menu
     */
    public function items(Request $request)
    {
        $query = MenuItem::with('category');

        // Filtrer par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrer par disponibilité
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->input('per_page', 15);
        $items = $query->ordered()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'formatted_price' => $item->formatted_price,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'preparation_time' => $item->preparation_time,
                    'is_available' => $item->is_available,
                    'category' => [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                    ],
                ];
            }),
            'meta' => [
                'current_page' => $items->currentPage(),
                'from' => $items->firstItem(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'to' => $items->lastItem(),
                'total' => $items->total(),
            ],
            'links' => [
                'first' => $items->url(1),
                'last' => $items->url($items->lastPage()),
                'prev' => $items->previousPageUrl(),
                'next' => $items->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Détails d'un article
     */
    public function show($id)
    {
        $item = MenuItem::with('category')->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'image' => $item->image ? asset('storage/' . $item->image) : null,
                'preparation_time' => $item->preparation_time,
                'preparation_time_text' => $item->preparation_time_text,
                'is_available' => $item->is_available,
                'category' => [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                    'description' => $item->category->description,
                ],
            ],
        ], 200);
    }

    /**
     * Passer une commande
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            'delivery_time' => 'nullable|date',
        ]);

        $user = $request->user();

        // Calculer sous-total et total, préparer les lignes pour order_items
        $subtotal = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);

            if (!$menuItem || !$menuItem->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => "L'article " . ($menuItem->name ?? '') . " n'est plus disponible",
                ], 400);
            }

            $qty = (int) $item['quantity'];
            $lineTotal = $menuItem->price * $qty;
            $subtotal += $lineTotal;

            $itemsData[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'item_description' => $menuItem->description,
                'unit_price' => $menuItem->price,
                'quantity' => $qty,
                'total_price' => $lineTotal,
                'special_requests' => $item['special_instructions'] ?? null,
            ];
        }

        $tax = 0;
        $deliveryFee = 0;
        $total = $subtotal + $tax + $deliveryFee;

        // Réservation active dans la chambre : rattacher la commande au client (Guest) présent
        $stay = GuestReservationHelper::activeStayForUser($user);
        $roomId = $stay !== null ? $stay['room_id'] : null;
        $guestId = $stay !== null ? $stay['guest_id'] : null;

        if (! $roomId && $user->room_number) {
            $room = Room::where('enterprise_id', $user->enterprise_id)
                ->where('room_number', $user->room_number)
                ->first();
            $roomId = $room?->id;
        }

        // Créer la commande : guest_id = client de la réservation active, room_id = chambre
        $order = Order::create([
            'user_id' => $user->id,
            'guest_id' => $guestId,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $roomId,
            'order_number' => $this->generateOrderNumber(),
            'type' => 'room_service',
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'special_instructions' => $request->special_instructions,
        ]);

        foreach ($itemsData as $itemData) {
            $order->orderItems()->create($itemData);
        }

        $order->load('orderItems.menuItem');

        // Envoyer notification push au client de la chambre (tablette/app de la chambre)
        try {
            $firebaseService = app(FirebaseNotificationService::class);
            if ($order->room_id) {
                $firebaseService->sendNewOrderNotificationToRoom($order);
            }
            // Notifier le staff
            $firebaseService->sendToStaffForSection(
                $user->enterprise_id,
                \App\Helpers\StaffSection::ROOM_SERVICE_ORDERS,
                'Nouvelle commande',
                "Nouvelle commande #{$order->order_number} de la chambre {$user->room_number}"
            );
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la commande
            \Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande passée avec succès',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'formatted_total' => $order->formatted_total,
                'status' => $order->status,
                'items' => $order->orderItems->map(function ($orderItem) {
                    return [
                        'menu_item' => [
                            'id' => $orderItem->menu_item_id,
                            'name' => $orderItem->item_name,
                            'image' => $orderItem->menuItem?->image ? asset('storage/' . $orderItem->menuItem->image) : null,
                        ],
                        'quantity' => $orderItem->quantity,
                        'unit_price' => (float) $orderItem->unit_price,
                        'subtotal' => (float) $orderItem->total_price,
                        'special_instructions' => $orderItem->special_requests,
                    ];
                }),
                'created_at' => $order->created_at->toISOString(),
            ],
        ], 201);
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
