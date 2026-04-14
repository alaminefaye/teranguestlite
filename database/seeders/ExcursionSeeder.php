<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Excursion;
use App\Models\Enterprise;

class ExcursionSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $excursions = [
            [
                'name' => 'Visite de l\'Île de Gorée',
                'type' => 'cultural',
                'description' => 'Visite guidée de l\'île historique de Gorée',
                'price_adult' => 25000,
                'price_child' => 15000,
                'duration_hours' => 4,
                'departure_time' => '09:00',
                'min_participants' => 4,
                'max_participants' => 20,
                'included' => ['Transport en ferry', 'Guide francophone', 'Entrée musée'],
                'not_included' => ['Repas', 'Boissons', 'Pourboires'],
            ],
            [
                'name' => 'Safari au Parc de Bandia',
                'type' => 'adventure',
                'description' => 'Safari animalier dans la réserve de Bandia',
                'price_adult' => 35000,
                'price_child' => 20000,
                'duration_hours' => 6,
                'departure_time' => '08:00',
                'min_participants' => 2,
                'max_participants' => 15,
                'included' => ['Transport 4x4', 'Guide', 'Entrée parc', 'Eau minérale'],
                'not_included' => ['Déjeuner', 'Photos avec animaux'],
            ],
            [
                'name' => 'Lac Rose et Dunes',
                'type' => 'relaxation',
                'description' => 'Découverte du Lac Retba et des dunes de sable',
                'price_adult' => 30000,
                'price_child' => 18000,
                'duration_hours' => 5,
                'departure_time' => '10:00',
                'min_participants' => 3,
                'max_participants' => 25,
                'included' => ['Transport', 'Guide', 'Balade en quad (option)'],
                'not_included' => ['Repas', 'Quad (supplément)'],
            ],
            [
                'name' => 'City Tour de Dakar',
                'type' => 'city_tour',
                'description' => 'Découverte des sites incontournables de Dakar',
                'price_adult' => 20000,
                'price_child' => 12000,
                'duration_hours' => 4,
                'departure_time' => '09:00',
                'min_participants' => 2,
                'max_participants' => 30,
                'included' => ['Transport', 'Guide', 'Entrées monuments'],
                'not_included' => ['Repas', 'Souvenirs'],
            ],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($excursions as $data) {
                Excursion::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'price_adult' => $data['price_adult'],
                    'price_child' => $data['price_child'],
                    'duration_hours' => $data['duration_hours'],
                    'departure_time' => $data['departure_time'],
                    'min_participants' => $data['min_participants'],
                    'max_participants' => $data['max_participants'],
                    'included' => $data['included'],
                    'not_included' => $data['not_included'],
                    'status' => 'available',
                    'is_featured' => rand(0, 1) == 1,
                ]);
            }
            echo "✅ Excursions créées pour : {$enterprise->name}\n";
        }
    }
}
