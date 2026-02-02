<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Room;
use App\Models\User;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer l'entreprise King Fahd Palace Hotel
        $enterprise = \App\Models\Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        
        if (!$enterprise) {
            $this->command->warn('Entreprise King Fahd Palace Hotel non trouvée. Assurez-vous d\'exécuter DemoDataSeeder en premier.');
            return;
        }

        // Récupérer les chambres
        $rooms = Room::where('enterprise_id', $enterprise->id)->get();

        if ($rooms->isEmpty()) {
            $this->command->warn('Aucune chambre occupée trouvée.');
            return;
        }

        // Récupérer les clients (guests)
        $guests = User::where('enterprise_id', $enterprise->id)
            ->where('role', 'guest')
            ->get();

        if ($guests->isEmpty()) {
            $this->command->warn('Aucun client trouvé.');
            return;
        }

        // Récupérer les articles de menu disponibles
        $menuItems = MenuItem::where('enterprise_id', $enterprise->id)
            ->where('is_available', true)
            ->get();

        if ($menuItems->isEmpty()) {
            $this->command->warn('Aucun article de menu disponible.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'];
        $types = ['room_service', 'restaurant', 'bar'];

        $this->command->info('Création des commandes de test...');

        // Créer 15 commandes
        for ($i = 1; $i <= 15; $i++) {
            $room = $rooms->random();
            $guest = $guests->random();
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];

            // Calculer les dates en fonction du statut
            $createdAt = now()->subDays(rand(0, 7));
            $confirmedAt = in_array($status, ['confirmed', 'preparing', 'ready', 'delivering', 'delivered']) 
                ? $createdAt->copy()->addMinutes(rand(5, 30)) : null;
            $preparingAt = in_array($status, ['preparing', 'ready', 'delivering', 'delivered']) 
                ? $confirmedAt->copy()->addMinutes(rand(10, 30)) : null;
            $readyAt = in_array($status, ['ready', 'delivering', 'delivered']) 
                ? $preparingAt->copy()->addMinutes(rand(15, 45)) : null;
            $deliveringAt = in_array($status, ['delivering', 'delivered']) 
                ? $readyAt->copy()->addMinutes(rand(2, 10)) : null;
            $deliveredAt = $status === 'delivered' 
                ? $deliveringAt->copy()->addMinutes(rand(5, 15)) : null;
            $cancelledAt = $status === 'cancelled' 
                ? $createdAt->copy()->addMinutes(rand(5, 60)) : null;

            // Sélectionner 1 à 5 articles aléatoires
            $selectedItems = $menuItems->random(rand(1, 5));
            
            $subtotal = 0;
            $itemsData = [];

            foreach ($selectedItems as $menuItem) {
                $quantity = rand(1, 3);
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
            $deliveryFee = $type === 'room_service' ? 1000 : 0;
            $total = $subtotal + $tax + $deliveryFee;

            // Créer la commande
            $order = Order::create([
                'enterprise_id' => $enterprise->id,
                'user_id' => $guest->id,
                'room_id' => $room->id,
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'type' => $type,
                'status' => $status,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'special_instructions' => rand(0, 1) ? 'Sans sel, merci.' : null,
                'created_at' => $createdAt,
                'confirmed_at' => $confirmedAt,
                'preparing_at' => $preparingAt,
                'ready_at' => $readyAt,
                'delivering_at' => $deliveringAt,
                'delivered_at' => $deliveredAt,
                'cancelled_at' => $cancelledAt,
            ]);

            // Créer les lignes de commande
            foreach ($itemsData as $itemData) {
                $order->orderItems()->create($itemData);
            }

            $this->command->info("Commande {$order->order_number} créée ({$status})");
        }

        $this->command->info('✅ 15 commandes créées avec succès !');
    }
}
