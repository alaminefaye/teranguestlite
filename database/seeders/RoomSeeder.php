<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Enterprise;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $roomTypes = [
            ['type' => 'single', 'capacity' => 1, 'price' => 65000],
            ['type' => 'single', 'capacity' => 2, 'price' => 75000],
            ['type' => 'double', 'capacity' => 2, 'price' => 85000],
            ['type' => 'double', 'capacity' => 3, 'price' => 95000],
            ['type' => 'deluxe', 'capacity' => 2, 'price' => 120000],
            ['type' => 'deluxe', 'capacity' => 3, 'price' => 135000],
            ['type' => 'suite', 'capacity' => 2, 'price' => 200000],
            ['type' => 'suite', 'capacity' => 4, 'price' => 250000],
            ['type' => 'presidential', 'capacity' => 4, 'price' => 500000],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($roomTypes as $index => $roomData) {
                $roomNumber = ($enterprise->id * 100) + $index + 1;
                
                Room::create([
                    'enterprise_id' => $enterprise->id,
                    'room_number' => (string)$roomNumber,
                    'type' => $roomData['type'],
                    'capacity' => $roomData['capacity'],
                    'price_per_night' => $roomData['price'],
                    'floor' => (int)($index / 3) + 1,
                    'status' => $index < 5 ? 'available' : ($index < 7 ? 'occupied' : 'maintenance'),
                    'description' => "Chambre {$roomData['type']} pour {$roomData['capacity']} personne(s), vue panoramique",
                    'amenities' => ['WiFi', 'TV', 'Climatisation', 'Mini-bar', 'Coffre-fort'],
                ]);
            }
            echo "✅ Chambres créées pour : {$enterprise->name}\n";
        }
    }
}
