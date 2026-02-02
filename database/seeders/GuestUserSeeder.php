<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Enterprise;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer l'entreprise King Fahd Palace Hotel
        $enterprise = Enterprise::where('email', 'contact@kingfahdpalace.sn')->first();
        
        if (!$enterprise) {
            $this->command->warn('Entreprise King Fahd Palace Hotel non trouvée.');
            return;
        }

        // Créer un utilisateur guest de test
        $guest = User::create([
            'name' => 'Client Test',
            'email' => 'guest@test.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'enterprise_id' => $enterprise->id,
            'department' => null,
            'room_number' => '101',
        ]);

        $this->command->info('✅ Utilisateur guest créé avec succès !');
        $this->command->info('Email : guest@test.com');
        $this->command->info('Mot de passe : password');
        $this->command->info('Chambre : 101');
    }
}
