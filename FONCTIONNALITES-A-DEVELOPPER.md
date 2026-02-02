# Teranga Guest — Fonctionnalités à développer (Web & Mobile)

> **Référence visuelle :** interface type King Fahd Palace Hotel (tablette in-room) + barre d'icônes (4 axes transversaux).  
> **Objectif :** application **SaaS multi-tenant** — **web** et **mobile** pour l'assistant digital hôtel / guest in-room.

---

## 1. Architecture SaaS & Rôles

### 1.1 Super Admin (Platform)
**Accès :** admin@admin.com / passer123

**Caractéristiques :**
- **N'est associé à AUCUNE entreprise** (hôtel) — `role = 'super_admin'` et `enterprise_id = null`
- Accès plateforme global — voit **TOUT**
- Peut créer et gérer les entreprises (hôtels)
- Accès à toutes les données de toutes les entreprises

**Dashboard Super Admin :**
- **Vue d'ensemble plateforme** :
  - Nombre total d'entreprises (hôtels)
  - Nombre total d'utilisateurs (tous rôles confondus)
  - Statistiques globales (commandes, réservations, revenus par hôtel)
- **Liste des entreprises (hôtels)** avec statistiques :
  - Nom de l'hôtel
  - Nombre de chambres
  - Nombre d'utilisateurs (admins hôtel, staff, guests)
  - Commandes du mois
  - Revenus du mois
  - Date de création
  - Statut (actif / inactif)
- **Actions :**
  - Créer une nouvelle entreprise (hôtel)
  - Modifier / désactiver une entreprise
  - Voir le détail d'une entreprise (statistiques complètes)
  - Gérer les utilisateurs de chaque entreprise

### 1.2 Admin Hôtel (Enterprise Admin)
**Caractéristiques :**
- Associé à **UNE entreprise (hôtel)** spécifique — `role = 'admin'` et `enterprise_id = X`
- Voit **uniquement les données de son hôtel**
- Peut gérer les utilisateurs de son hôtel (staff, guests)
- Accès aux modules de gestion (room service, spa, réception, etc.)

**Dashboard Admin Hôtel :**
- **Vue d'ensemble de l'hôtel** :
  - Statistiques du jour (check-ins, check-outs, commandes)
  - Chambres occupées / disponibles
  - Commandes en cours (room service, spa, blanchisserie)
  - Revenus du jour / mois
- **Gestion de l'hôtel** :
  - Chambres
  - Menus et services
  - Réservations
  - Commandes (room service, spa, blanchisserie)
  - Staff (réception, housekeeping, etc.)

### 1.3 Staff Hôtel
**Caractéristiques :**
- Associé à **UNE entreprise (hôtel)** et **UN département** — `role = 'staff'`, `enterprise_id = X`, `department = 'reception'|'housekeeping'|...`
- Accès limité selon département (réception, housekeeping, room service, spa, etc.)
- Voit **uniquement les demandes et commandes de son département**

### 1.4 Guest (Client / Invité)
**Caractéristiques :**
- Associé à **UNE entreprise (hôtel)** et **UNE chambre** pendant son séjour — `role = 'guest'`, `enterprise_id = X`
- Accès à l'interface tablette / mobile (8 modules services)
- Voit **uniquement ses commandes et réservations**

---

## 2. Analyse du projet actuel

### Stack technique existante
- **Backend :** Laravel (PHP)
- **Frontend web :** Blade, Vite, Tailwind CSS, Alpine.js
- **Base :** structure TailAdmin (dashboard, auth, formulaires, tables, charts)
- **État :** routes et vues génériques en place ; **aucun module guest (chambre, restaurant, réception, etc.)** encore développé

### Structure existante à réutiliser
- **MenuHelper** (`app/Helpers/MenuHelper.php`) — génération dynamique du menu sidebar
- **Sidebar** (`resources/views/layouts/sidebar.blade.php`) — sidebar avec Alpine.js, icônes SVG, submenus
- **Dashboard** (`resources/views/pages/dashboard/index.blade.php`) — statistiques avec cartes et tableaux
- **Layout** (`resources/views/layouts/app.blade.php`) — layout principal avec header, sidebar, footer

