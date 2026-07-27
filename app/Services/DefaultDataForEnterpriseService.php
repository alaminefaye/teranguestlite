<?php

namespace App\Services;

use App\Models\AmenityCategory;
use App\Models\AmenityItem;
use App\Models\Enterprise;
use App\Models\Excursion;
use App\Models\GuideCategory;
use App\Models\GuideItem;
use App\Models\LaundryService;
use App\Models\LeisureCategory;
use App\Models\PalaceService;

/**
 * Crée les données par défaut pour une nouvelle entreprise (Sport/Loisirs,
 * Blanchisserie, Services Palace, Amenities & Conciergerie, Excursions).
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
        $instance->seedGuides($enterprise);
        $instance->seedExcursions($enterprise);
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

    protected function seedGuides(Enterprise $enterprise): void
    {
        $category = GuideCategory::withoutGlobalScope('enterprise')->firstOrCreate(
            [
                'enterprise_id' => $enterprise->id,
                'category_type' => 'useful_numbers',
            ],
            [
                'name' => ['fr' => 'Numéros utiles', 'en' => 'Useful numbers'],
                'order' => 1,
                'is_active' => true,
            ]
        );

        $items = [
            ['title' => 'BAR PISCINE', 'phone' => '2010'],
            ['title' => 'BAR SAINT-LOUIS', 'phone' => '2009'],
            ['title' => 'BASE NAUTIQ', 'phone' => '2702'],
            ['title' => 'BOUTIQUE', 'phone' => '2013'],
            ['title' => 'INFIRMERIE', 'phone' => '2016'],
            ['title' => 'SALLE DE SPORT', 'phone' => '2015'],
            ['title' => 'SDT EXCURSION', 'phone' => '2008'],
            ['title' => 'RECEPTION', 'phone' => '2500'],
            ['title' => 'RELATION CLIENTELE FRAM', 'phone' => '2017'],
            ['title' => 'ROOM SERVICE', 'phone' => '2408'],
            ['title' => 'SPA', 'phone' => '2012'],
        ];

        $order = 1;
        foreach ($items as $itemData) {
            GuideItem::withoutGlobalScope('enterprise')->firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'guide_category_id' => $category->id,
                    'phone' => $itemData['phone'],
                ],
                [
                    'title' => ['fr' => $itemData['title'], 'en' => $itemData['title']],
                    'order' => $order++,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedExcursions(Enterprise $enterprise): void
    {
        $excursions = [
            [
                'name' => '½ J Brousse',
                'type' => 'adventure',
                'description' => 'Villages, école, brousse et retour des pêcheurs à Mbour.',
                'price_adult' => 21500,
                'price_child' => 11000,
                'price_adult_eur' => 33.00,
                'price_child_eur' => 17.00,
                'duration_hours' => 4,
                'departure_time' => '08:30',
                'schedule_description' => 'Jours : Vendredi, Lundi, Jeudi, Samedi, Mardi (Matin 08h30-12h00 ou Après-midi 15h00-18h30).',
                'min_participants' => 4,
                'max_participants' => 20,
                'included' => ['Visite de villages & école', 'Retour des pêcheurs à Mbour', 'Repas & 1/2 eau inclus'],
                'not_included' => ['Boissons hors 1/2 eau', 'Dépenses personnelles'],
            ],
            [
                'name' => 'Joal - Fadiouth',
                'type' => 'cultural',
                'description' => 'Île aux coquillages, sécherie de poisson, balade traditionnelle en pirogue.',
                'price_adult' => 25000,
                'price_child' => 13000,
                'price_adult_eur' => 38.00,
                'price_child_eur' => 19.00,
                'duration_hours' => 5,
                'departure_time' => '08:30',
                'schedule_description' => 'Jours : Dimanche, Mercredi, Jeudi, Vendredi (08h30 - 13h00).',
                'min_participants' => 2,
                'max_participants' => 25,
                'included' => ['Visite île aux coquillages', 'Sécherie de poisson', 'Balade en pirogue', 'Guide local'],
                'not_included' => ['Repas', 'Boissons'],
            ],
            [
                'name' => 'Réserve de Bandia',
                'type' => 'adventure',
                'description' => 'Safari animalier dans la réserve : girafes, antilopes, singes, rhinocéros, etc.',
                'price_adult' => 30000,
                'price_child' => 15000,
                'price_adult_eur' => 45.73,
                'price_child_eur' => 22.86,
                'duration_hours' => 3,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Vendredi, Dimanche, Lundi, Mardi, Jeudi, Samedi (Créneaux : 08h00-11h00 ou 08h30-11h30).',
                'min_participants' => 2,
                'max_participants' => 15,
                'included' => ['Safari animalier en camion 4x4', 'Entrée à la réserve', 'Guide spécialiste'],
                'not_included' => ['Déjeuner', 'Photos personnelles'],
            ],
            [
                'name' => 'Lac Rose',
                'type' => 'relaxation',
                'description' => 'Découverte du Lac Retba (Lac salé) et expérience de flottaison.',
                'price_adult' => 20000,
                'price_child' => 10000,
                'price_adult_eur' => 30.50,
                'price_child_eur' => 15.25,
                'duration_hours' => 5,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Vendredi (08h00 - 13h00).',
                'min_participants' => 2,
                'max_participants' => 20,
                'included' => ['Transport', 'Visite du Lac salé', 'Flottaison'],
                'not_included' => ['Repas', 'Balade en quad en option'],
            ],
            [
                'name' => 'Soirée Brousse',
                'type' => 'cultural',
                'description' => 'Lutte sérère traditionnelle, dîner typique et grand spectacle en brousse.',
                'price_adult' => 40000,
                'price_child' => 20000,
                'price_adult_eur' => 61.00,
                'price_child_eur' => 30.50,
                'duration_hours' => 5,
                'departure_time' => '18:00',
                'schedule_description' => 'Jours : Mardi, Jeudi, Mercredi, Vendredi (Soirée brousse).',
                'min_participants' => 4,
                'max_participants' => 50,
                'included' => ['Dîner traditionnel', 'Spectacle de lutte sérère', 'Animations & Boissons incluses'],
                'not_included' => ['Achats personnels'],
            ],
            [
                'name' => 'Lagune Somone',
                'type' => 'relaxation',
                'description' => 'Balade en pirogue à la découverte de la lagune et de la réserve ornithologique des mangroves.',
                'price_adult' => 20000,
                'price_child' => 10000,
                'price_adult_eur' => 30.50,
                'price_child_eur' => 15.25,
                'duration_hours' => 3,
                'departure_time' => '09:00',
                'schedule_description' => 'Jours : Dimanche, Jeudi (09h00 - 12h00).',
                'min_participants' => 2,
                'max_participants' => 20,
                'included' => ['Balade en pirogue', 'Guide naturaliste', 'Accès lagune & mangrove'],
                'not_included' => ['Repas', 'Boissons'],
            ],
            [
                'name' => 'Gorée / Lac Rose',
                'type' => 'cultural',
                'description' => 'Combiné sur une journée : Île historique de Gorée, musées et découverte du Lac Rose.',
                'price_adult' => 45000,
                'price_child' => 23000,
                'price_adult_eur' => 68.60,
                'price_child_eur' => 34.30,
                'duration_hours' => 11,
                'departure_time' => '07:00',
                'schedule_description' => 'Jours : Mardi, Jeudi, Dimanche (07h00 - 18h00).',
                'min_participants' => 4,
                'max_participants' => 30,
                'included' => ['Traversée en chaloupe', 'Entrées musées Gorée', 'Visite Lac Rose', 'Déjeuner inclus'],
                'not_included' => ['Boissons personnelles'],
            ],
            [
                'name' => 'Dakar - Gorée',
                'type' => 'city_tour',
                'description' => 'Île historique de Gorée, musées, tour panoramique de la ville de Dakar et marchés artisanaux.',
                'price_adult' => 40000,
                'price_child' => 20000,
                'price_adult_eur' => 61.00,
                'price_child_eur' => 30.50,
                'duration_hours' => 12,
                'departure_time' => '07:00',
                'schedule_description' => 'Jours : Dimanche, Mardi, Jeudi (07h00 - 19h00).',
                'min_participants' => 2,
                'max_participants' => 30,
                'included' => ['Traversée chaloupe Gorée', 'Visite guidée Dakar & musées', 'Déjeuner inclus'],
                'not_included' => ['Souvenirs personnels'],
            ],
            [
                'name' => 'Gorée en ½ Journée',
                'type' => 'cultural',
                'description' => 'Visite guidée de l\'île mémoire de Gorée et de ses musées.',
                'price_adult' => 28000,
                'price_child' => 14000,
                'price_adult_eur' => 42.50,
                'price_child_eur' => 21.25,
                'duration_hours' => 4,
                'departure_time' => '07:00',
                'schedule_description' => 'Jours : Lundi, Jeudi, Vendredi, Dimanche (07h00-15h00 ou 08h00-13h00).',
                'min_participants' => 2,
                'max_participants' => 25,
                'included' => ['Traversée en chaloupe', 'Guide historique', 'Entrées musées'],
                'not_included' => ['Repas'],
            ],
            [
                'name' => 'Ranch aux Lions',
                'type' => 'adventure',
                'description' => 'Expérience exceptionnelle : marche encadrée très proche des lions en liberté.',
                'price_adult' => 35000,
                'price_child' => 20000,
                'price_adult_eur' => 53.00,
                'price_child_eur' => 26.50,
                'duration_hours' => 4,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Lundi, Jeudi, Vendredi, Dimanche (08h00 - 11h30 / 08h00 - 13h00).',
                'min_participants' => 2,
                'max_participants' => 15,
                'included' => ['Entrée ranch aux lions', 'Marche accompagnée par des rangers profs'],
                'not_included' => ['Repas', 'Photos pro'],
            ],
            [
                'name' => 'Bandia / Lions',
                'type' => 'adventure',
                'description' => 'Journée complète d\'aventure : Safari dans la Réserve de Bandia et marche au Ranch des Lions.',
                'price_adult' => 57500,
                'price_child' => 30000,
                'price_adult_eur' => 87.50,
                'price_child_eur' => 43.75,
                'duration_hours' => 10,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Samedi, Dimanche, Mercredi (08h00 - 18h00).',
                'min_participants' => 4,
                'max_participants' => 20,
                'included' => ['Safari réserve de Bandia', 'Entrée & marche ranch des lions', 'Déjeuner inclus'],
                'not_included' => ['Boissons'],
            ],
            [
                'name' => 'Saloum - Marlodj',
                'type' => 'adventure',
                'description' => 'Marché brousse, balade en pirogue dans les bolongs du Saloum, île aux oiseaux, village sérère et baobab.',
                'price_adult' => 38000,
                'price_child' => 19000,
                'price_adult_eur' => 58.00,
                'price_child_eur' => 29.00,
                'duration_hours' => 10,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Samedi, Dimanche, Mercredi (08h00 - 18h00).',
                'min_participants' => 4,
                'max_participants' => 25,
                'included' => ['Balade pirogue bolongs', 'Visite île de Marlodj & village', 'Repas & 1/2 eau inclus'],
                'not_included' => ['Boissons hors 1/2 eau'],
            ],
            [
                'name' => 'Lac Rose, Dunes & Plage',
                'type' => 'relaxation',
                'description' => 'Lac Retba salé, parcours des dunes de l\'étape finale du Paris-Dakar et détente sur la plage.',
                'price_adult' => 35000,
                'price_child' => 18000,
                'price_adult_eur' => 53.00,
                'price_child_eur' => 26.50,
                'duration_hours' => 8,
                'departure_time' => '09:00',
                'schedule_description' => 'Jours : Mardi, Mercredi (09h00 - 17h00).',
                'min_participants' => 2,
                'max_participants' => 30,
                'included' => ['Visite Lac Rose & dunes', 'Accès plage', 'Déjeuner inclus'],
                'not_included' => ['Balade en quad en supplément'],
            ],
            [
                'name' => 'Somone / Oiseaux Exotiques',
                'type' => 'relaxation',
                'description' => 'Combiné balade en pirogue dans la mangrove de Somone et visite du parc des oiseaux exotiques.',
                'price_adult' => 35000,
                'price_child' => 18000,
                'price_adult_eur' => 53.00,
                'price_child_eur' => 26.50,
                'duration_hours' => 4,
                'departure_time' => '09:00',
                'schedule_description' => 'Jours : Samedi, Lundi, Mardi, Jeudi, Vendredi (09h00 - 12h00 / 08h30 - 13h00).',
                'min_participants' => 2,
                'max_participants' => 25,
                'included' => ['Pirogue lagune Somone', 'Entrée parc oiseaux exotiques', 'Guide'],
                'not_included' => ['Repas'],
            ],
            [
                'name' => 'Marché Brousse ½ J',
                'type' => 'cultural',
                'description' => 'Immersion typique dans un marché local hebdomadaire de brousse.',
                'price_adult' => 20000,
                'price_child' => 10000,
                'price_adult_eur' => 30.48,
                'price_child_eur' => 15.24,
                'duration_hours' => 4,
                'departure_time' => '08:30',
                'schedule_description' => 'Jours : Jeudi, Mercredi, Samedi (08h30 - 13h00).',
                'min_participants' => 2,
                'max_participants' => 20,
                'included' => ['Transport', 'Guide accompagnateur local'],
                'not_included' => ['Achats personnels'],
            ],
            [
                'name' => 'Journée Saint-Louis',
                'type' => 'cultural',
                'description' => 'Tour de la ville historique de Saint-Louis en calèche, musée de l\'Aéropostale et quartier des pêcheurs.',
                'price_adult' => 70000,
                'price_child' => 35000,
                'price_adult_eur' => 107.00,
                'price_child_eur' => 53.50,
                'duration_hours' => 12,
                'departure_time' => '07:00',
                'schedule_description' => 'Jours : Lundi, Jeudi (07h00 - 19h00).',
                'min_participants' => 4,
                'max_participants' => 20,
                'included' => ['Tour de ville en calèche', 'Visite musée Aéropostale', 'Quartier des pêcheurs', 'Déjeuner inclus'],
                'not_included' => ['Boissons'],
            ],
            [
                'name' => '2 Jrs Désert Lompoul / St Louis',
                'type' => 'adventure',
                'description' => 'Nuit en bivouac sous tentes maures dans le désert de Lompoul, balade à dromadaire et visite de Saint-Louis.',
                'price_adult' => 110000,
                'price_child' => 55000,
                'price_adult_eur' => 167.50,
                'price_child_eur' => 83.75,
                'duration_hours' => 29,
                'departure_time' => '13:30',
                'schedule_description' => 'Jours : Lundi, Jeudi (Départ 13h30 - Retour le lendemain 18h00). 2 Journées.',
                'min_participants' => 4,
                'max_participants' => 20,
                'included' => ['Bivouac désert Lompoul', 'Balade à dromadaire', 'Tentes maures', 'Repas inclus', 'Tour St-Louis'],
                'not_included' => ['Boissons hors repas'],
            ],
            [
                'name' => 'Parc Oiseaux Exotiques',
                'type' => 'relaxation',
                'description' => 'Visite guidée et observation d\'espèces d\'oiseaux exotiques rares.',
                'price_adult' => 20000,
                'price_child' => 10000,
                'price_adult_eur' => 30.48,
                'price_child_eur' => 15.24,
                'duration_hours' => 2,
                'departure_time' => '09:00',
                'schedule_description' => 'Jours : Tous les jours (09h00 - 11h00).',
                'min_participants' => 1,
                'max_participants' => 30,
                'included' => ['Entrée au parc', 'Guide ornithologique'],
                'not_included' => ['Transport'],
            ],
            [
                'name' => 'Pêche en Mer',
                'type' => 'adventure',
                'description' => 'Session de pêche en mer artisanale tous les jours avec équipement complet.',
                'price_adult' => 20000,
                'price_child' => 10000,
                'price_adult_eur' => 30.50,
                'price_child_eur' => 15.25,
                'duration_hours' => 4,
                'departure_time' => '08:00',
                'schedule_description' => 'Jours : Tous les jours (08h00 à 12h00 / 13h00). (30,50 euros / 20 000 FCFA par personne).',
                'min_participants' => 1,
                'max_participants' => 10,
                'included' => ['Matériel de pêche', 'Bateau & équipage'],
                'not_included' => ['Boissons'],
            ],
        ];

        $order = 1;
        foreach ($excursions as $data) {
            Excursion::withoutGlobalScope('enterprise')->firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'name' => ['fr' => $data['name'], 'en' => $data['name']],
                ],
                [
                    'type' => $data['type'],
                    'description' => ['fr' => $data['description'], 'en' => $data['description']],
                    'price_adult' => $data['price_adult'],
                    'price_child' => $data['price_child'],
                    'price_adult_eur' => $data['price_adult_eur'] ?? null,
                    'price_child_eur' => $data['price_child_eur'] ?? null,
                    'duration_hours' => $data['duration_hours'],
                    'departure_time' => $data['departure_time'],
                    'schedule_description' => $data['schedule_description'],
                    'min_participants' => $data['min_participants'],
                    'max_participants' => $data['max_participants'],
                    'included' => $data['included'],
                    'not_included' => $data['not_included'],
                    'status' => 'available',
                    'is_featured' => $order <= 5,
                    'display_order' => $order++,
                    'is_active' => true,
                ]
            );
        }
    }
}
