<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Enterprise;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $categoriesData = [
            ['name' => 'Petit Déjeuner', 'description' => 'Options de petit déjeuner', 'type' => 'room_service'],
            ['name' => 'Plats Principaux', 'description' => 'Plats principaux', 'type' => 'room_service'],
            ['name' => 'Boissons', 'description' => 'Boissons chaudes et froides', 'type' => 'room_service'],
            ['name' => 'Desserts', 'description' => 'Desserts et douceurs', 'type' => 'room_service'],
        ];

        $itemsData = [
            // Petit Déjeuner
            ['Omelette Complète', 'Omelette avec fromage, jambon et légumes', 4500, 15, 'Petit Déjeuner'],
            ['Pancakes Américains', 'Stack de pancakes avec sirop d\'érable', 3500, 12, 'Petit Déjeuner'],
            ['Continental', 'Croissants, pain, confiture, café/thé', 5000, 10, 'Petit Déjeuner'],
            
            // Plats Principaux
            ['Poulet Yassa', 'Poulet mariné aux oignons et citron', 8500, 30, 'Plats Principaux'],
            ['Thiéboudienne', 'Riz au poisson sénégalais', 9000, 35, 'Plats Principaux'],
            ['Steak Frites', 'Steak de boeuf avec frites maison', 12000, 25, 'Plats Principaux'],
            ['Spaghetti Bolognaise', 'Pâtes à la sauce bolognaise', 7500, 20, 'Plats Principaux'],
            ['Salade César', 'Salade avec poulet grillé', 6500, 15, 'Plats Principaux'],
            
            // Boissons
            ['Café Espresso', 'Café italien', 1500, 5, 'Boissons'],
            ['Thé Vert', 'Thé vert nature', 1000, 5, 'Boissons'],
            ['Jus d\'Orange Frais', 'Jus pressé maison', 2500, 8, 'Boissons'],
            ['Coca Cola', 'Boisson gazeuse', 1500, 2, 'Boissons'],
            ['Eau Minérale', 'Eau plate ou gazeuse', 1000, 2, 'Boissons'],
            
            // Desserts
            ['Tiramisu', 'Dessert italien au café', 4000, 10, 'Desserts'],
            ['Crème Brûlée', 'Dessert français classique', 4500, 12, 'Desserts'],
            ['Salade de Fruits', 'Fruits frais de saison', 3000, 8, 'Desserts'],
        ];

        foreach ($enterprises as $enterprise) {
            // Créer les catégories
            $categories = [];
            foreach ($categoriesData as $catData) {
                $category = MenuCategory::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'type' => $catData['type'],
                    'status' => 'active',
                    'display_order' => count($categories) + 1,
                ]);
                $categories[$catData['name']] = $category;
            }

            // Créer les articles
            foreach ($itemsData as $itemData) {
                $category = $categories[$itemData[4]];
                MenuItem::create([
                    'enterprise_id' => $enterprise->id,
                    'category_id' => $category->id,
                    'name' => $itemData[0],
                    'description' => $itemData[1],
                    'price' => $itemData[2],
                    'preparation_time' => $itemData[3],
                    'is_available' => true,
                    'is_featured' => false,
                    'display_order' => 0,
                ]);
            }

            echo "✅ Menu créé pour : {$enterprise->name}\n";
        }
    }
}
