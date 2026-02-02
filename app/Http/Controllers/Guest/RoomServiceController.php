<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoomServiceController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::with(['menuItems' => function($query) {
            $query->available()->ordered();
        }])
        ->active()
        ->where('type', 'room_service')
        ->ordered()
        ->get();

        return view('pages.guest.room-service.index', [
            'title' => 'Room Service',
            'categories' => $categories,
        ]);
    }

    public function show(MenuItem $menuItem): View
    {
        $menuItem->load('category');

        if (!$menuItem->is_available) {
            return redirect()->route('guest.room-service.index')
                ->with('error', 'Cet article n\'est pas disponible.');
        }

        return view('pages.guest.room-service.show', [
            'title' => $menuItem->name,
            'item' => $menuItem,
        ]);
    }

    public function cart(): View
    {
        return view('pages.guest.room-service.cart', [
            'title' => 'Mon Panier',
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        if (!$user->room_number) {
            return back()->with('error', 'Aucune chambre associée à votre compte.');
        }

        // Trouver la chambre de l'utilisateur
        $room = \App\Models\Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        if (!$room) {
            return back()->with('error', 'Chambre introuvable.');
        }

        $validated['enterprise_id'] = $user->enterprise_id;
        $validated['user_id'] = $user->id;
        $validated['room_id'] = $room->id;
        $validated['type'] = 'room_service';
        $validated['status'] = 'pending';

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

        $tax = $subtotal * 0.18;
        $deliveryFee = 1000; // Frais livraison pour room service
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

        return redirect()->route('guest.orders.show', $order)
            ->with('success', 'Votre commande a été passée avec succès ! Numéro: ' . $order->order_number);
    }
}
