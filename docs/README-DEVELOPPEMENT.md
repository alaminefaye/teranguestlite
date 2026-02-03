# Teranga Guest - Guide de développement

> **Application SaaS multi-tenant pour la gestion hôtelière**  
> Version: Phase 2 terminée (2 février 2026)

---

## 🎯 Vue d'ensemble

Teranga Guest est une **application SaaS multi-tenant** pour la gestion hôtelière avec interface guest (tablette/mobile). Elle permet à plusieurs hôtels d'utiliser la même plateforme tout en gardant leurs données isolées.

### Technologies
- **Backend :** Laravel 11
- **Frontend :** Blade + Vite + Tailwind CSS 4 + Alpine.js
- **Base de données :** MySQL
- **Design :** TailAdmin (adapté)

---

## ✅ Fonctionnalités développées

### Phase 1 : Architecture SaaS & Auth ✅

**Multi-tenant complet :**
- Super Admin (voit tout)
- Admin Hôtel (voit uniquement son hôtel)
- Staff (voit son département)
- Guest (voit ses données)

**Authentification :**
- Login/Logout
- Redirection automatique selon rôle
- Session sécurisée

**Dashboard Super Admin :**
- Statistiques plateforme
- CRUD entreprises (hôtels)
- Upload logos
- Gestion utilisateurs

### Phase 2 : Chambres & Réservations ✅

**Gestion des chambres :**
- CRUD complet
- Types : Simple, Double, Suite, Deluxe, Présidentielle
- Statuts : Disponible, Occupée, Maintenance, Réservée
- Équipements (12 options)
- Upload d'images
- Filtres et recherche

**Gestion des réservations :**
- CRUD complet
- Auto-génération numéro (RES-XXXXXXXX)
- Calcul prix automatique
- Vérification disponibilité
- Check-in / Check-out
- Annulation
- Timeline visuelle
- Filtres et recherche

**Dashboard Admin Hôtel :**
- Statistiques temps réel
- Chambres (total, disponibles, occupées, maintenance)
- Réservations (total, check-ins/outs jour)
- Liste réservations récentes
- Actions rapides

---

## 🗄️ Base de données

### Tables créées
1. **enterprises** - Hôtels de la plateforme
2. **users** - Utilisateurs (super_admin, admin, staff, guest)
3. **rooms** - Chambres (avec enterprise_id)
4. **reservations** - Réservations (avec enterprise_id)

### Relations
- Enterprise → hasMany(Users, Rooms, Reservations)
- User → belongsTo(Enterprise)
- Room → belongsTo(Enterprise), hasMany(Reservations)
- Reservation → belongsTo(Enterprise, User, Room)

---

## 🔐 Comptes de test

### Super Admin
- **Email :** admin@admin.com
- **Mot de passe :** passer123
- **Accès :** Toute la plateforme
- **URL :** http://localhost:8000/admin/dashboard

### Admin Hôtel (King Fahd Palace Hotel)
- **Email :** admin@kingfahd.sn
- **Mot de passe :** password
- **Accès :** Dashboard hôtel, Chambres, Réservations
- **URL :** http://localhost:8000/dashboard

### Staff
- **Reception :** reception@kingfahd.sn / password
- **Housekeeping :** housekeeping@kingfahd.sn / password
- **Room Service :** roomservice@kingfahd.sn / password
- **Accès :** Dashboard département (à développer)

### Guest
- **Email :** jean.dupont@example.com / password
- **Accès :** Interface tablette (à développer)

---

## 🚀 Installation & Démarrage

### 1. Cloner et installer
```bash
cd /Users/Zhuanz/Desktop/projets/web/terangaguest
composer install
npm install
```

### 2. Configurer l'environnement
```bash
cp .env.example .env
# Configurer la base de données dans .env
```

### 3. Migrer et seeder
```bash
php artisan migrate:fresh --seed
```

### 4. Démarrer le serveur
```bash
php artisan serve
```

### 5. Compiler les assets (si besoin)
```bash
npm run dev
# Ou pour production :
npm run build
```

---

## 🧪 Guide de test

### Tester en tant que Super Admin

```bash
# 1. Ouvrir http://localhost:8000/signin
# 2. Se connecter avec admin@admin.com / passer123
```

**Ce que vous pouvez faire :**
- ✅ Voir le dashboard super admin
- ✅ Voir la liste des entreprises (1 entreprise : King Fahd Palace Hotel)
- ✅ Créer une nouvelle entreprise
- ✅ Modifier une entreprise
- ✅ Voir les détails d'une entreprise
- ✅ Supprimer une entreprise

### Tester en tant qu'Admin Hôtel

```bash
# 1. Se déconnecter (menu user > Déconnexion)
# 2. Se connecter avec admin@kingfahd.sn / password
```

**Ce que vous pouvez faire :**

**Dashboard :**
- ✅ Voir statistiques de l'hôtel
- ✅ 8 chambres, X disponibles, Y occupées
- ✅ Check-ins/outs du jour
- ✅ Liste réservations récentes

**Chambres (/dashboard/rooms) :**
- ✅ Voir liste des 8 chambres
- ✅ Filtrer par type (Single, Double, Suite, etc.)
- ✅ Filtrer par statut (Disponible, Occupée, etc.)
- ✅ Rechercher par numéro
- ✅ Créer une nouvelle chambre (ex: 104)
- ✅ Uploader une image pour la chambre
- ✅ Sélectionner équipements (Wi-Fi, TV, Minibar, etc.)
- ✅ Modifier une chambre existante
- ✅ Voir détails d'une chambre + ses réservations
- ✅ Supprimer une chambre (si pas de réservations actives)

