<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpaService;
use App\Models\Enterprise;

class SpaSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $spaServices = [
            ['Massage Relaxant', 'massage', 'Massage complet du corps pour relaxation profonde', 35000, 60],
            ['Massage Sportif', 'massage', 'Massage thérapeutique pour sportifs', 40000, 60],
            ['Soin Visage Hydratant', 'facial', 'Soin du visage avec masque hydratant', 25000, 45],
            ['Soin Visage Anti-âge', 'facial', 'Traitement anti-âge complet', 35000, 60],
            ['Manucure', 'body_treatment', 'Soin des mains et pose de vernis', 15000, 30],
            ['Pédicure', 'body_treatment', 'Soin des pieds avec massage', 18000, 45],
            ['Hammam', 'wellness', 'Séance de hammam traditionnel', 20000, 45],
            ['Sauna', 'wellness', 'Accès au sauna', 15000, 30],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($spaServices as $data) {
                SpaService::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data[0],
                    'category' => $data[1],
                    'description' => $data[2],
                    'price' => $data[3],
                    'duration' => $data[4],
                    'status' => 'available',
                    'is_featured' => rand(0, 1) == 1,
                ]);
            }
            echo "✅ Services Spa créés pour : {$enterprise->name}\n";
        }
    }
}
