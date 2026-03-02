<?php

namespace App\Services;

use App\Models\AmenityCategory;
use App\Models\AmenityItem;
use App\Models\Enterprise;
use App\Models\LaundryService;
use App\Models\LeisureCategory;
use App\Models\PalaceService;

/**
 * Crée les données par défaut pour une nouvelle entreprise (Sport/Loisirs,
 * Blanchisserie, Services Palace, Amenities & Conciergerie).
 * Appelé automatiquement à la création d'une entreprise.
 */
class DefaultDataForEnterpriseService
{
    public static function seedForEnterprise(Enterprise $enterprise): void
    {
        $instance = new self();
        $instance->seedLeisure($enterprise);
        $instance->seedLaundry($enterprise);
        $instance->seedPalace($enterprise);
        $instance->seedAmenities($enterprise);
    }

    protected function seedLeisure(Enterprise $enterprise): void
    {
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
            ['name' => 'Squash', 'description' => 'Réservation du court de squash et location de raquettes.', 'type' => 'other', 'display_order' => 2],
            ['name' => 'Sport & Fitness', 'description' => 'Horaires de la salle et réservation de coach personnel.', 'type' => 'fitness', 'display_order' => 3],
            ['name' => 'Piscine', 'description' => 'Accès piscine, créneaux nage et horaires.', 'type' => 'other', 'display_order' => 4],
            ['name' => 'Aquagym & Natation', 'description' => 'Cours d\'aquagym et cours de natation.', 'type' => 'other', 'display_order' => 5],
            ['name' => 'Yoga & Pilates', 'description' => 'Cours et séances yoga, pilates.', 'type' => 'other', 'display_order' => 6],
            ['name' => 'Running & VTT', 'description' => 'Parcours running et VTT, location de matériel.', 'type' => 'other', 'display_order' => 7],
            ['name' => 'Beach Volley', 'description' => 'Réservation du terrain et créneaux beach volley.', 'type' => 'other', 'display_order' => 8],
            ['name' => 'Cours collectifs', 'description' => 'Cours de groupe : stretching, cardio, renforcement.', 'type' => 'other', 'display_order' => 9],
            ['name' => 'Terrain de foot', 'description' => 'Réservation du terrain de football.', 'type' => 'other', 'display_order' => 10],
            ['name' => 'Terrain de basket', 'description' => 'Réservation du terrain de basket-ball.', 'type' => 'other', 'display_order' => 11],
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
            ['name' => 'Hammam & Sauna', 'description' => 'Accès hammam et sauna, horaires et réservation.', 'type' => 'other', 'display_order' => 1],
            ['name' => 'Excursions & Découverte', 'description' => 'Activités et excursions proposées par l\'hôtel.', 'type' => 'other', 'display_order' => 2],
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
    }

    protected function seedLaundry(Enterprise $enterprise): void
    {
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
    }

    protected function seedPalace(Enterprise $enterprise): void
    {
        $services = [
            ['Conciergerie VIP', 'concierge', 'Service de conciergerie personnalisé 24/7', 50000, false, true],
            ['Transfert Aéroport', 'transport', 'Transfert privé aéroport - hôtel', 25000, false, false],
            ['Location Voiture avec Chauffeur', 'transport', 'Service de chauffeur privé pour la journée', 75000, false, true],
            ['Organisation Événement', 'vip', 'Organisation d\'événements privés', null, true, true],
            ['Service Majordome', 'butler', 'Service de majordome personnel', 100000, false, true],
            ['Baby-sitting', 'concierge', 'Service de garde d\'enfants qualifié', 15000, false, false],
            ['Pressing Express', 'concierge', 'Service de pressing en moins de 2h', 10000, false, false],
            ['Billetterie Spectacles', 'concierge', 'Réservation billets spectacles et concerts', null, true, false],
        ];
        foreach ($services as $data) {
            PalaceService::create([
                'enterprise_id' => $enterprise->id,
                'name' => $data[0],
                'category' => $data[1],
                'description' => $data[2],
                'price' => $data[3],
                'price_on_request' => $data[4],
                'is_premium' => $data[5],
                'status' => 'available',
            ]);
        }
    }

    protected function seedAmenities(Enterprise $enterprise): void
    {
        $categoriesWithItems = [
            [
                'name' => 'Articles de toilette',
                'display_order' => 0,
                'items' => ['Savon', 'Shampooing', 'Dentifrice', 'Brosse à dents', 'Peigne', 'Serviettes'],
            ],
            [
                'name' => 'Oreillers supplémentaires',
                'display_order' => 1,
                'items' => ['Oreiller supplémentaire'],
            ],
            [
                'name' => 'Kit de rasage',
                'display_order' => 2,
                'items' => ['Rasoir', 'Mousse à raser', 'Après-rasage', 'Lames de rechange'],
            ],
            [
                'name' => 'Autre demande',
                'display_order' => 3,
                'items' => [],
            ],
        ];
        foreach ($categoriesWithItems as $catData) {
            $category = AmenityCategory::create([
                'enterprise_id' => $enterprise->id,
                'name' => $catData['name'],
                'display_order' => $catData['display_order'],
            ]);
            foreach ($catData['items'] as $order => $itemName) {
                AmenityItem::create([
                    'amenity_category_id' => $category->id,
                    'name' => $itemName,
                    'display_order' => $order,
                ]);
            }
        }
    }
}
