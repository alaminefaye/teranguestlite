<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Enterprise;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('passer123'),
            'role' => 'super_admin',
            'enterprise_id' => null,
            'department' => 'Direction Générale',
            'must_change_password' => false,
        ]);
        echo "✅ Super Admin créé\n";

        $enterprises = Enterprise::all();

        foreach ($enterprises as $enterprise) {
            // Admin de l'entreprise
            User::create([
                'name' => "Administrateur {$enterprise->name}",
                'email' => "admin@" . \Str::slug($enterprise->name) . ".com",
                'password' => Hash::make('passer123'),
                'role' => 'admin',
                'enterprise_id' => $enterprise->id,
                'department' => 'Direction',
                'must_change_password' => false,
            ]);

            // Staff (3 par entreprise)
            $departments = ['Réception', 'Service en chambre', 'Restaurant'];
            foreach ($departments as $index => $dept) {
                User::create([
                    'name' => "Staff {$dept} - {$enterprise->name}",
                    'email' => "staff" . ($index + 1) . "@" . \Str::slug($enterprise->name) . ".com",
                    'password' => Hash::make('passer123'),
                    'role' => 'staff',
                    'enterprise_id' => $enterprise->id,
                    'department' => $dept,
                    'must_change_password' => false,
                ]);
            }

            // Guests (5 par entreprise)
            for ($i = 1; $i <= 5; $i++) {
                $roomNumber = 100 + $i;
                User::create([
                    'name' => "Client Chambre {$roomNumber}",
                    'email' => "guest{$i}@" . \Str::slug($enterprise->name) . ".com",
                    'password' => Hash::make('passer123'),
                    'role' => 'guest',
                    'enterprise_id' => $enterprise->id,
                    'room_number' => (string)$roomNumber,
                    'must_change_password' => false,
                ]);
            }

            echo "✅ Utilisateurs créés pour : {$enterprise->name}\n";
        }
    }
}