**Réservations (/dashboard/reservations) :**
- ✅ Voir liste des 3 réservations
- ✅ Filtrer par statut
- ✅ Filtrer par chambre
- ✅ Rechercher par référence ou nom client
- ✅ Créer une nouvelle réservation :
  - Sélectionner un client
  - Choisir dates (check-in/out)
  - Sélectionner une chambre disponible
  - Voir le prix calculer en temps réel
  - Ajouter demandes spéciales
- ✅ Voir détails d'une réservation (timeline)
- ✅ Effectuer check-in (statut chambre → occupée)
- ✅ Effectuer check-out (statut chambre → disponible)
- ✅ Annuler une réservation
- ✅ Modifier une réservation

---

## 📋 Architecture Multi-Tenant

### Filtrage automatique

**Trait `EnterpriseScopeTrait`** appliqué sur tous les modèles liés à une entreprise (Room, Reservation, etc.) :

```php
// Super admin voit tout
if (auth()->user()->isSuperAdmin()) {
    // Pas de filtre
}

// Autres utilisateurs voient uniquement leur entreprise
else {
    $query->where('enterprise_id', auth()->user()->enterprise_id);
}
```

### Middleware

**`EnsureUserBelongsToEnterprise`** vérifie que :
- Super admin peut accéder à tout
- Autres utilisateurs ont un enterprise_id
- Appliqué sur toutes les routes `/admin` et `/dashboard`

---

## 🎨 Interface

### Menu adaptatif (MenuHelper)

**Super Admin :**
- Dashboard
- Entreprises (Hôtels)
- Utilisateurs

**Admin Hôtel :**
- Dashboard
- Chambres
- Réservations
- Commandes (sous-menu : Room Service, Restaurants, Spa, Blanchisserie)
- Services (sous-menu : Menus, Restaurants & Bars, Spa, Palace, Excursions)
- Staff

**Staff :**
- Dashboard département
- Items selon département

### Thème
- Clair/Sombre (toggle automatique)
- Sidebar collapsible
- Responsive design
- Badges colorés selon statut
- Icônes SVG

---

## 🛠️ Commandes utiles

### Database
```bash
# Migrer
php artisan migrate

# Reset + seed
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback
```

### Générer du code
```bash
# Contrôleur
php artisan make:controller NomController --resource

# Modèle
php artisan make:model NomModele

# Migration
php artisan make:migration create_table_name

# Seeder
php artisan make:seeder NomSeeder
```

### Serveur
```bash
# Démarrer
php artisan serve

# Voir routes
php artisan route:list

# Voir routes dashboard
php artisan route:list --path=dashboard
```

---

## 📁 Structure du projet

```
terangaguest/
├── app/
│   ├── Helpers/
│   │   └── MenuHelper.php (adaptatif par rôle)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/ (Super Admin)
│   │   │   ├── Auth/
│   │   │   ├── Dashboard/ (Admin Hôtel)
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   │       └── EnsureUserBelongsToEnterprise.php
│   └── Models/
│       ├── Scopes/
│       │   └── EnterpriseScopeTrait.php
│       ├── Enterprise.php
│       ├── User.php
│       ├── Room.php
│       └── Reservation.php
├── database/
│   ├── migrations/ (7 migrations)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── SuperAdminSeeder.php
│       └── DemoDataSeeder.php
├── resources/views/
│   ├── layouts/ (app.blade.php, sidebar, header)
│   └── pages/
│       ├── admin/ (Super Admin)
│       ├── dashboard/ (Admin Hôtel)
│       └── auth/
├── routes/
│   └── web.php
└── docs/
    ├── FONCTIONNALITES-A-DEVELOPPER.md
    ├── PHASE-1-COMPLETED.md
    ├── PHASE-2-COMPLETED.md
    ├── RESUME-DEVELOPPEMENT.md
    └── NEXT-STEPS.md
```

---

## 🎯 Prochaines étapes

### Option 1 : Continuer modules Admin (recommandé)
1. Menus & Articles (Room Service)
2. Restaurants & Bars
3. Services Spa
4. Blanchisserie
5. Services Palace
6. Destination
7. Excursions

### Option 2 : Interface Guest (Tablette)
1. Layout guest (fond sombre bleu nuit)
2. Grille 8 modules services
3. Barre latérale 4 icônes
4. Header & Footer dynamiques

---

## 📊 Statistiques du projet

- **Phases terminées :** 2/5 (40%)
- **Fichiers créés :** 33
- **Lignes de code :** ~2500
- **Temps de développement :** ~3 heures
- **Tests :** Tous fonctionnels ✅

---

## 🌐 URLs principales

### Super Admin
- Login : http://localhost:8000/signin
- Dashboard : http://localhost:8000/admin/dashboard
- Entreprises : http://localhost:8000/admin/enterprises

### Admin Hôtel
- Login : http://localhost:8000/signin
- Dashboard : http://localhost:8000/dashboard
- Chambres : http://localhost:8000/dashboard/rooms
- Réservations : http://localhost:8000/dashboard/reservations

---

## 🎉 Application prête à tester !

**Serveur lancé sur :** http://localhost:8000

**Prochaine action suggérée :**
1. Ouvrir http://localhost:8000 dans votre navigateur
2. Se connecter avec `admin@kingfahd.sn` / `password`
3. Tester les fonctionnalités Chambres & Réservations
4. Décider de la suite : modules Admin ou interface Guest ?

---

**Bon test ! 🚀**
