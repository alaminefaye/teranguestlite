<?php

namespace Database\Seeders;

use App\Models\Enterprise;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $pricingRows = [
            [
                'formula_type' => 'single',
                'formula_name' => [
                    'fr' => 'Chambre et petit déjeuner',
                    'en' => 'Room and breakfast',
                ],
                'items' => [
                    [
                        'label' => ['fr' => 'Chambre individuelle', 'en' => 'Single room'],
                        'capacity' => 1,
                        'price' => 71000,
                    ],
                    [
                        'label' => ['fr' => 'Chambre 2 personnes', 'en' => 'Double room'],
                        'capacity' => 2,
                        'price' => 92000,
                    ],
                    [
                        'label' => ['fr' => 'Enfants', 'en' => 'Children'],
                        'capacity' => 1,
                        'price' => 25000,
                    ],
                ],
            ],
            [
                'formula_type' => 'double',
                'formula_name' => [
                    'fr' => 'Demi-pension',
                    'en' => 'Half board',
                ],
                'items' => [
                    [
                        'label' => ['fr' => 'Chambre individuelle', 'en' => 'Single room'],
                        'capacity' => 1,
                        'price' => 81000,
                    ],
                    [
                        'label' => ['fr' => 'Chambre 2 personnes', 'en' => 'Double room'],
                        'capacity' => 2,
                        'price' => 122000,
                    ],
                    [
                        'label' => ['fr' => 'Enfants', 'en' => 'Children'],
                        'capacity' => 1,
                        'price' => 30000,
                    ],
                ],
            ],
            [
                'formula_type' => 'suite',
                'formula_name' => [
                    'fr' => 'Pension complète',
                    'en' => 'Full board',
                ],
                'items' => [
                    [
                        'label' => ['fr' => 'Chambre individuelle', 'en' => 'Single room'],
                        'capacity' => 1,
                        'price' => 91000,
                    ],
                    [
                        'label' => ['fr' => 'Chambre 2 personnes', 'en' => 'Double room'],
                        'capacity' => 2,
                        'price' => 132000,
                    ],
                    [
                        'label' => ['fr' => 'Enfants', 'en' => 'Children'],
                        'capacity' => 1,
                        'price' => 35000,
                    ],
                ],
            ],
            [
                'formula_type' => 'deluxe',
                'formula_name' => [
                    'fr' => 'Tout compris',
                    'en' => 'All inclusive',
                ],
                'items' => [
                    [
                        'label' => ['fr' => 'Chambre individuelle', 'en' => 'Single room'],
                        'capacity' => 1,
                        'price' => 99000,
                    ],
                    [
                        'label' => ['fr' => 'Chambre 2 personnes', 'en' => 'Double room'],
                        'capacity' => 2,
                        'price' => 148000,
                    ],
                    [
                        'label' => ['fr' => 'Enfants', 'en' => 'Children'],
                        'capacity' => 1,
                        'price' => 38000,
                    ],
                ],
            ],
        ];

        foreach ($enterprises as $enterprise) {
            Room::where('enterprise_id', $enterprise->id)->delete();

            $index = 1;
            foreach ($pricingRows as $formula) {
                foreach ($formula['items'] as $item) {
                    Room::create([
                    'enterprise_id' => $enterprise->id,
                    'room_number' => sprintf(
                        'T-%d-%02d',
                        $enterprise->id,
                        $index++
                    ),
                    'floor' => null,
                    'type' => $formula['formula_type'],
                    'type_name' => $formula['formula_name'],
                    'capacity' => $item['capacity'],
                    'price_per_night' => $item['price'],
                    'status' => 'available',
                    'description' => $item['label'],
                    'amenities' => [
                        'Tarif journalier',
                        'Prestations incluses selon formule',
                    ],
                ]);
                }
            }

            echo "✅ Tarifs chambres créés pour : {$enterprise->name}\n";
        }
    }
}
