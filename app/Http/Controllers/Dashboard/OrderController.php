<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'guest', 'room']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Recherche par numéro de commande ou nom client
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($subQ) use ($request) {
                      $subQ->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $stats = [
            'total' => Order::count(),
            'pending' => Order::pending()->count(),
            'confirmed' => Order::confirmed()->count(),
            'preparing' => Order::preparing()->count(),
            'ready' => Order::ready()->count(),
            'delivering' => Order::delivering()->count(),
            'delivered' => Order::delivered()->count(),
            'cancelled' => Order::cancelled()->count(),
        ];

        return view('pages.dashboard.orders.index', [
            'title' => 'Commandes',
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $menuItems = MenuItem::with('category')
            ->available()
            ->ordered()
            ->get()
            ->groupBy('category.name');

        $rooms = Room::available()
            ->orWhere('status', 'occupied')
            ->orderBy('room_number', 'asc')
            ->get();

        return view('pages.dashboard.orders.create', [
            'title' => 'Créer une commande',
            'menuItems' => $menuItems,
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'type' => 'required|in:room_service,restaurant,bar,spa',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        // Lier au guest si la chambre a un séjour en cours (pour les notifications push client)
        $reservation = Reservation::where('enterprise_id', $validated['enterprise_id'])
            ->currentStay($validated['room_id'])
            ->first();
        if ($reservation && $reservation->guest_id) {
            $validated['guest_id'] = $reservation->guest_id;
        }

        // Calculer les totaux
        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $quantity = $item['quantity'];
            $price = $menuItem->price;
            $total = $price * $quantity;

            $subtotal += $total;

            $itemsData[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'item_description' => $menuItem->description,
                'unit_price' => $price,
                'quantity' => $quantity,
                'total_price' => $total,
            ];
        }

        $tax = $subtotal * 0.18; // TVA 18%
        $deliveryFee = $validated['type'] === 'room_service' ? 1000 : 0; // Frais livraison pour room service
        $total = $subtotal + $tax + $deliveryFee;

        $validated['subtotal'] = $subtotal;
        $validated['tax'] = $tax;
        $validated['delivery_fee'] = $deliveryFee;
        $validated['total'] = $total;

        // Créer la commande
        $order = Order::create($validated);

        // Créer les lignes de commande
        foreach ($itemsData as $itemData) {
            $order->orderItems()->create($itemData);
        }

        return redirect()->route('dashboard.orders.show', $order)
            ->with('success', 'Commande créée avec succès ! Numéro: ' . $order->order_number);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'guest', 'room', 'orderItems.menuItem']);

        return view('pages.dashboard.orders.show', [
            'title' => 'Commande #' . $order->order_number,
            'order' => $order,
        ]);
    }

    public function edit(Order $order): View
    {
        // On ne peut modifier que les commandes pending
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('error', 'Impossible de modifier cette commande.');
        }

        $order->load('orderItems');

        $menuItems = MenuItem::with('category')
            ->available()
            ->ordered()
            ->get()
            ->groupBy('category.name');

        $rooms = Room::available()
            ->orWhere('status', 'occupied')
            ->orderBy('room_number', 'asc')
            ->get();

        return view('pages.dashboard.orders.edit', [
            'title' => 'Modifier commande #' . $order->order_number,
            'order' => $order,
            'menuItems' => $menuItems,
            'rooms' => $rooms,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('error', 'Impossible de modifier cette commande.');
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        // Recalculer les totaux
        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $quantity = $item['quantity'];
            $price = $menuItem->price;
            $total = $price * $quantity;

            $subtotal += $total;

            $itemsData[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'item_description' => $menuItem->description,
                'unit_price' => $price,
                'quantity' => $quantity,
                'total_price' => $total,
            ];
        }

        $tax = $subtotal * 0.18;
        $deliveryFee = $order->type === 'room_service' ? 1000 : 0;
        $total = $subtotal + $tax + $deliveryFee;

        // Mettre à jour la commande
        $order->update([
            'room_id' => $validated['room_id'],
            'special_instructions' => $validated['special_instructions'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
        ]);

        // Supprimer les anciennes lignes et recréer
        $order->orderItems()->delete();
        foreach ($itemsData as $itemData) {
            $order->orderItems()->create($itemData);
        }

        return redirect()->route('dashboard.orders.show', $order)
            ->with('success', 'Commande mise à jour avec succès !');
    }

    public function destroy(Order $order): RedirectResponse
    {
        // On ne peut supprimer que les commandes pending ou cancelled
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Impossible de supprimer cette commande.');
        }

        $order->delete();

        return redirect()->route('dashboard.orders.index')
            ->with('success', 'Commande supprimée avec succès !');
    }

    // Actions de workflow
    public function confirm(Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Seules les commandes en attente peuvent être confirmées.');
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Commande confirmée avec succès !');
    }

    public function prepare(Order $order): RedirectResponse
    {
        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Seules les commandes confirmées peuvent être préparées.');
        }

        $order->update([
            'status' => 'preparing',
            'preparing_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Préparation de la commande commencée !');
    }

    public function markReady(Order $order): RedirectResponse
    {
        if ($order->status !== 'preparing') {
            return back()->with('error', 'Seules les commandes en préparation peuvent être marquées comme prêtes.');
        }

        $order->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Commande prête pour livraison !');
    }

    public function deliver(Order $order): RedirectResponse
    {
        if ($order->status !== 'ready') {
            return back()->with('error', 'Seules les commandes prêtes peuvent être livrées.');
        }

        $order->update([
            'status' => 'delivering',
            'delivering_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Commande en cours de livraison !');
    }

    public function complete(Order $order): RedirectResponse
    {
        if ($order->status !== 'delivering') {
            return back()->with('error', 'Seules les commandes en livraison peuvent être complétées.');
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Commande livrée avec succès !');
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return back()->with('error', 'Cette commande ne peut pas être annulée.');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
        $this->notifyOrderStatusToClient($order);

        return back()->with('success', 'Commande annulée.');
    }

    private function notifyOrderStatusToClient(Order $order): void
    {
        try {
            $order->refresh();
            $order->load(['user', 'guest']);
            $sent = app(FirebaseNotificationService::class)->sendOrderStatusNotificationToClient($order);
            if (! $sent) {
                \Log::warning('Order status notification not sent', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'guest_id' => $order->guest_id,
                    'user_id' => $order->user_id,
                    'status' => $order->status,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Firebase order status notification: ' . $e->getMessage());
        }
    }
}
