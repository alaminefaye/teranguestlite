<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AmenityCategory;
use App\Models\AmenityItem;
use App\Models\Enterprise;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $categoriesWithItems = [
            [
                'name' => 'Articles de toilette',
                'display_order' => 0,
                'items' => [
                    'Savon',
                    'Shampooing',
                    'Dentifrice',
                    'Brosse à dents',
                    'Peigne',
                    'Serviettes',
                ],
            ],
            [
                'name' => 'Oreillers supplémentaires',
                'display_order' => 1,
                'items' => [
                    'Oreiller supplémentaire',
                ],
            ],
            [
                'name' => 'Kit de rasage',
                'display_order' => 2,
                'items' => [
                    'Rasoir',
                    'Mousse à raser',
                    'Après-rasage',
                    'Lames de rechange',
                ],
            ],
            [
                'name' => 'Autre demande',
                'display_order' => 3,
                'items' => [],
            ],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($categoriesWithItems as $catData) {
                $category = AmenityCategory::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $catData['name'],
                    'display_order' => $catData['display_order'],
                ]);

                foreach ($catData['items'] as $order => $itemName) {
                    AmenityItem::create([
                        'amenity_category_id' => $category->id,
                        'name' => $itemName,
                        'display_order' => $order,
                    ]);
                }
            }
            echo "✅ Amenities & Conciergerie créés pour : {$enterprise->name}\n";
        }
    }
}
