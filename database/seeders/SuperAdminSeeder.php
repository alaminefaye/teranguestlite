<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le super admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('passer123'),
            'role' => 'super_admin',
            'enterprise_id' => null, // Pas associé à une entreprise
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin créé avec succès !');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Mot de passe: passer123');
    }
}
