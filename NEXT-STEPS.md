# Prochaines étapes - Guide détaillé

> **Objectif :** Compléter la Phase 2 (CRUD Chambres & Réservations)

---

## 🎯 Ordre recommandé de développement

### 1. CRUD Chambres (Rooms) — Priorité HAUTE

**Pourquoi en premier ?**
- Les réservations dépendent des chambres
- Plus simple à implémenter
- Permet de tester le système multi-tenant

**Étapes :**

#### A. Implémenter `RoomController`
```php
// app/Http/Controllers/Dashboard/RoomController.php

public function index() {
    // Liste des chambres avec filtres (type, status)
    // Pagination
    // Recherche par numéro de chambre
}

public function create() {
    // Formulaire de création
    // Liste des types et statuts disponibles
}

public function store(Request $request) {
    // Validation
    // Création avec enterprise_id automatique
    // Upload image si fournie
    // Redirect avec message success
}

public function show(Room $room) {
    // Détails de la chambre
    // Liste des réservations passées et futures
    // Statistiques (taux d'occupation)
}

public function edit(Room $room) {
    // Formulaire pré-rempli
}

public function update(Request $request, Room $room) {
    // Validation
    // Mise à jour
    // Upload image si changée
    // Redirect avec message success
}

public function destroy(Room $room) {
    // Vérifier qu'il n'y a pas de réservations actives
    // Supprimer l'image
    // Supprimer la chambre
    // Redirect avec message success
}
```

#### B. Créer les vues Rooms
1. **index.blade.php** :
   - Table des chambres (numéro, type, statut, prix, capacité)
   - Filtres (type, statut)
   - Bouton "Nouvelle chambre"
   - Actions : Voir, Modifier, Supprimer
   - Pagination

2. **create.blade.php** :
   - Formulaire : numéro, étage, type, statut, prix, capacité, description
   - Upload image
   - Sélection équipements (checkboxes)
   - Boutons Créer / Annuler

3. **show.blade.php** :
   - Détails complets de la chambre
   - Liste des réservations (passées et futures)
   - Boutons Modifier / Supprimer

4. **edit.blade.php** :
   - Même formulaire que create, pré-rempli
   - Aperçu de l'image actuelle
   - Boutons Mettre à jour / Annuler

#### C. Ajouter les routes
```php
// routes/web.php

Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'enterprise'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('rooms', RoomController::class);
});
```

---

### 2. Dashboard Admin Hôtel — Priorité HAUTE

**Adapter le dashboard existant** (`app/Http/Controllers/DashboardController.php`)

```php
public function index() {
    // Statistiques chambres
    $totalRooms = Room::count();
    $availableRooms = Room::available()->count();
    $occupiedRooms = Room::occupied()->count();
    $maintenanceRooms = Room::maintenance()->count();
    
    // Statistiques réservations
    $totalReservations = Reservation::count();
    $checkInsToday = Reservation::checkInToday()->count();
    $checkOutsToday = Reservation::checkOutToday()->count();
    $pendingReservations = Reservation::pending()->count();
    
    // Réservations récentes
    $recentReservations = Reservation::with(['room', 'user'])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    return view('pages.dashboard.index', compact(...));
}
```

**Mettre à jour la vue** (`resources/views/pages/dashboard/index.blade.php`)
- Remplacer le contenu actuel par les nouvelles statistiques
- Cartes : Chambres, Réservations, Check-ins/outs du jour
- Tableau : Réservations récentes
- Graphiques (optionnel)

---

### 3. CRUD Réservations — Priorité MOYENNE

**Après avoir terminé les chambres**

#### A. Implémenter `ReservationController`

**Points importants :**
- Vérifier disponibilité de la chambre avant création
- Calculer le prix total automatiquement
- Mettre à jour le statut de la chambre lors du check-in/out
- Actions supplémentaires : `checkIn()`, `checkOut()`, `cancel()`

#### B. Créer les vues Reservations

1. **index.blade.php** :
   - Filtres : statut, dates, chambre
   - Affichage : numéro réservation, guest, chambre, dates, statut
   - Actions : Voir, Modifier, Annuler

