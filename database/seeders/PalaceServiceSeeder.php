<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PalaceService;
use App\Models\Enterprise;

class PalaceServiceSeeder extends Seeder
{
    public function run(): void
    {
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        if (!$enterprise) return;

        $services = [
            ['name' => 'Conciergerie 24/7', 'category' => 'concierge', 'price' => null, 'price_on_request' => true, 'is_premium' => false],
            ['name' => 'Transfert Aéroport', 'category' => 'transport', 'price' => 25000, 'price_on_request' => false, 'is_premium' => false],
            ['name' => 'Location Voiture avec Chauffeur', 'category' => 'transport', 'price' => 50000, 'price_on_request' => false, 'is_premium' => true],
            ['name' => 'Majordome Personnel', 'category' => 'butler', 'price' => 100000, 'price_on_request' => false, 'is_premium' => true],
            ['name' => 'Organisation Événement Privé', 'category' => 'vip', 'price' => null, 'price_on_request' => true, 'is_premium' => true],
            ['name' => 'Réservation Restaurant Externe', 'category' => 'concierge', 'price' => null, 'price_on_request' => true, 'is_premium' => false],
            ['name' => 'Visites guidées personnalisées', 'category' => 'concierge', 'price' => null, 'price_on_request' => true, 'is_premium' => false],
            ['name' => 'Assistance médecin', 'category' => 'concierge', 'price' => null, 'price_on_request' => true, 'is_premium' => false],
            ['name' => 'Urgence sécurité', 'category' => 'concierge', 'price' => null, 'price_on_request' => true, 'is_premium' => false],
        ];

        foreach ($services as $i => $data) {
            PalaceService::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'name' => $data['name'],
                ],
                array_merge($data, ['display_order' => $i + 1, 'description' => 'Service de luxe pour votre confort.', 'status' => 'available'])
            );
        }

        $this->command->info('✅ Services palace (dont Exploration & Mobilité) créés !');
    }
}
