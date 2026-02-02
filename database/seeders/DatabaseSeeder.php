<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer le super admin
        $this->call([
            SuperAdminSeeder::class,
            DemoDataSeeder::class,
            MenuSeeder::class,
            OrderSeeder::class,
            GuestUserSeeder::class,
            RestaurantSeeder::class,
            SpaServiceSeeder::class,
            LaundryServiceSeeder::class,
            PalaceServiceSeeder::class,
            ExcursionSeeder::class,
        ]);
    }
}
