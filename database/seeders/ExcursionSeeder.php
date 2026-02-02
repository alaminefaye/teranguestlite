<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Excursion;
use App\Models\Enterprise;

class ExcursionSeeder extends Seeder
{
    public function run(): void
    {
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        if (!$enterprise) return;

        $excursions = [
            ['name' => 'Visite Île de Gorée', 'type' => 'cultural', 'price_adult' => 15000, 'price_child' => 8000, 'duration_hours' => 4, 'departure_time' => '09:00', 'is_featured' => true, 'max_participants' => 20],
            ['name' => 'Tour de la ville de Dakar', 'type' => 'city_tour', 'price_adult' => 12000, 'price_child' => 6000, 'duration_hours' => 3, 'departure_time' => '10:00', 'is_featured' => true, 'max_participants' => 30],
            ['name' => 'Lac Rose (Lac Retba)', 'type' => 'adventure', 'price_adult' => 20000, 'price_child' => 10000, 'duration_hours' => 6, 'departure_time' => '08:00', 'is_featured' => true, 'max_participants' => 15],
            ['name' => 'Marché Sandaga & Soumbédioune', 'type' => 'cultural', 'price_adult' => 8000, 'price_child' => 4000, 'duration_hours' => 2, 'departure_time' => '15:00', 'is_featured' => false, 'max_participants' => 25],
            ['name' => 'Plage de Ngor', 'type' => 'relaxation', 'price_adult' => 10000, 'price_child' => 5000, 'duration_hours' => 4, 'departure_time' => '11:00', 'is_featured' => false, 'max_participants' => 20],
            ['name' => 'Village Artisanal de Soumbédioune', 'type' => 'cultural', 'price_adult' => 7000, 'price_child' => 3500, 'duration_hours' => 2, 'departure_time' => '14:00', 'is_featured' => false, 'max_participants' => 30],
        ];

        foreach ($excursions as $i => $data) {
            Excursion::create(array_merge($data, [
                'enterprise_id' => $enterprise->id,
                'description' => 'Découvrez les merveilles du Sénégal avec nos guides expérimentés.',
                'display_order' => $i + 1,
                'status' => 'available',
            ]));
        }
        
        $this->command->info('✅ 6 excursions créées !');
    }
}
