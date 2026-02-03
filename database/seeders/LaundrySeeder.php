<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaundryService;
use App\Models\Enterprise;

class LaundrySeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = Enterprise::all();

        $services = [
            ['Chemise', 'washing', 'Lavage et repassage chemise', 2000, 24],
            ['Pantalon', 'washing', 'Lavage et repassage pantalon', 2500, 24],
            ['Robe', 'washing', 'Lavage et repassage robe', 3000, 24],
            ['Costume', 'dry_cleaning', 'Nettoyage à sec costume complet', 8000, 48],
            ['Draps', 'washing', 'Lavage draps de lit', 3500, 24],
            ['Serviettes', 'washing', 'Lavage serviettes de bain', 1500, 24],
            ['Repassage Express', 'express', 'Service de repassage express', 5000, 4],
            ['Nettoyage à Sec Délicat', 'dry_cleaning', 'Nettoyage à sec vêtements délicats', 6000, 48],
        ];

        foreach ($enterprises as $enterprise) {
            foreach ($services as $data) {
                LaundryService::create([
                    'enterprise_id' => $enterprise->id,
                    'name' => $data[0],
                    'category' => $data[1],
                    'description' => $data[2],
                    'price' => $data[3],
                    'turnaround_hours' => $data[4],
                    'status' => 'available',
                ]);
            }
            echo "✅ Services Blanchisserie créés pour : {$enterprise->name}\n";
        }
    }
}
