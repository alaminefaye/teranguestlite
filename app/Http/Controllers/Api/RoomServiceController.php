<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Services\FirebaseNotificationService;

class RoomServiceController extends Controller
{
    /**
     * Liste des catégories de menu
     */
    public function categories(Request $request)
    {
        $query = MenuCategory::with(['items' => function($q) {
            $q->where('is_available', true);
        }])->withCount('items');

        // Filtrer par disponibilité
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

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
                    'is_available' => $category->is_available,
                    'items_count' => $category->items_count,
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

        // Calculer le total
        $total = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            
            if (!$menuItem || !$menuItem->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => "L'article {$menuItem->name} n'est plus disponible",
                ], 400);
            }

            $subtotal = $menuItem->price * $item['quantity'];
            $total += $subtotal;

            $orderItems[] = [
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->price,
                'subtotal' => $subtotal,
                'special_instructions' => $item['special_instructions'] ?? null,
            ];
        }

        // Créer la commande
        $order = Order::create([
            'user_id' => $user->id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $user->room_number,
            'order_number' => $this->generateOrderNumber(),
            'items' => $orderItems,
            'total_amount' => $total,
            'status' => 'pending',
            'special_instructions' => $request->special_instructions,
        ]);

        // Envoyer notification push
        try {
            $firebaseService = app(FirebaseNotificationService::class);
            $firebaseService->sendNewOrderNotification($user, $order);
            
            // Notifier le staff
            $firebaseService->sendToStaff(
                $user->enterprise_id,
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
                'total_amount' => $order->total_amount,
                'formatted_total' => $order->formatted_total,
                'status' => $order->status,
                'items' => collect($orderItems)->map(function ($item) {
                    $menuItem = MenuItem::find($item['menu_item_id']);
                    return [
                        'menu_item' => [
                            'id' => $menuItem->id,
                            'name' => $menuItem->name,
                            'image' => $menuItem->image ? asset('storage/' . $menuItem->image) : null,
                        ],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                        'special_instructions' => $item['special_instructions'],
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
