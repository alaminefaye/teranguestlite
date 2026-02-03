<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;

class OrderController extends Controller
{
    /**
     * Liste des commandes de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = Order::where('user_id', $request->user()->id);

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'formatted_total' => $order->formatted_total,
                    'status' => $order->status,
                    'status_label' => $order->status_name,
                    'items_count' => count($order->items),
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
     * Détails d'une commande
     */
    public function show(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        // Charger les détails des articles
        $itemsWithDetails = collect($order->items)->map(function ($item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            return [
                'id' => $item['menu_item_id'],
                'menu_item' => [
                    'id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'image' => $menuItem->image ? asset('storage/' . $menuItem->image) : null,
                    'description' => $menuItem->description,
                ],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'special_instructions' => $item['special_instructions'] ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'formatted_total' => $order->formatted_total,
                'status' => $order->status,
                'status_label' => $order->status_name,
                'special_instructions' => $order->special_instructions,
                'items' => $itemsWithDetails,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
            ],
        ], 200);
    }

    /**
     * Recommander une commande
     */
    public function reorder(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        // Vérifier la disponibilité des articles
        $unavailableItems = [];
        foreach ($order->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            if (!$menuItem || !$menuItem->is_available) {
                $unavailableItems[] = $menuItem ? $menuItem->name : 'Article supprimé';
            }
        }

        if (!empty($unavailableItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Certains articles ne sont plus disponibles',
                'unavailable_items' => $unavailableItems,
            ], 400);
        }

        // Créer une nouvelle commande avec les mêmes articles
        $newOrder = Order::create([
            'user_id' => $request->user()->id,
            'enterprise_id' => $request->user()->enterprise_id,
            'room_id' => $request->user()->room_number,
            'order_number' => $this->generateOrderNumber(),
            'items' => $order->items,
            'total_amount' => $order->total_amount,
            'status' => 'pending',
            'special_instructions' => $order->special_instructions,
        ]);

        // Notification
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendNewOrderNotification($request->user(), $newOrder);
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande re-passée avec succès',
            'data' => [
                'id' => $newOrder->id,
                'order_number' => $newOrder->order_number,
                'total_amount' => $newOrder->total_amount,
                'formatted_total' => $newOrder->formatted_total,
                'status' => $newOrder->status,
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
