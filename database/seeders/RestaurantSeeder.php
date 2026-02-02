<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Enterprise;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        
        if (!$enterprise) {
            $this->command->warn('Entreprise King Fahd Palace Hotel non trouvée.');
            return;
        }

        $restaurants = [
            [
                'name' => 'Le Méditerranéen',
                'type' => 'restaurant',
                'description' => 'Restaurant gastronomique méditerranéen avec vue sur l\'océan. Cuisine raffinée et produits frais.',
                'location' => 'Rez-de-chaussée, vue mer',
                'capacity' => 80,
                'status' => 'open',
                'opening_hours' => [
                    'monday' => ['open' => '12:00', 'close' => '22:30'],
                    'tuesday' => ['open' => '12:00', 'close' => '22:30'],
                    'wednesday' => ['open' => '12:00', 'close' => '22:30'],
                    'thursday' => ['open' => '12:00', 'close' => '22:30'],
                    'friday' => ['open' => '12:00', 'close' => '23:00'],
                    'saturday' => ['open' => '12:00', 'close' => '23:00'],
                    'sunday' => ['open' => '12:00', 'close' => '22:30'],
                ],
                'phone' => '+221 33 123 45 67',
                'email' => 'mediterraneen@kingfahdpalace.sn',
                'has_terrace' => true,
                'has_wifi' => true,
                'has_live_music' => false,
                'accepts_reservations' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Teranga Buffet',
                'type' => 'restaurant',
                'description' => 'Buffet international avec une large sélection de plats sénégalais et internationaux.',
                'location' => 'Premier étage',
                'capacity' => 150,
                'status' => 'open',
                'opening_hours' => [
                    'monday' => ['open' => '07:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '07:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '07:00', 'close' => '22:00'],
                    'thursday' => ['open' => '07:00', 'close' => '22:00'],
                    'friday' => ['open' => '07:00', 'close' => '22:00'],
                    'saturday' => ['open' => '07:00', 'close' => '22:00'],
                    'sunday' => ['open' => '07:00', 'close' => '22:00'],
                ],
                'phone' => '+221 33 123 45 68',
                'email' => 'buffet@kingfahdpalace.sn',
                'has_terrace' => false,
                'has_wifi' => true,
                'has_live_music' => false,
                'accepts_reservations' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Le Piano Bar',
                'type' => 'bar',
                'description' => 'Bar lounge avec ambiance jazz et cocktails signature. Musique live tous les soirs.',
                'location' => 'Lobby, côté mer',
                'capacity' => 50,
                'status' => 'open',
                'opening_hours' => [
                    'monday' => ['open' => '18:00', 'close' => '02:00'],
                    'tuesday' => ['open' => '18:00', 'close' => '02:00'],
                    'wednesday' => ['open' => '18:00', 'close' => '02:00'],
                    'thursday' => ['open' => '18:00', 'close' => '02:00'],
                    'friday' => ['open' => '18:00', 'close' => '03:00'],
                    'saturday' => ['open' => '18:00', 'close' => '03:00'],
                    'sunday' => ['open' => '18:00', 'close' => '02:00'],
                ],
                'phone' => '+221 33 123 45 69',
                'email' => 'pianobar@kingfahdpalace.sn',
                'has_terrace' => true,
                'has_wifi' => true,
                'has_live_music' => true,
                'accepts_reservations' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Pool Bar Oasis',
                'type' => 'pool_bar',
                'description' => 'Bar au bord de la piscine. Cocktails tropicaux, smoothies et snacks légers.',
                'location' => 'Piscine principale',
                'capacity' => 40,
                'status' => 'open',
                'opening_hours' => [
                    'monday' => ['open' => '10:00', 'close' => '19:00'],
                    'tuesday' => ['open' => '10:00', 'close' => '19:00'],
                    'wednesday' => ['open' => '10:00', 'close' => '19:00'],
                    'thursday' => ['open' => '10:00', 'close' => '19:00'],
                    'friday' => ['open' => '10:00', 'close' => '20:00'],
                    'saturday' => ['open' => '10:00', 'close' => '20:00'],
                    'sunday' => ['open' => '10:00', 'close' => '19:00'],
                ],
                'phone' => '+221 33 123 45 70',
                'email' => 'poolbar@kingfahdpalace.sn',
                'has_terrace' => false,
                'has_wifi' => true,
                'has_live_music' => false,
                'accepts_reservations' => false,
                'display_order' => 4,
            ],
            [
                'name' => 'Café Dakar',
                'type' => 'cafe',
                'description' => 'Café cosy proposant des cafés d\'exception, pâtisseries et snacks. Ouvert toute la journée.',
                'location' => 'Lobby, côté jardin',
                'capacity' => 30,
                'status' => 'open',
                'opening_hours' => [
                    'monday' => ['open' => '06:30', 'close' => '20:00'],
                    'tuesday' => ['open' => '06:30', 'close' => '20:00'],
                    'wednesday' => ['open' => '06:30', 'close' => '20:00'],
                    'thursday' => ['open' => '06:30', 'close' => '20:00'],
                    'friday' => ['open' => '06:30', 'close' => '20:00'],
                    'saturday' => ['open' => '06:30', 'close' => '20:00'],
                    'sunday' => ['open' => '06:30', 'close' => '20:00'],
                ],
                'phone' => '+221 33 123 45 71',
                'email' => 'cafe@kingfahdpalace.sn',
                'has_terrace' => true,
                'has_wifi' => true,
                'has_live_music' => false,
                'accepts_reservations' => false,
                'display_order' => 5,
            ],
        ];

        foreach ($restaurants as $restaurantData) {
            $restaurantData['enterprise_id'] = $enterprise->id;
            Restaurant::create($restaurantData);
            $this->command->info("Restaurant '{$restaurantData['name']}' créé");
        }

        $this->command->info('✅ 5 restaurants/bars créés avec succès !');
    }
}
