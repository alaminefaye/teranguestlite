<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['room', 'orderItems'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistiques
        $stats = [
            'total' => Order::where('user_id', auth()->id())->count(),
            'pending' => Order::where('user_id', auth()->id())->pending()->count(),
            'in_progress' => Order::where('user_id', auth()->id())
                ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivering'])
                ->count(),
            'delivered' => Order::where('user_id', auth()->id())->delivered()->count(),
        ];

        return view('pages.guest.orders.index', [
            'title' => 'Mes Commandes',
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function show(Order $order): View
    {
        // Vérifier que la commande appartient à l'utilisateur
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $order->load(['room', 'orderItems']);

        return view('pages.guest.orders.show', [
            'title' => 'Commande #' . $order->order_number,
            'order' => $order,
        ]);
    }

    public function reorder(Order $order)
    {
        // Vérifier que la commande appartient à l'utilisateur
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $order->load('orderItems');

        // Créer un tableau d'articles pour le panier
        $cartItems = [];
        foreach ($order->orderItems as $item) {
            if ($item->menuItem && $item->menuItem->is_available) {
                $cartItems[] = [
                    'menu_item_id' => $item->menu_item_id,
                    'quantity' => $item->quantity,
                ];
            }
        }

        // Stocker dans la session
        session(['cart' => $cartItems]);

        return redirect()->route('guest.room-service.cart')
            ->with('success', 'Articles ajoutés au panier !');
    }
}
