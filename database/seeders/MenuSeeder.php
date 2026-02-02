<?php

namespace Database\Seeders;

use App\Models\Enterprise;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🍽️  Création des menus et articles...');

        $enterprise = Enterprise::first();

        if (!$enterprise) {
            $this->command->error('Aucune entreprise trouvée. Exécutez d abord DemoDataSeeder.');
            return;
        }

        // Catégories Room Service
        $roomServiceCategories = [
            ['name' => 'Petit déjeuner', 'type' => 'room_service', 'order' => 1],
            ['name' => 'Plats chauds', 'type' => 'room_service', 'order' => 2],
            ['name' => 'Sandwichs & Salades', 'type' => 'room_service', 'order' => 3],
            ['name' => 'Desserts', 'type' => 'room_service', 'order' => 4],
            ['name' => 'Boissons', 'type' => 'room_service', 'order' => 5],
        ];

        $createdCategories = [];

        foreach ($roomServiceCategories as $cat) {
            $category = MenuCategory::create([
                'enterprise_id' => $enterprise->id,
                'name' => $cat['name'],
                'description' => 'Catégorie ' . $cat['name'],
                'type' => $cat['type'],
                'status' => 'active',
                'display_order' => $cat['order'],
            ]);
            $createdCategories[$cat['name']] = $category;
        }

        $this->command->info('✅ Catégories créées : ' . count($createdCategories));

        // Articles Petit déjeuner
        $breakfastItems = [
            ['name' => 'Continental Breakfast', 'price' => 8000, 'prep' => 15],
            ['name' => 'American Breakfast', 'price' => 12000, 'prep' => 20],
            ['name' => 'Omelette aux choix', 'price' => 6000, 'prep' => 15],
            ['name' => 'Croissant & Café', 'price' => 3500, 'prep' => 5],
            ['name' => 'Pain perdu', 'price' => 5000, 'prep' => 15],
        ];

        foreach ($breakfastItems as $item) {
            MenuItem::create([
                'enterprise_id' => $enterprise->id,
                'category_id' => $createdCategories['Petit déjeuner']->id,
                'name' => $item['name'],
                'description' => 'Délicieux ' . strtolower($item['name']),
                'price' => $item['price'],
                'preparation_time' => $item['prep'],
                'is_available' => true,
                'is_featured' => false,
            ]);
        }

        // Articles Plats chauds
        $hotDishes = [
            ['name' => 'Poulet Yassa', 'price' => 15000, 'prep' => 30],
            ['name' => 'Thiéboudienne', 'price' => 18000, 'prep' => 35],
            ['name' => 'Mafé', 'price' => 16000, 'prep' => 30],
            ['name' => 'Steak frites', 'price' => 20000, 'prep' => 25],
            ['name' => 'Pâtes Carbonara', 'price' => 12000, 'prep' => 20],
        ];

        foreach ($hotDishes as $item) {
            MenuItem::create([
                'enterprise_id' => $enterprise->id,
                'category_id' => $createdCategories['Plats chauds']->id,
                'name' => $item['name'],
                'description' => 'Plat savoureux',
                'price' => $item['price'],
                'preparation_time' => $item['prep'],
                'is_available' => true,
                'is_featured' => true,
            ]);
        }

        // Articles Sandwichs & Salades
        $sandwiches = [
            ['name' => 'Club Sandwich', 'price' => 7000, 'prep' => 10],
            ['name' => 'Burger Royal', 'price' => 9000, 'prep' => 15],
            ['name' => 'Salade César', 'price' => 6500, 'prep' => 10],
            ['name' => 'Salade Niçoise', 'price' => 8000, 'prep' => 10],
        ];

        foreach ($sandwiches as $item) {
            MenuItem::create([
                'enterprise_id' => $enterprise->id,
                'category_id' => $createdCategories['Sandwichs & Salades']->id,
                'name' => $item['name'],
                'description' => 'Frais et savoureux',
                'price' => $item['price'],
                'preparation_time' => $item['prep'],
                'is_available' => true,
                'is_featured' => false,
            ]);
        }

        // Articles Desserts
        $desserts = [
            ['name' => 'Tarte au citron', 'price' => 4000, 'prep' => 5],
            ['name' => 'Tiramisu', 'price' => 4500, 'prep' => 5],
            ['name' => 'Fondant au chocolat', 'price' => 5000, 'prep' => 15],
            ['name' => 'Salade de fruits', 'price' => 3000, 'prep' => 5],
        ];

        foreach ($desserts as $item) {
            MenuItem::create([
                'enterprise_id' => $enterprise->id,
                'category_id' => $createdCategories['Desserts']->id,
                'name' => $item['name'],
                'description' => 'Douceur gourmande',
                'price' => $item['price'],
                'preparation_time' => $item['prep'],
                'is_available' => true,
                'is_featured' => false,
            ]);
        }

        // Articles Boissons
        $drinks = [
            ['name' => 'Coca-Cola', 'price' => 1500, 'prep' => 2],
            ['name' => 'Jus d\'orange frais', 'price' => 2500, 'prep' => 5],
            ['name' => 'Café Expresso', 'price' => 2000, 'prep' => 5],
            ['name' => 'Thé à la menthe', 'price' => 1500, 'prep' => 5],
            ['name' => 'Eau minérale', 'price' => 1000, 'prep' => 1],
        ];

        foreach ($drinks as $item) {
            MenuItem::create([
                'enterprise_id' => $enterprise->id,
                'category_id' => $createdCategories['Boissons']->id,
                'name' => $item['name'],
                'description' => 'Rafraîchissant',
                'price' => $item['price'],
                'preparation_time' => $item['prep'],
                'is_available' => true,
                'is_featured' => false,
            ]);
        }

        $totalItems = MenuItem::count();
        $this->command->info('✅ Articles créés : ' . $totalItems);
        $this->command->info('');
        $this->command->info('🎉 Menus créés avec succès !');
    }
}
