<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaundryService;
use App\Models\Enterprise;

class LaundryServiceSeeder extends Seeder
{
    public function run(): void
    {
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        if (!$enterprise) return;

        $services = [
            ['name' => 'Lavage + Repassage chemise', 'category' => 'washing', 'price' => 2500, 'turnaround_hours' => 24],
            ['name' => 'Lavage + Repassage pantalon', 'category' => 'washing', 'price' => 3000, 'turnaround_hours' => 24],
            ['name' => 'Lavage + Repassage robe', 'category' => 'washing', 'price' => 4000, 'turnaround_hours' => 24],
            ['name' => 'Nettoyage à sec costume', 'category' => 'dry_cleaning', 'price' => 8000, 'turnaround_hours' => 48],
            ['name' => 'Nettoyage à sec manteau', 'category' => 'dry_cleaning', 'price' => 10000, 'turnaround_hours' => 48],
            ['name' => 'Repassage uniquement', 'category' => 'ironing', 'price' => 1500, 'turnaround_hours' => 12],
            ['name' => 'Service Express (4h)', 'category' => 'express', 'price' => 5000, 'turnaround_hours' => 4],
        ];

        foreach ($services as $i => $data) {
            LaundryService::create(array_merge($data, ['enterprise_id' => $enterprise->id, 'display_order' => $i + 1]));
        }
        
        $this->command->info('✅ 7 services blanchisserie créés !');
    }
}
