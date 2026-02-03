<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "\n🌱 DÉBUT DU SEEDING...\n\n";

        // 1. Créer les entreprises
        echo "📍 Création des entreprises...\n";
        $this->call(EnterpriseSeeder::class);
        echo "\n";

        // 2. Créer les utilisateurs (Super Admin + admins + staff + guests)
        echo "👥 Création des utilisateurs...\n";
        $this->call(UserSeeder::class);
        echo "\n";

        // 3. Créer les chambres
        echo "🏨 Création des chambres...\n";
        $this->call(RoomSeeder::class);
        echo "\n";

        // 4. Créer les menus (catégories + articles)
        echo "🍽️ Création des menus...\n";
        $this->call(MenuSeeder::class);
        echo "\n";

        // 5. Créer les restaurants
        echo "🍷 Création des restaurants...\n";
        $this->call(RestaurantSeeder::class);
        echo "\n";

        // 6. Créer les services spa
        echo "💆 Création des services spa...\n";
        $this->call(SpaSeeder::class);
        echo "\n";

        // 7. Créer les excursions
        echo "🏖️ Création des excursions...\n";
        $this->call(ExcursionSeeder::class);
        echo "\n";

        // 8. Créer les services de blanchisserie
        echo "👔 Création des services blanchisserie...\n";
        $this->call(LaundrySeeder::class);
        echo "\n";

        // 9. Créer les services palace
        echo "👑 Création des services palace...\n";
        $this->call(PalaceSeeder::class);
        echo "\n";

        echo "✅ SEEDING TERMINÉ AVEC SUCCÈS !\n\n";
        
        echo "📊 RÉSUMÉ DES DONNÉES CRÉÉES :\n";
        echo "   - 5 Entreprises (Hôtels)\n";
        echo "   - 1 Super Admin + 5 Admins + 15 Staff + 25 Guests = 46 Utilisateurs\n";
        echo "   - 40 Chambres (8 par hôtel)\n";
        echo "   - 85 Articles de menu (17 par hôtel)\n";
        echo "   - 15 Restaurants/Bars (3 par hôtel)\n";
        echo "   - 40 Services Spa (8 par hôtel)\n";
        echo "   - 20 Excursions (4 par hôtel)\n";
        echo "   - 40 Services Blanchisserie (8 par hôtel)\n";
        echo "   - 40 Services Palace (8 par hôtel)\n";
        echo "\n🎉 Base de données prête pour les tests !\n\n";
    }
}
