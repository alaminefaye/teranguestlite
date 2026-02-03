<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PalaceService;
use App\Models\Enterprise;

class PalaceSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $services = [
            ['Conciergerie VIP', 'concierge', 'Service de conciergerie personnalisé 24/7', 50000, false, true],
            ['Transfert Aéroport', 'transport', 'Transfert privé aéroport - hôtel', 25000, false, false],
            ['Location Voiture avec Chauffeur', 'transport', 'Service de chauffeur privé pour la journée', 75000, false, true],
            ['Organisation Événement', 'vip', 'Organisation d\'événements privés', null, true, true],
            ['Service Majordome', 'butler', 'Service de majordome personnel', 100000, false, true],
            ['Baby-sitting', 'concierge', 'Service de garde d\'enfants qualifié', 15000, false, false],
            ['Pressing Express', 'concierge', 'Service de pressing en moins de 2h', 10000, false, false],
            ['Billetterie Spectacles', 'concierge', 'Réservation billets spectacles et concerts', null, true, false],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($services as $data) {
                PalaceService::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data[0],
                    'category' => $data[1],
                    'description' => $data[2],
                    'price' => $data[3],
                    'price_on_request' => $data[4],
                    'is_premium' => $data[5],
                    'status' => 'available',
                ]);
            }
            echo "✅ Services Palace créés pour : {$enterprise->name}\n";
        }
    }
}
