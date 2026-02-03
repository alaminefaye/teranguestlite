<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enterprise;
use Illuminate\Support\Facades\Storage;

class EnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = [
            [
                'name' => 'King Fahd Palace',
                'address' => 'Almadies, Dakar, Sénégal',
                'phone' => '+221 33 869 69 69',
                'email' => 'contact@kingfahdpalace.sn',
                'city' => 'Dakar',
                'country' => 'Sénégal',
                'status' => 'active',
            ],
            [
                'name' => 'Radisson Blu Hotel',
                'address' => 'Route de la Corniche Ouest, Dakar',
                'phone' => '+221 33 869 20 00',
                'email' => 'info@radissonblu-dakar.com',
                'city' => 'Dakar',
                'country' => 'Sénégal',
                'status' => 'active',
            ],
            [
                'name' => 'Teranga Hotel & Lodge',
                'address' => 'Rue de Thann, Plateau, Dakar',
                'phone' => '+221 33 823 09 21',
                'email' => 'contact@terangahotel.sn',
                'city' => 'Dakar',
                'country' => 'Sénégal',
                'status' => 'active',
            ],
            [
                'name' => 'Pullman Dakar Teranga',
                'address' => 'Place de l\'Indépendance, Dakar',
                'phone' => '+221 33 849 90 00',
                'email' => 'info@pullman-dakar.com',
                'city' => 'Dakar',
                'country' => 'Sénégal',
                'status' => 'active',
            ],
            [
                'name' => 'Onomo Hotel Airport',
                'address' => 'Aéroport International Blaise Diagne',
                'phone' => '+221 33 865 60 00',
                'email' => 'dakar@onomohotel.com',
                'city' => 'Diass',
                'country' => 'Sénégal',
                'status' => 'active',
            ],
        ];

        foreach ($enterprises as $enterprise) {
            Enterprise::create($enterprise);
            echo "✅ Entreprise créée : {$enterprise['name']}\n";
        }
    }
}
