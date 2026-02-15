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

        if ($enterprises->isEmpty()) {
            echo "⚠️ Aucune entreprise. Créez d'abord les entreprises (EnterpriseSeeder).\n";
            return;
        }

        foreach ($enterprises as $enterprise) {
            $sport = LeisureCategory::withoutGlobalScope('enterprise')->firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'parent_id' => null,
                    'type' => 'sport',
                ],
                [
                    'name' => 'Sport',
                    'description' => 'Réservation de parcours, courts, matériel et accès salle de sport.',
                    'display_order' => 0,
                ]
            );
            $loisirs = LeisureCategory::withoutGlobalScope('enterprise')->firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'parent_id' => null,
                    'type' => 'loisirs',
                ],
                [
                    'name' => 'Loisirs',
                    'description' => 'Spa, bien-être et autres activités de loisirs.',
                    'display_order' => 1,
                ]
            );

            $sportChildren = [
                ['name' => 'Golf', 'description' => 'Réservation Tee-time et location de matériel golf.', 'type' => 'golf', 'display_order' => 0],
                ['name' => 'Tennis', 'description' => 'Réservation de courts et location de matériel tennis.', 'type' => 'tennis', 'display_order' => 1],
                ['name' => 'Sport & Fitness', 'description' => 'Horaires de la salle et réservation de coach personnel.', 'type' => 'fitness', 'display_order' => 2],
                ['name' => 'Piscine', 'description' => 'Accès piscine et créneaux nage.', 'type' => 'other', 'display_order' => 3],
                ['name' => 'Yoga & Pilates', 'description' => 'Cours et séances yoga, pilates.', 'type' => 'other', 'display_order' => 4],
                ['name' => 'Running & VTT', 'description' => 'Parcours running et VTT, location de matériel.', 'type' => 'other', 'display_order' => 5],
            ];
            foreach ($sportChildren as $data) {
                LeisureCategory::withoutGlobalScope('enterprise')->firstOrCreate(
                    [
                        'enterprise_id' => $enterprise->id,
                        'parent_id' => $sport->id,
                        'type' => $data['type'],
                        'name' => $data['name'],
                    ],
                    [
                        'description' => $data['description'],
                        'display_order' => $data['display_order'],
                    ]
                );
            }

            $loisirsChildren = [
                ['name' => 'Spa & Bien-être', 'description' => 'Carte des soins et réservation des créneaux de massage.', 'type' => 'spa', 'display_order' => 0],
            ];
            foreach ($loisirsChildren as $data) {
                LeisureCategory::withoutGlobalScope('enterprise')->firstOrCreate(
                    [
                        'enterprise_id' => $enterprise->id,
                        'parent_id' => $loisirs->id,
                        'type' => $data['type'],
                        'name' => $data['name'],
                    ],
                    [
                        'description' => $data['description'],
                        'display_order' => $data['display_order'],
                    ]
                );
            }

            echo "✅ Bien-être, Sport & Loisirs (Sport + Loisirs + activités) pour : {$enterprise->name}\n";
        }
    }
}