### Ce qui existe déjà (spécifications)
- `SPEC-TERANGA-GUEST-MODULES.md` — 4 modules (Chambre, Restaurant & Bar, Réception, Commandes & Services) avec routes et modèles proposés
- `hoteza-hotpad-fonctionnalites.md` — benchmark Hoteza HotPad (contrôle chambre, room service, blanchisserie, etc.)
- **Migrations SaaS** :
  - `2026_02_02_143953_create_enterprises_table.php` (vide — à compléter)
  - `2026_02_02_144004_add_role_and_enterprise_id_to_users_table.php` (vide — à compléter)

### Ce qu'il faut ajouter pour coller aux photos
- **8 services** comme sur l'interface tablette (Room Service, Restaurants & Bars, Spa, Ménage & Blanchisserie, Services du Palace, Découvrir Dakar, Sorties & Spa Panoramique, Réception & Conciergerie)
- **4 axes transversaux** (icônes barre latérale) : **interaction / sélection**, **favoris**, **contrôle à distance / device**, **sécurité / accès**
- **Header** : logo, notifications, profil, Wi‑Fi, message de bienvenue, bannières dynamiques (ex. Happy Hour)
- **Footer** : heure, date, météo
- **Double plateforme :** **web** (responsive / tablette) et **mobile** (app native ou PWA)
- **Multi-tenant :** chaque entreprise (hôtel) voit uniquement ses données et commandes

---

## 3. Cibles plateformes

| Plateforme | Usage principal | Contraintes |
|------------|------------------|-------------|
| **Web Admin** | Super Admin, Admin Hôtel, Staff | Dashboard, gestion, statistiques — interface claire avec sidebar MenuHelper |
| **Web Tablette** | Guest (tablette en chambre) | Responsive, tactile, thème sombre type "palace" (bleu nuit / or) |
| **Mobile** | Guest (smartphone) — même services que la tablette | iOS + Android (native ou PWA / React Native / Flutter selon choix) |

---

## 4. Les 8 modules services (grille centrale — ref. photo tablette)

Ce sont les **fonctionnalités métier** à développer, identiques sur web et mobile. **Chaque entreprise (hôtel) voit uniquement ses données**.

### 4.1 Room Service
- **Rôle :** commande de repas et boissons en chambre.
- **À développer :**
  - Menus (petit-déjeuner, déjeuner, dîner, snacks, boissons) **filtrés par `enterprise_id`**
  - Panier, quantités, instructions (allergies, remarques)
  - Heure de livraison souhaitée
  - Validation et envoi de la commande
  - Suivi du statut (reçue, en préparation, en livraison, livrée)
  - Historique des commandes du séjour **uniquement pour le guest**

### 4.2 Restaurants & Bars
- **Rôle :** découverte des restaurants et bars de l'hôtel ; réservation et/ou commande.
- **À développer :**
  - Liste des restaurants et bars **de l'hôtel** (horaires, descriptions, photos)
  - Menus par établissement
  - Réservation de table (date, heure, nombre de personnes)
  - Lien avec room service si commande en chambre
  - Événements (ex. Happy Hour) affichés dans les bannières ou sur la fiche

### 4.3 Spa & Bien-être
- **Rôle :** prestations spa et bien-être.
- **À développer :**
  - Catalogue des soins **de l'hôtel** (massages, soins visage, etc.) avec durées et tarifs
  - Réservation de créneaux
  - Informations pratiques (accès, tenue, annulation)
  - Suivi des réservations spa **uniquement pour le guest**

### 4.4 Ménage & Blanchisserie
- **Rôle :** housekeeping et blanchisserie / pressing.
- **À développer :**
  - Demande de ménage (faire la chambre, ne pas déranger)
  - Blanchisserie / pressing : type de service (lavage, repassage, pressing), articles, quantités, instructions, créneau
  - Tarifs et suivi des demandes
  - Option "serviettes / draps en supplément"

### 4.5 Services du Palace
- **Rôle :** services premium et divers (concierge, équipements, baby-sitter, etc.).
- **À développer :**
  - Liste des services **de l'hôtel** (équipements en chambre, baby-sitter, transferts, etc.)
  - Formulaire de demande par type de service
  - Suivi des demandes
  - Informations et tarifs selon l'offre de l'hôtel

