<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpaService;
use App\Models\Enterprise;

class SpaServiceSeeder extends Seeder
{
    public function run(): void
    {
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        
        if (!$enterprise) {
            $this->command->warn('Entreprise King Fahd Palace Hotel non trouvée.');
            return;
        }

        $services = [
            ['name' => 'Massage Traditionnel Sénégalais', 'category' => 'massage', 'price' => 35000, 'duration' => 60, 'is_featured' => true],
            ['name' => 'Massage Suédois', 'category' => 'massage', 'price' => 40000, 'duration' => 60, 'is_featured' => true],
            ['name' => 'Massage aux Pierres Chaudes', 'category' => 'massage', 'price' => 45000, 'duration' => 90, 'is_featured' => true],
            ['name' => 'Massage Relaxant', 'category' => 'massage', 'price' => 30000, 'duration' => 45, 'is_featured' => false],
            ['name' => 'Soin du Visage Hydratant', 'category' => 'facial', 'price' => 25000, 'duration' => 60, 'is_featured' => false],
            ['name' => 'Soin Anti-âge', 'category' => 'facial', 'price' => 35000, 'duration' => 75, 'is_featured' => true],
            ['name' => 'Gommage Corps Complet', 'category' => 'body_treatment', 'price' => 28000, 'duration' => 45, 'is_featured' => false],
            ['name' => 'Enveloppement Corps', 'category' => 'body_treatment', 'price' => 32000, 'duration' => 60, 'is_featured' => false],
            ['name' => 'Hammam & Gommage', 'category' => 'wellness', 'price' => 38000, 'duration' => 90, 'is_featured' => true],
            ['name' => 'Sauna Privatif', 'category' => 'wellness', 'price' => 15000, 'duration' => 30, 'is_featured' => false],
        ];

        foreach ($services as $index => $serviceData) {
            SpaService::create(array_merge($serviceData, [
                'enterprise_id' => $enterprise->id,
                'description' => 'Service de qualité supérieure pour votre détente et bien-être.',
                'status' => 'available',
                'display_order' => $index + 1,
            ]));
        }

        $this->command->info('✅ 10 services spa créés avec succès !');
    }
}
