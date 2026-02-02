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
        ];

        foreach ($services as $i => $data) {
            PalaceService::create(array_merge($data, ['enterprise_id' => $enterprise->id, 'display_order' => $i + 1, 'description' => 'Service de luxe pour votre confort.']));
        }
        
        $this->command->info('✅ 6 services palace créés !');
    }
}