### 4.6 Découvrir Dakar (ou la destination)
- **Rôle :** découverte de la ville / destination.
- **À développer :**
  - Contenu éditorial **par hôtel** (lieux, activités, transports, sécurité)
  - Carte interactive (points d'intérêt)
  - Suggestions d'excursions et liens avec le module "Sorties"
  - Infos pratiques (devises, langue, urgences)

### 4.7 Sorties & Spa Panoramique
- **Rôle :** sorties, excursions et accès au spa panoramique (ou équivalent).
- **À développer :**
  - Liste des sorties / excursions **proposées par l'hôtel** (descriptions, tarifs, durée)
  - Réservation d'excursions
  - Infos et réservation pour le spa panoramique (horaires, accès)
  - Suivi des réservations

### 4.8 Réception & Conciergerie
- **Rôle :** contact réception, concierge, demandes générales.
- **À développer :**
  - Contacter la réception (appel, message, chat si prévu)
  - Liste des services / départements **de l'hôtel** (Réception, Concierge, Housekeeping, Maintenance, etc.)
  - Demandes de service (type, message, pièce jointe optionnelle)
  - Suivi des demandes (en attente, en cours, résolu)
  - Alertes / notifications envoyées par l'hôtel (maintenance, événements, infos)
  - Concierge : réservation restaurant externe, taxi, transport

---

## 5. Les 4 axes transversaux (barre d'icônes — ref. première photo)

Ces axes sont des **fonctionnalités transverses** qui s'appliquent à l'ensemble de l'app (web et mobile).

### 5.1 Interaction / Sélection (icône main)
- **Rôle :** interaction utilisateur, sélection, actions rapides.
- **À développer :**
  - Navigation tactile fluide (tap, swipe)
  - Sélection d'options (chambre, réservation, langue)
  - Actions contextuelles (valider, annuler, modifier)
  - Retour tactile cohérent sur tous les écrans

### 5.2 Favoris (icône cœur +)
- **Rôle :** sauvegarder ce que le client aime pour y revenir vite.
- **À développer :**
  - "Ajouter aux favoris" sur : plats, restaurants, soins spa, excursions, lieux Dakar
  - Page / tiroir "Mes favoris" (liste + accès rapide)
  - Synchronisation favoris entre web et mobile si compte utilisateur
  - **Filtrés par `enterprise_id`** — un guest ne voit que les favoris de son hôtel

### 5.3 Contrôle à distance / Device (icône télécommande)
- **Rôle :** contrôle des équipements en chambre (si l'hôtel le propose).
- **À développer :**
  - Contrôle TV (volume, chaînes, on/off) depuis l'app
  - Contrôle éclairage, climatisation, rideaux (si API / intégration fournies)
  - Interface type "télécommande" ou "contrôle chambre" dans l'app
  - *Note :* dépend des équipements et APIs de l'hôtel ; prévoir mode "non disponible" si pas d'intégration

### 5.4 Sécurité & Accès (icône cadenas)
- **Rôle :** authentification, confidentialité, accès sécurisé.
- **À développer :**
  - Connexion invité (numéro de chambre + nom / code réservation, ou compte)
  - Session sécurisée (token, expiration)
  - Données limitées au séjour / à la chambre du client **et à son hôtel**
  - Déconnexion et verrouillage d'écran (pour tablette en chambre)
  - Respect RGPD / confidentialité (pas de caméra, pas de stockage local sensible si possible)

---

## 6. En-tête et pied de page (ref. photo tablette)

### 6.1 Header
- **À développer :**
  - Logo et nom de l'hôtel (ex. King Fahd Palace Hotel) — **dynamique par `enterprise_id`**
  - Message de bienvenue personnalisé ("Bienvenue au …", "Votre assistant digital est à votre service")
  - Icônes : **Notifications** (cloche), **Profil** (personne), **Wi‑Fi** (statut connexion)
  - Bannière dynamique (ex. "Happy Hour au Lounge ce soir de 18h à 20h") — éditable côté CMS / back-office **par hôtel**
  - Langue et préférences (si multilingue)

### 6.2 Footer
- **À développer :**
  - Heure courante et date (ex. "07:45 PM Mercredi 21 Avril")
  - Météo (icône + résumé : température, temps)
  - Rappel du logo / nom de l'hôtel
  - Option : liens légaux (mentions, confidentialité, CGU) en bas de page

---

## 7. Récapitulatif des fonctionnalités à développer

### Par zone de l'interface
| Zone | Fonctionnalités |
|------|------------------|
| **Header** | Logo, bienvenue, notifications, profil, Wi‑Fi, bannières dynamiques, langue |
| **Grille centrale** | 8 modules : Room Service, Restaurants & Bars, Spa & Bien-être, Ménage & Blanchisserie, Services du Palace, Découvrir Dakar, Sorties & Spa Panoramique, Réception & Conciergerie |
| **Barre latérale / icônes** | Interaction, Favoris, Contrôle chambre / device, Sécurité & accès |
| **Footer** | Heure, date, météo, logo |

### Par type de besoin
| Besoin | Détail |
|--------|--------|
| **Auth / Contexte** | Identification invité (chambre + réservation ou compte), session, scope données par séjour **et par hôtel** |
| **Contenu dynamique** | Bannières, événements (Happy Hour), messages de l'hôtel, alertes |
| **Commandes** | Room service, restaurants, spa, blanchisserie, services palace, sorties — panier, validation, suivi |
| **Réservations** | Restaurants, spa, excursions, tables |
| **Contact** | Réception, concierge, demandes de service, suivi, notifications |
| **Découverte** | Destination (Dakar), carte, lieux, infos pratiques |
| **Transverse** | Favoris, contrôle chambre (si dispo), sécurité, UX tactile |
| **Multi-tenant** | Toutes les données filtrées par `enterprise_id` sauf pour super admin |

---

## 8. Structure technique recommandée

### 8.1 Architecture Multi-Tenant (SaaS)

**Filtrage des données par entreprise (hôtel) :**
- Middleware `EnsureUserBelongsToEnterprise` pour vérifier l'accès
- Scope Eloquent global sur tous les modèles liés à une entreprise (commandes, chambres, menus, etc.)
- Seul le **Super Admin** (`role = 'super_admin'` et `enterprise_id = null`) voit tout
- Tous les autres utilisateurs (`role = 'admin'`, `'staff'`, `'guest'`) voient **uniquement les données de leur `enterprise_id`**

**Exemple de scope global (à appliquer sur tous les modèles liés à une entreprise) :**
```php
// app/Models/Scopes/EnterpriseScopeTrait.php
protected static function booted()
{
    static::addGlobalScope('enterprise', function (Builder $builder) {
        if (auth()->check() && auth()->user()->role !== 'super_admin') {
            $builder->where('enterprise_id', auth()->user()->enterprise_id);
        }
    });
}
```

### 8.2 Application web admin (Laravel)
- **Utilisation du dashboard et sidebar existants** (TailAdmin, MenuHelper)
- **Routes séparées** :
  - `/admin/*` — Super Admin (gestion des entreprises)
  - `/dashboard/*` — Admin Hôtel et Staff (gestion de l'hôtel)
- **Menus adaptés par rôle** (via MenuHelper) :
  - **Super Admin** : Entreprises, Utilisateurs, Statistiques globales
  - **Admin Hôtel** : Chambres, Menus, Commandes, Staff, Statistiques hôtel
  - **Staff** : Commandes de son département (réception, housekeeping, room service, spa, etc.)

### 8.3 Application web guest (tablette)
- **Layout guest :** fond sombre (bleu nuit), accents or, grille 8 icônes + barre latérale 4 icônes
- **Routes :** préfixe type `/guest` ou `/app` pour toutes les pages invité
- **Modules Laravel :** un contrôleur (ou groupe) par module (RoomService, Restaurants, Spa, Housekeeping, PalaceServices, DiscoverDakar, Outings, Reception)
- **API :** endpoints JSON pour le front (panier, commandes, demandes, réservations) si le mobile consomme la même API
- **Temps réel :** heure, date, météo (API météo), notifications (polling ou WebSocket si besoin)

### 8.4 Application mobile
- **Choix possibles :** PWA (même code web, installable) **ou** app native (React Native / Flutter) consommant l'API Laravel
- **Fonctionnalités identiques** aux 8 modules + 4 axes + header + footer
- **Offline minimal :** affichage des infos déjà chargées (menus, infos chambre) si pas de réseau ; envoi des actions dès reconnexion

### 8.5 Base de données (tables à créer)

**Multi-tenant (SaaS) :**
- `enterprises` — entreprises (hôtels) : `id`, `name`, `address`, `phone`, `email`, `logo`, `status`, `created_at`, `updated_at`
- `users` — utilisateurs : `id`, `name`, `email`, `password`, `role` (`super_admin`, `admin`, `staff`, `guest`), `enterprise_id` (nullable pour super_admin), `department` (nullable pour staff), `created_at`, `updated_at`

**Hôtels :**
- `rooms` — chambres : `id`, `enterprise_id`, `room_number`, `floor`, `type`, `status`, `created_at`, `updated_at`
- `reservations` — réservations : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `check_in`, `check_out`, `status`, `created_at`, `updated_at`

**Room Service & Restaurants :**
- `menus` — menus : `id`, `enterprise_id`, `name` (petit-déjeuner, déjeuner, dîner, bar), `created_at`, `updated_at`
- `menu_items` — articles menu : `id`, `enterprise_id`, `menu_id`, `category_id`, `name`, `description`, `price`, `image`, `available`, `created_at`, `updated_at`
- `categories` — catégories : `id`, `enterprise_id`, `name` (plats, boissons, snacks), `created_at`, `updated_at`
- `orders` — commandes : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `type` (room_service, restaurant), `status`, `delivery_time`, `instructions`, `total`, `created_at`, `updated_at`
- `order_items` — articles commande : `id`, `order_id`, `menu_item_id`, `quantity`, `price`, `created_at`, `updated_at`

**Restaurants & Bars :**
- `restaurants` — restaurants/bars : `id`, `enterprise_id`, `name`, `description`, `opening_hours`, `image`, `created_at`, `updated_at`
- `restaurant_bookings` — réservations restaurant : `id`, `enterprise_id`, `user_id` (guest), `restaurant_id`, `date`, `time`, `guests_count`, `status`, `created_at`, `updated_at`

**Spa & Bien-être :**
- `spa_services` — services spa : `id`, `enterprise_id`, `name`, `description`, `duration`, `price`, `image`, `available`, `created_at`, `updated_at`
- `spa_bookings` — réservations spa : `id`, `enterprise_id`, `user_id` (guest), `spa_service_id`, `date`, `time`, `status`, `created_at`, `updated_at`

**Ménage & Blanchisserie :**
- `housekeeping_requests` — demandes ménage : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `type` (do_not_disturb, make_room, extra_towels), `status`, `created_at`, `updated_at`
- `laundry_requests` — demandes blanchisserie : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `service_type` (washing, ironing, pressing), `items`, `instructions`, `pickup_time`, `status`, `total`, `created_at`, `updated_at`

**Services du Palace :**
- `palace_services` — services : `id`, `enterprise_id`, `name`, `description`, `price`, `available`, `created_at`, `updated_at`
- `palace_service_requests` — demandes : `id`, `enterprise_id`, `user_id` (guest), `palace_service_id`, `details`, `status`, `created_at`, `updated_at`

**Découvrir Destination :**
- `pois` — points d'intérêt : `id`, `enterprise_id`, `name`, `description`, `address`, `latitude`, `longitude`, `category`, `image`, `created_at`, `updated_at`
- `articles` — articles : `id`, `enterprise_id`, `title`, `content`, `category`, `image`, `created_at`, `updated_at`

**Sorties & Excursions :**
- `outings` — sorties : `id`, `enterprise_id`, `name`, `description`, `duration`, `price`, `image`, `available`, `created_at`, `updated_at`
- `outing_bookings` — réservations sorties : `id`, `enterprise_id`, `user_id` (guest), `outing_id`, `date`, `time`, `guests_count`, `status`, `total`, `created_at`, `updated_at`

**Réception & Conciergerie :**
- `departments` — départements : `id`, `enterprise_id`, `name` (Réception, Concierge, Housekeeping, Maintenance), `phone`, `email`, `created_at`, `updated_at`
- `service_requests` — demandes service : `id`, `enterprise_id`, `user_id` (guest), `department_id`, `subject`, `message`, `attachment`, `status`, `created_at`, `updated_at`
- `hotel_messages` — messages hôtel : `id`, `enterprise_id`, `title`, `message`, `type` (info, alert, event), `created_at`, `updated_at`
- `notifications` — notifications : `id`, `enterprise_id`, `user_id`, `title`, `message`, `type`, `read_at`, `created_at`, `updated_at`

**Transverse :**
- `favourites` — favoris : `id`, `enterprise_id`, `user_id`, `favouritable_type` (menu_item, restaurant, spa_service, outing, poi), `favouritable_id`, `created_at`, `updated_at`
- `wake_up_calls` — réveils : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `time`, `repeat`, `status`, `created_at`, `updated_at`
- `room_preferences` — préférences chambre : `id`, `enterprise_id`, `user_id` (guest), `room_id`, `preferences` (JSON), `created_at`, `updated_at`

**IMPORTANT :** Toutes les tables (sauf `users` et `enterprises`) doivent avoir un champ `enterprise_id` pour le filtrage multi-tenant.

---

## 9. Ordre de développement suggéré

### Phase 1 : Architecture SaaS & Auth
1. **Migrations multi-tenant** — Compléter `enterprises` et `users` (role, enterprise_id, department).
2. **Seeder super admin** — Créer admin@admin.com / passer123 avec `role = 'super_admin'` et `enterprise_id = null`.
3. **Auth & Middleware** — Connexion, middleware `EnsureUserBelongsToEnterprise`, scope global Eloquent.
4. **Dashboard Super Admin** — Liste entreprises, créer entreprise, statistiques globales (utiliser MenuHelper + sidebar existants).
5. **Dashboard Admin Hôtel** — Statistiques de l'hôtel, menu adapté par rôle (utiliser MenuHelper + sidebar existants).

### Phase 2 : Modules métier (Admin Hôtel & Staff)
6. **Chambres & Réservations** — CRUD chambres, réservations (back-office admin).
7. **Menus & Articles** — CRUD menus, articles (room service, restaurant).
8. **Restaurants & Bars** — CRUD restaurants/bars.
9. **Spa & Services** — CRUD services spa, services palace.
10. **Destination & Sorties** — CRUD points d'intérêt, articles, excursions.
11. **Départements** — CRUD départements (réception, housekeeping, etc.).

### Phase 3 : Interface Guest (Tablette & Mobile)
12. **Auth guest + layout** — Connexion invité (chambre/réservation), layout tablette (header, footer, grille 8 modules, barre 4 icônes).
13. **Room Service** — Menus, panier, commande, suivi (cœur métier).
14. **Restaurants & Bars** — Fiches, réservation, lien room service.
15. **Spa & Bien-être** — Catalogue et réservation soins.
16. **Ménage & Blanchisserie** — Demandes ménage et blanchisserie.
17. **Services du Palace** — Liste et demandes de services.
18. **Découvrir Dakar** — Contenu + carte.
19. **Sorties & Spa Panoramique** — Excursions et spa panoramique.
20. **Réception & Conciergerie** — Demandes, contact, alertes (réutilisé partout).
21. **Favoris** — Ajout / liste / sync.
22. **Contrôle chambre** — Si équipements et API disponibles.
23. **Header / Footer** — Bannières, heure, date, météo, notifications (en parallèle ou tôt).

### Phase 4 : Staff & Suivi
24. **Dashboard Staff** — Interface staff par département (réception, housekeeping, room service, spa, etc.).
25. **Suivi commandes** — Statuts commandes, notifications temps réel.

### Phase 5 : Mobile
26. **Mobile** — PWA ou app native selon choix, en réutilisant l'API.

---

## 10. Design (aligné avec les photos)

### 10.1 Interface Admin (Super Admin, Admin Hôtel, Staff)
- **Réutiliser le thème existant** (TailAdmin, Tailwind CSS, Alpine.js)
- **Sidebar avec MenuHelper** (icônes SVG, submenus, activeMenu)
- **Dashboard avec cartes statistiques** (comme dans `pages/dashboard/index.blade.php`)
- **Thème clair/sombre** (existant)

### 10.2 Interface Guest (Tablette & Mobile)
- **Thème :** fond bleu nuit (#0f172a type), texte et icônes or / doré (#eab308 type).
- **Typographie :** titres lisibles, boutons larges pour tactile.
- **Grille :** 8 grandes tuiles (icône + libellé) comme sur la photo tablette.
- **Barre latérale :** 4 icônes (main, cœur+, télécommande, cadenas) sur fond sombre.
- **Responsive :** même structure sur mobile (menu ou grille adaptée).

---

Ce document sert de **référentiel des fonctionnalités à développer** pour Teranga Guest, en **web** et **mobile**, avec architecture **SaaS multi-tenant** alignée sur les visuels fournis.
