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

        // 8 véhicules : nom, type, places, ordre, prix/jour (FCFA), prix demi-journée (FCFA)
        $vehicles = [
            ['Berline Premium', 'berline', 4, 0, 45000, 28000],
            ['Berline Confort', 'berline', 5, 1, 40000, 25000],
            ['SUV 5 places', 'suv', 5, 2, 55000, 35000],
            ['SUV 7 places', 'suv', 7, 3, 75000, 48000],
            ['Minibus', 'minibus', 12, 4, 120000, 75000],
            ['Van 8 places', 'van', 8, 5, 85000, 52000],
            ['Véhicule Prestige', 'other', 4, 6, 90000, 55000],
            ['Limousine', 'other', 6, 7, 150000, 90000],
        ];

        foreach ($enterprises as $enterprise) {
            $existing = Vehicle::withoutGlobalScope('enterprise')
                ->where('enterprise_id', $enterprise->id)
                ->ordered()
                ->get();
            foreach ($vehicles as $index => $data) {
                if (isset($existing[$index])) {
                    $existing[$index]->update([
                        'name' => $data[0],
                        'vehicle_type' => $data[1],
                        'number_of_seats' => $data[2],
                        'display_order' => $data[3],
                        'price_per_day' => $data[4],
                        'price_half_day' => $data[5],
                    ]);
                } else {
                    Vehicle::withoutGlobalScope('enterprise')->create([
                        'enterprise_id' => $enterprise->id,
                        'name' => $data[0],
                        'vehicle_type' => $data[1],
                        'number_of_seats' => $data[2],
                        'display_order' => $data[3],
                        'price_per_day' => $data[4],
                        'price_half_day' => $data[5],
                        'image' => null,
                        'is_available' => true,
                    ]);
                }
            }
            echo "   ✅ 8 véhicules (prix jour / demi-journée) pour : {$enterprise->name}\n";
        }
    }
}
