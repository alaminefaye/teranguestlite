<?php

namespace Database\Seeders;

use App\Models\Enterprise;
use App\Models\User;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏨 Création des données de démonstration...');

        // 1. Créer une entreprise (hôtel)
        $enterprise = Enterprise::create([
            'name' => 'King Fahd Palace Hotel',
            'address' => 'Route de la Corniche Ouest',
            'phone' => '+221 33 869 69 69',
            'email' => 'contact@kingfahdpalace.sn',
            'city' => 'Dakar',
            'country' => 'Sénégal',
            'status' => 'active',
        ]);
        $this->command->info('✅ Entreprise créée : ' . $enterprise->name);

        // 2. Créer un admin pour cet hôtel
        $admin = User::create([
            'name' => 'Admin King Fahd',
            'email' => 'admin@kingfahd.sn',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'enterprise_id' => $enterprise->id,
            'email_verified_at' => now(),
        ]);
        $this->command->info('✅ Admin hôtel créé : ' . $admin->email . ' / password');

        // 3. Créer du staff
        $staff = [
            ['name' => 'Réceptionniste 1', 'email' => 'reception@kingfahd.sn', 'department' => 'reception'],
            ['name' => 'Housekeeping Manager', 'email' => 'housekeeping@kingfahd.sn', 'department' => 'housekeeping'],
            ['name' => 'Room Service Manager', 'email' => 'roomservice@kingfahd.sn', 'department' => 'room_service'],
        ];

        foreach ($staff as $s) {
            User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password'),
                'role' => 'staff',
                'enterprise_id' => $enterprise->id,
                'department' => $s['department'],
                'email_verified_at' => now(),
            ]);
        }
        $this->command->info('✅ Staff créé : ' . count($staff) . ' membres');

        // 4. Créer des chambres
        $rooms = [
            // Étage 1
            ['room_number' => '101', 'floor' => 1, 'type' => 'single', 'price' => 75000, 'capacity' => 1],
            ['room_number' => '102', 'floor' => 1, 'type' => 'double', 'price' => 100000, 'capacity' => 2],
            ['room_number' => '103', 'floor' => 1, 'type' => 'double', 'price' => 100000, 'capacity' => 2],
            
            // Étage 2
            ['room_number' => '201', 'floor' => 2, 'type' => 'suite', 'price' => 150000, 'capacity' => 3],
            ['room_number' => '202', 'floor' => 2, 'type' => 'suite', 'price' => 150000, 'capacity' => 3],
            ['room_number' => '203', 'floor' => 2, 'type' => 'deluxe', 'price' => 200000, 'capacity' => 4],
            
            // Étage 3
            ['room_number' => '301', 'floor' => 3, 'type' => 'deluxe', 'price' => 200000, 'capacity' => 4],
            ['room_number' => '302', 'floor' => 3, 'type' => 'presidential', 'price' => 500000, 'capacity' => 6],
        ];

        $amenitiesOptions = [
            ['wifi', 'tv', 'ac', 'minibar'],
            ['wifi', 'tv', 'ac', 'minibar', 'safe'],
            ['wifi', 'tv', 'ac', 'minibar', 'safe', 'balcony'],
            ['wifi', 'tv', 'ac', 'minibar', 'safe', 'balcony', 'bathtub', 'desk'],
            ['wifi', 'tv', 'ac', 'minibar', 'safe', 'balcony', 'bathtub', 'shower', 'hairdryer', 'phone', 'ironing', 'desk'],
        ];

        foreach ($rooms as $index => $r) {
            $amenities = $amenitiesOptions[min($index % 5, 4)];
            
            Room::create([
                'enterprise_id' => $enterprise->id,
                'room_number' => $r['room_number'],
                'floor' => $r['floor'],
                'type' => $r['type'],
                'status' => 'available',
                'price_per_night' => $r['price'],
                'capacity' => $r['capacity'],
                'description' => 'Chambre confortable avec vue sur l\'océan.',
                'amenities' => $amenities,
            ]);
        }
        $this->command->info('✅ Chambres créées : ' . count($rooms));

        // 5. Créer des guests et réservations
        $guests = [
            ['name' => 'Jean Dupont', 'email' => 'jean.dupont@example.com'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@example.com'],
            ['name' => 'Pierre Bernard', 'email' => 'pierre.bernard@example.com'],
        ];

        $createdGuests = [];
        foreach ($guests as $g) {
            $guest = User::create([
                'name' => $g['name'],
                'email' => $g['email'],
                'password' => Hash::make('password'),
                'role' => 'guest',
                'enterprise_id' => $enterprise->id,
                'email_verified_at' => now(),
            ]);
            $createdGuests[] = $guest;
        }
        $this->command->info('✅ Guests créés : ' . count($guests));

        // Créer des réservations
        $roomsList = Room::where('enterprise_id', $enterprise->id)->get();
        
        foreach ($createdGuests as $index => $guest) {
            $room = $roomsList[$index];
            
            Reservation::create([
                'enterprise_id' => $enterprise->id,
                'user_id' => $guest->id,
                'room_id' => $room->id,
                'reservation_number' => 'RES-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'check_in' => now()->addDays($index * 3),
                'check_out' => now()->addDays($index * 3 + 3),
                'guests_count' => rand(1, 2),
                'status' => $index === 0 ? 'confirmed' : 'pending',
                'total_price' => $room->price_per_night * 3,
                'special_requests' => 'Lit supplémentaire pour enfant',
            ]);
        }
        $this->command->info('✅ Réservations créées : ' . count($createdGuests));

        $this->command->info('');
        $this->command->info('🎉 Données de démonstration créées avec succès !');
        $this->command->info('');
        $this->command->info('📧 Comptes créés :');
        $this->command->info('   Super Admin : admin@admin.com / passer123');
        $this->command->info('   Admin Hôtel : admin@kingfahd.sn / password');
        $this->command->info('   Staff : reception@kingfahd.sn / password');
        $this->command->info('   Guest : jean.dupont@example.com / password');
    }
}
