<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeisureCategory;
use App\Models\Enterprise;

class LeisureSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        foreach ($enterprises as $enterprise) {
            if (LeisureCategory::where('enterprise_id', $enterprise->id)->whereNull('parent_id')->exists()) {
                continue;
            }
            $sport = LeisureCategory::create([
                'enterprise_id' => $enterprise->id,
                'parent_id' => null,
                'name' => 'Sport',
                'description' => 'Réservation de parcours, courts, matériel et accès salle de sport.',
                'type' => 'sport',
                'display_order' => 0,
            ]);
            $loisirs = LeisureCategory::create([
                'enterprise_id' => $enterprise->id,
                'parent_id' => null,
                'name' => 'Loisirs',
                'description' => 'Spa, bien-être et autres activités de loisirs.',
                'type' => 'loisirs',
                'display_order' => 1,
            ]);

            $sportChildren = [
                ['name' => 'Golf & Tennis', 'description' => 'Réservation Tee-time, courts de tennis et location de matériel.', 'type' => 'golf_tennis', 'display_order' => 0],
                ['name' => 'Sport & Fitness', 'description' => 'Horaires de la salle et réservation de coach personnel.', 'type' => 'fitness', 'display_order' => 1],
            ];
            foreach ($sportChildren as $data) {
                LeisureCategory::create([
                    'enterprise_id' => $enterprise->id,
                    'parent_id' => $sport->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'display_order' => $data['display_order'],
                ]);
            }

            $loisirsChildren = [
                ['name' => 'Spa & Bien-être', 'description' => 'Carte des soins et réservation des créneaux de massage.', 'type' => 'spa', 'display_order' => 0],
            ];
            foreach ($loisirsChildren as $data) {
                LeisureCategory::create([
                    'enterprise_id' => $enterprise->id,
                    'parent_id' => $loisirs->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'display_order' => $data['display_order'],
                ]);
            }

            echo "✅ Bien-être, Sport & Loisirs (Sport + Loisirs + activités) créés pour : {$enterprise->name}\n";
        }
    }
}
