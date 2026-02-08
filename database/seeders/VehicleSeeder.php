<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Enterprise;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        // 8 véhicules avec des types différents : berline, suv, minibus, van, other
        $vehicles = [
            ['Berline Premium', 'berline', 4, 0],
            ['Berline Confort', 'berline', 5, 1],
            ['SUV 5 places', 'suv', 5, 2],
            ['SUV 7 places', 'suv', 7, 3],
            ['Minibus', 'minibus', 12, 4],
            ['Van 8 places', 'van', 8, 5],
            ['Véhicule Prestige', 'other', 4, 6],
            ['Limousine', 'other', 6, 7],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($vehicles as $data) {
                Vehicle::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data[0],
                    'vehicle_type' => $data[1],
                    'number_of_seats' => $data[2],
                    'display_order' => $data[3],
                    'image' => null,
                    'is_available' => true,
                ]);
            }
            echo "   ✅ 8 véhicules créés pour : {$enterprise->name}\n";
        }
    }
}