2. **create.blade.php** :
   - Sélection dates (check-in, check-out)
   - Sélection guest (existant ou nouveau)
   - Sélection chambre disponible (filtré par dates)
   - Prix calculé automatiquement
   - Demandes spéciales

3. **show.blade.php** :
   - Détails réservation
   - Infos guest et chambre
   - Timeline (created → confirmed → checked_in → checked_out)
   - Boutons : Check-in, Check-out, Annuler, Modifier

---

### 4. Modules suivants (après Rooms & Reservations)

**Ordre suggéré :**
1. Menus & Articles (Room Service)
2. Restaurants & Bars
3. Services Spa
4. Blanchisserie
5. Services Palace
6. Destination (Découvrir Dakar)
7. Excursions

---

## 🛠️ Conseils de développement

### 1. Commencer petit
- Créer d'abord l'index (liste) avec bouton "Créer"
- Ajouter le formulaire de création
- Tester la création
- Ajouter show, edit, delete progressivement

### 2. Réutiliser les composants
- Copier/adapter les vues enterprises existantes
- Utiliser les mêmes classes Tailwind
- Garder la cohérence visuelle

### 3. Validation
```php
// Exemple validation Room
$validated = $request->validate([
    'room_number' => 'required|string|unique:rooms,room_number,' . $room?->id,
    'floor' => 'nullable|integer',
    'type' => 'required|in:single,double,suite,deluxe,presidential',
    'status' => 'required|in:available,occupied,maintenance,reserved',
    'price_per_night' => 'required|numeric|min:0',
    'capacity' => 'required|integer|min:1',
    'description' => 'nullable|string',
    'image' => 'nullable|image|max:2048',
]);

// Ajouter enterprise_id automatiquement
$validated['enterprise_id'] = auth()->user()->enterprise_id;
```

### 4. Upload d'images
```php
if ($request->hasFile('image')) {
    // Supprimer l'ancienne image si elle existe
    if ($room->image) {
        Storage::disk('public')->delete($room->image);
    }
    
    // Uploader la nouvelle
    $validated['image'] = $request->file('image')->store('rooms', 'public');
}
```

### 5. Messages flash
```php
// Dans le contrôleur
return redirect()->route('dashboard.rooms.index')
    ->with('success', 'Chambre créée avec succès !');

// Dans la vue
@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600">
        {{ session('success') }}
    </div>
@endif
```

---

## 📋 Checklist par module

### Module Rooms
- [ ] RoomController complet (7 méthodes)
- [ ] index.blade.php (liste + filtres)
- [ ] create.blade.php (formulaire)
- [ ] show.blade.php (détails)
- [ ] edit.blade.php (formulaire)
- [ ] Routes dans web.php
- [ ] Tests manuels (créer, modifier, supprimer)

### Module Reservations
- [ ] ReservationController complet (+ actions check-in/out/cancel)
- [ ] index.blade.php (liste + filtres)
- [ ] create.blade.php (formulaire avec sélection chambre dispo)
- [ ] show.blade.php (détails + timeline)
- [ ] edit.blade.php (formulaire)
- [ ] Routes dans web.php
- [ ] Tests manuels (créer, check-in, check-out, annuler)

---

## 🎨 Design tips

**Réutiliser les classes existantes :**
- Cartes : `rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900`
- Boutons primaires : `px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600`
- Badges statut : `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium`
- Tables : voir `enterprises/index.blade.php` pour exemple

**Couleurs par statut :**
- Success (vert) : disponible, confirmé, actif
- Warning (jaune) : en attente, réservé
- Error (rouge) : maintenance, annulé
- Blue : occupé, check-in
- Gray : check-out, inactif

---

## 🚀 Commandes rapides

```bash
# Créer une migration
php artisan make:migration create_table_name

# Exécuter les migrations
php artisan migrate

# Créer un contrôleur resource
php artisan make:controller Dashboard/NomController --resource

# Créer un modèle
php artisan make:model NomModele

# Lancer le serveur
php artisan serve

# Voir les routes
php artisan route:list
```

---

**Bon développement ! 🚀**

N'hésitez pas à vous référer aux fichiers existants (`EnterpriseController`, vues enterprises) comme exemples.
