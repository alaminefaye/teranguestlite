<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Enterprise;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $restaurantsData = [
            [
                'name' => 'Restaurant Gastronomique',
                'type' => 'restaurant',
                'capacity' => 80,
                'description' => 'Restaurant gastronomique avec vue sur l\'océan',
                'opening_hours' => [
                    'monday' => ['open' => '12:00', 'close' => '23:00'],
                    'tuesday' => ['open' => '12:00', 'close' => '23:00'],
                    'wednesday' => ['open' => '12:00', 'close' => '23:00'],
                    'thursday' => ['open' => '12:00', 'close' => '23:00'],
                    'friday' => ['open' => '12:00', 'close' => '23:00'],
                    'saturday' => ['open' => '12:00', 'close' => '00:00'],
                    'sunday' => ['open' => '12:00', 'close' => '23:00'],
                ],
            ],
            [
                'name' => 'Le Rooftop Bar',
                'type' => 'bar',
                'capacity' => 50,
                'description' => 'Bar sur le toit avec cocktails signature',
                'opening_hours' => [
                    'monday' => ['open' => '18:00', 'close' => '02:00'],
                    'tuesday' => ['open' => '18:00', 'close' => '02:00'],
                    'wednesday' => ['open' => '18:00', 'close' => '02:00'],
                    'thursday' => ['open' => '18:00', 'close' => '02:00'],
                    'friday' => ['open' => '18:00', 'close' => '03:00'],
                    'saturday' => ['open' => '18:00', 'close' => '03:00'],
                    'sunday' => ['open' => '18:00', 'close' => '02:00'],
                ],
            ],
            [
                'name' => 'La Terrasse',
                'type' => 'cafe',
                'capacity' => 40,
                'description' => 'Espace lounge avec ambiance détente',
                'opening_hours' => [
                    'monday' => ['open' => '10:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                    'thursday' => ['open' => '10:00', 'close' => '22:00'],
                    'friday' => ['open' => '10:00', 'close' => '22:00'],
                    'saturday' => ['open' => '10:00', 'close' => '22:00'],
                    'sunday' => ['open' => '10:00', 'close' => '22:00'],
                ],
            ],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($restaurantsData as $data) {
                Restaurant::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                    'opening_hours' => $data['opening_hours'],
                    'status' => 'open',
                    'has_terrace' => true,
                    'has_wifi' => true,
                    'has_live_music' => $data['type'] === 'bar',
                    'accepts_reservations' => true,
                ]);
            }
            echo "✅ Restaurants créés pour : {$enterprise->name}\n";
        }
    }
}
