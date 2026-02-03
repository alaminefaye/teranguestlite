# 📱 TERANGA GUEST - APPLICATION MOBILE FLUTTER

**Version :** 1.0.0  
**Date :** 02 Février 2026  
**Design :** Inspiré de l'interface King Fahd Palace Hotel

---

## 🎨 DESIGN SYSTEM

### Palette de Couleurs

**Couleurs Principales :**
```dart
// Bleu Marine Foncé (Background)
Color primaryDark = Color(0xFF0A1929);
Color primaryBlue = Color(0xFF1A2F44);

// Or/Gold (Accent)
Color accentGold = Color(0xFFD4AF37);
Color accentGoldLight = Color(0xFFE5C158);

// Texte
Color textWhite = Color(0xFFFFFFFF);
Color textGray = Color(0xFFB0B8C1);
Color textGold = Color(0xFFD4AF37);
```

**Dégradés :**
```dart
// Background Gradient
LinearGradient backgroundGradient = LinearGradient(
  begin: Alignment.topCenter,
  end: Alignment.bottomCenter,
  colors: [Color(0xFF0A1929), Color(0xFF1A2F44)],
);

// Gold Gradient (Boutons)
LinearGradient goldGradient = LinearGradient(
  colors: [Color(0xFFD4AF37), Color(0xFFE5C158)],
);
```

### Typographie

```dart
// Titres
TextStyle heading1 = TextStyle(
  fontSize: 28,
  fontWeight: FontWeight.bold,
  color: Colors.white,
  fontFamily: 'Playfair Display', // Élégant
);

// Sous-titres
TextStyle heading2 = TextStyle(
  fontSize: 18,
  fontWeight: FontWeight.w600,
  color: Color(0xFFD4AF37),
);

// Corps de texte
TextStyle bodyText = TextStyle(
  fontSize: 16,
  color: Color(0xFFB0B8C1),
);
```

### Composants UI

**Service Card :**
- Taille : 150x150px
- Border radius : 16px
- Background : Transparent avec bordure gold
- Icon : Gold, taille 48x48px
- Label : White, centré en bas

**Bottom Navigation :**
- 4 items : Accueil, Commandes, Réservations, Profil
- Background : primaryBlue
- Selected : accentGold
- Unselected : textGray

---

## 📋 FONCTIONNALITÉS À DÉVELOPPER

### PHASE 1 : AUTHENTIFICATION & SETUP (Semaine 1)

#### 1.1 Splash Screen ⏱️ 4h
**Écran :**
- Logo Teranga Guest animé
- Texte "Bienvenue" avec animation fade-in
- Vérification token auto-login
- Navigation automatique

**Fichiers :**
- `lib/screens/splash_screen.dart`
- `lib/widgets/animated_logo.dart`

---

#### 1.2 Login Screen ⏱️ 6h
**Écran :**
- Logo de l'hôtel en haut
- Champs : Email, Mot de passe
- Bouton "Se connecter" (gold gradient)
- Loader lors de la connexion
- Messages d'erreur élégants

**API :**
- `POST /api/auth/login`

**Fichiers :**
- `lib/screens/auth/login_screen.dart`
- `lib/services/auth_service.dart`
- `lib/providers/auth_provider.dart`

**Stockage :**
- Token dans `flutter_secure_storage`
- User data dans `SharedPreferences`

---

#### 1.3 Configuration Firebase ⏱️ 4h
**Setup :**
- Firebase Core
- Firebase Messaging (FCM)
- Permissions notifications iOS/Android
- Background/Foreground handlers

**API :**
- `POST /api/fcm-token` (enregistrer token)
- `DELETE /api/fcm-token` (supprimer token)

**Fichiers :**
- `lib/services/firebase_service.dart`
- `lib/services/notification_service.dart`

---

### PHASE 2 : DASHBOARD & NAVIGATION (Semaine 1-2)

#### 2.1 Dashboard Principal ⏱️ 8h
**Design :** Comme l'image fournie

**Header :**
- Logo hôtel (King Fahd Palace style)
- Nom de l'hôtel
- Icônes : Notifications, Profile
- Message de bienvenue : "Bienvenue au [Nom Hôtel]"
- Sous-titre : "Votre assistant digital est à votre service"

**Grille de Services (2x4) :**
1. 🍽️ **Room Service** → `/room-service`
2. 🍷 **Restaurants & Bars** → `/restaurants`
3. 💆 **Spa & Bien-être** → `/spa`
4. 👑 **Services Palace** → `/palace`
5. 🏖️ **Excursions** → `/excursions`
6. 👔 **Blanchisserie** → `/laundry`
7. 🛎️ **Conciergerie** → `/concierge`
8. 📞 **Centre d'Appels** → Action directe (tel:)

**Service Card Design :**
```dart
Container(
  decoration: BoxDecoration(
    border: Border.all(color: accentGold, width: 1.5),
    borderRadius: BorderRadius.circular(16),
  ),
  child: Column(
    mainAxisAlignment: MainAxisAlignment.center,
    children: [
      Icon(serviceIcon, size: 48, color: accentGold),
      SizedBox(height: 12),
      Text(serviceName, style: goldTextStyle),
    ],
  ),
)
```

**Footer :**
- Heure actuelle (Format : HH:mm)
- Logo hôtel miniature

**Fichiers :**
- `lib/screens/dashboard/dashboard_screen.dart`
- `lib/widgets/service_card.dart`
- `lib/widgets/dashboard_header.dart`

---

#### 2.2 Bottom Navigation ⏱️ 4h
**4 Onglets :**
1. 🏠 **Accueil** - Dashboard
2. 📦 **Commandes** - Historique commandes/réservations
3. 🎫 **Réservations** - Réservations actives
4. 👤 **Profil** - Profile utilisateur

**Fichiers :**
- `lib/screens/main_screen.dart`
- `lib/widgets/custom_bottom_nav.dart`

---

### PHASE 3 : ROOM SERVICE (Semaine 2)

#### 3.1 Liste Catégories ⏱️ 4h
**Écran :**
- Liste des catégories de menu
- Card par catégorie avec image/icon
- Nombre d'articles par catégorie
- Navigation vers liste articles

**API :**
- `GET /api/room-service/categories`

**Fichiers :**
- `lib/screens/room_service/categories_screen.dart`
- `lib/widgets/category_card.dart`

---

#### 3.2 Liste Articles ⏱️ 6h
**Écran :**
- Liste articles d'une catégorie
- Card : Image, Nom, Description, Prix, Temps préparation
- Filtres : Disponible, Prix
- Recherche
- Pagination (Load more)

**API :**
- `GET /api/room-service/items?category_id={id}&page={page}`

**Fichiers :**
- `lib/screens/room_service/items_screen.dart`
- `lib/widgets/menu_item_card.dart`
- `lib/providers/room_service_provider.dart`

---

#### 3.3 Détail Article ⏱️ 4h
**Écran :**
- Image en grand
- Nom, Description complète
- Prix formaté
- Temps de préparation
- Sélecteur quantité (+/-)
- Champ "Instructions spéciales"
- Bouton "Ajouter au panier" (gold)

**API :**
- `GET /api/room-service/items/{id}`

**Fichiers :**
- `lib/screens/room_service/item_detail_screen.dart`
- `lib/widgets/quantity_selector.dart`

---

#### 3.4 Panier ⏱️ 8h
**Écran :**
- Liste articles dans le panier
- Card : Image, Nom, Quantité, Prix unitaire, Sous-total
- Modifier quantité
- Supprimer article
- Instructions spéciales par article
- Total général
- Bouton "Commander" (gold gradient)

**Stockage :**
- Panier dans `Hive` (local database)

**API :**
- `POST /api/room-service/checkout`

**Fichiers :**
- `lib/screens/room_service/cart_screen.dart`
- `lib/services/cart_service.dart`
- `lib/models/cart_item.dart`

**Payload Checkout :**
```json
{
  "items": [
    {
      "menu_item_id": 1,
      "quantity": 2,
      "special_instructions": "Sans oignons"
    }
  ]
}
```

---

#### 3.5 Confirmation Commande ⏱️ 4h
**Écran :**
- Animation succès (checkmark)
- Numéro de commande
- Temps estimé de livraison
- Récapitulatif commande
- Bouton "Suivre ma commande"
- Bouton "Retour à l'accueil"

**Notification Push :**
- Recevoir confirmation commande

**Fichiers :**
- `lib/screens/room_service/order_confirmation_screen.dart`

---

### PHASE 4 : COMMANDES & HISTORIQUE (Semaine 2-3)

#### 4.1 Mes Commandes ⏱️ 6h
**Écran :**
- Liste de toutes les commandes
- Filtres : Statut (pending, confirmed, preparing, delivering, delivered)
- Card commande :
  - Numéro commande
  - Date/Heure
  - Nombre d'articles
  - Total
  - Statut avec badge coloré
  - Bouton "Détails"

**API :**
- `GET /api/orders?status={status}&page={page}`

**Fichiers :**
- `lib/screens/orders/orders_list_screen.dart`
- `lib/widgets/order_card.dart`
- `lib/providers/orders_provider.dart`

**Statuts & Couleurs :**
```dart
Map<String, Color> statusColors = {
  'pending': Colors.orange,
  'confirmed': Colors.blue,
  'preparing': Colors.purple,
  'delivering': Colors.cyan,
  'delivered': Colors.green,
  'cancelled': Colors.red,
};
```

---

#### 4.2 Détail Commande ⏱️ 6h
**Écran :**
- Numéro commande (grand et visible)
- Timeline du statut avec étapes
- Liste des articles commandés
- Sous-total, Total
- Instructions spéciales
- Date/Heure commande
- Temps estimé de livraison
- Bouton "Recommander" si delivered

**API :**
- `GET /api/orders/{id}`
- `POST /api/orders/{id}/reorder`

**Timeline Statuts :**
```
Pending → Confirmed → Preparing → Delivering → Delivered
   🔵       ✅          🍳           🚚          ✅
```

**Fichiers :**
- `lib/screens/orders/order_detail_screen.dart`
- `lib/widgets/order_timeline.dart`
- `lib/widgets/order_item_tile.dart`

---

### PHASE 5 : RESTAURANTS & BARS (Semaine 3)

#### 5.1 Liste Restaurants ⏱️ 6h
**Écran :**
- Liste restaurants/bars/cafés
- Card : Image, Nom, Type, Cuisine, Capacité
- Badge "Ouvert maintenant" (vert) / "Fermé" (rouge)
- Horaires affichés
- Filtre par type
- Navigation vers détails

**API :**
- `GET /api/restaurants?type={type}`

**Fichiers :**
- `lib/screens/restaurants/restaurants_list_screen.dart`
- `lib/widgets/restaurant_card.dart`
- `lib/providers/restaurants_provider.dart`

---

#### 5.2 Détail Restaurant ⏱️ 6h
**Écran :**
- Images (carousel si plusieurs)
- Nom, Type, Description
- Horaires d'ouverture (par jour)
- Capacité
- Amenities (icônes)
- Bouton "Réserver une table" (gold)

**API :**
- `GET /api/restaurants/{id}`

**Fichiers :**
- `lib/screens/restaurants/restaurant_detail_screen.dart`
- `lib/widgets/opening_hours_widget.dart`

---

#### 5.3 Réservation Restaurant ⏱️ 8h
**Écran :**
- Sélecteur de date (DatePicker)
- Sélecteur d'heure (TimePicker)
- Nombre de personnes (1-20)
- Champ "Demandes spéciales" (optionnel)
- Vérification disponibilité en temps réel
- Bouton "Confirmer la réservation"

**Validation :**
- Vérifier que l'heure est dans les horaires d'ouverture
- Vérifier que la capacité n'est pas dépassée
- Date >= aujourd'hui

**API :**
- `POST /api/restaurants/{id}/reserve`

**Payload :**
```json
{
  "date": "2026-02-10",
  "time": "20:00",
  "guests": 4,
  "special_requests": "Table près de la fenêtre"
}
```

**Fichiers :**
- `lib/screens/restaurants/reserve_restaurant_screen.dart`
- `lib/widgets/date_time_picker.dart`

---

#### 5.4 Mes Réservations Restaurant ⏱️ 4h
**Écran :**
- Liste réservations restaurants
- Card : Restaurant, Date, Heure, Nb personnes, Statut
- Filtrer par date (À venir, Passées)

**API :**
- `GET /api/my-restaurant-reservations`

**Fichiers :**
- `lib/screens/restaurants/my_reservations_screen.dart`
- `lib/widgets/reservation_card.dart`

---

### PHASE 6 : SPA & BIEN-ÊTRE (Semaine 3-4)

#### 6.1 Liste Services Spa ⏱️ 6h
**Écran :**
- Liste services spa
- Card : Image, Nom, Catégorie, Prix, Durée
- Filtres : Catégorie (massage, facial, body_treatment, wellness)
- Badge "Featured" pour services mis en avant
- Navigation vers détails

**API :**
- `GET /api/spa-services?category={category}`

**Fichiers :**
- `lib/screens/spa/spa_services_screen.dart`
- `lib/widgets/spa_service_card.dart`
- `lib/providers/spa_provider.dart`

---

#### 6.2 Détail Service Spa ⏱️ 6h
**Écran :**
- Image service
- Nom, Catégorie
- Description complète
- Prix, Durée (ex: 60 min)
- Features (liste avec icônes)
- Bouton "Réserver" (gold)

**API :**
- `GET /api/spa-services/{id}`

**Fichiers :**
- `lib/screens/spa/spa_service_detail_screen.dart`

---

#### 6.3 Réservation Spa ⏱️ 8h
**Écran :**
- Sélecteur de date
- Sélecteur d'heure (créneaux disponibles)
- Champ "Demandes spéciales"
- Récapitulatif : Service, Date, Heure, Prix
- Bouton "Confirmer"

**API :**
- `POST /api/spa-services/{id}/reserve`

**Payload :**
```json
{
  "date": "2026-02-10",
  "time": "14:00",
  "special_requests": "Huiles essentielles lavande"
}
```

**Fichiers :**
- `lib/screens/spa/reserve_spa_screen.dart`

---

#### 6.4 Mes Réservations Spa ⏱️ 4h
**Écran :**
- Liste réservations spa
- Card : Service, Date, Heure, Prix, Statut
- Bouton "Annuler" (si > 24h avant)

**API :**
- `GET /api/my-spa-reservations`

**Fichiers :**
- `lib/screens/spa/my_spa_reservations_screen.dart`

---

### PHASE 7 : EXCURSIONS (Semaine 4)

#### 7.1 Liste Excursions ⏱️ 6h
**Écran :**
- Liste excursions
- Card : Image, Nom, Type, Prix adulte/enfant, Durée
- Badge "Featured"
- Filtres : Type (cultural, adventure, relaxation, city_tour)

**API :**
- `GET /api/excursions?type={type}`

**Fichiers :**
- `lib/screens/excursions/excursions_list_screen.dart`
- `lib/widgets/excursion_card.dart`
- `lib/providers/excursions_provider.dart`

---

#### 7.2 Détail Excursion ⏱️ 6h
**Écran :**
- Images (carousel)
- Nom, Type, Description
- Prix adulte/enfant
- Durée, Heure départ
- Min/Max participants
- **Inclus** (liste avec checkmarks verts)
- **Non inclus** (liste avec X rouges)
- Bouton "Réserver"

**API :**
- `GET /api/excursions/{id}`

**Fichiers :**
- `lib/screens/excursions/excursion_detail_screen.dart`
- `lib/widgets/included_list.dart`

---

#### 7.3 Réservation Excursion ⏱️ 8h
**Écran :**
- Sélecteur date
- Nombre adultes (stepper)
- Nombre enfants (stepper)
- Calcul automatique du total
- Vérification min/max participants
- Champ "Demandes spéciales"
- Récapitulatif complet
- Bouton "Confirmer"

**Validation :**
- Total participants >= min_participants
- Total participants <= max_participants

**API :**
- `POST /api/excursions/{id}/book`

**Payload :**
```json
{
  "date": "2026-02-15",
  "adults": 2,
  "children": 1,
  "special_requests": "Enfant 5 ans"
}
```

**Fichiers :**
- `lib/screens/excursions/book_excursion_screen.dart`
- `lib/widgets/participant_stepper.dart`

---

#### 7.4 Mes Réservations Excursions ⏱️ 4h
**Écran :**
- Liste bookings excursions
- Card : Excursion, Date, Adultes, Enfants, Total, Statut

**API :**
- `GET /api/my-excursion-bookings`

**Fichiers :**
- `lib/screens/excursions/my_bookings_screen.dart`

---

### PHASE 8 : BLANCHISSERIE (Semaine 4-5)

#### 8.1 Liste Services Blanchisserie ⏱️ 4h
**Écran :**
- Liste services disponibles
- Card : Nom, Catégorie, Prix, Délai (turnaround)
- Regroupement par catégorie

**API :**
- `GET /api/laundry/services`

**Fichiers :**
- `lib/screens/laundry/laundry_services_screen.dart`
- `lib/widgets/laundry_service_tile.dart`
- `lib/providers/laundry_provider.dart`

---

#### 8.2 Créer Demande Blanchisserie ⏱️ 10h
**Écran :**
- Liste services avec sélection multiple
- Chaque service : Checkbox + Stepper quantité
- Calcul total en temps réel
- Sélecteur heure de collecte (optionnel)
- Temps de livraison estimé (auto-calculé)
- Champ "Instructions spéciales"
- Bouton "Envoyer la demande"

**Calcul Livraison :**
```dart
// Prendre le max des turnaround_hours
int maxTurnaround = selectedServices
    .map((s) => s.turnaroundHours)
    .reduce((a, b) => a > b ? a : b);
    
DateTime deliveryTime = pickupTime.add(Duration(hours: maxTurnaround));
```

**API :**
- `POST /api/laundry/request`

**Payload :**
```json
{
  "items": [
    {"laundry_service_id": 1, "quantity": 3},
    {"laundry_service_id": 4, "quantity": 1}
  ],
  "pickup_time": "2026-02-10 15:00",
  "special_instructions": "Traiter avec soin"
}
```

**Fichiers :**
- `lib/screens/laundry/create_request_screen.dart`
- `lib/widgets/laundry_item_selector.dart`

---

#### 8.3 Mes Demandes Blanchisserie ⏱️ 4h
**Écran :**
- Liste demandes
- Card : Numéro, Date, Nb articles, Total, Statut, Livraison estimée

**API :**
- `GET /api/my-laundry-requests`

**Fichiers :**
- `lib/screens/laundry/my_requests_screen.dart`

---

### PHASE 9 : SERVICES PALACE (Semaine 5)

#### 9.1 Liste Services Palace ⏱️ 6h
**Écran :**
- Liste services
- Card : Image, Nom, Catégorie, Prix (ou "Sur demande")
- Badge "Premium" (gold) pour services VIP
- Filtres : Catégorie, Premium

**API :**
- `GET /api/palace-services?category={category}&premium={1|0}`

**Fichiers :**
- `lib/screens/palace/palace_services_screen.dart`
- `lib/widgets/palace_service_card.dart`
- `lib/providers/palace_provider.dart`

---

#### 9.2 Détail Service Palace ⏱️ 4h
**Écran :**
- Image
- Nom, Catégorie, Description
- Prix ou "Prix sur demande"
- Badge Premium
- Bouton "Demander ce service"

**API :**
- `GET /api/palace-services/{id}`

**Fichiers :**
- `lib/screens/palace/palace_service_detail_screen.dart`

---

#### 9.3 Demande Service Palace ⏱️ 8h
**Écran :**
- Sélecteur date/heure souhaités
- Champ "Description détaillée de la demande" (obligatoire)
- Champ "Exigences spéciales" (optionnel)
- Affichage prix estimé (si disponible)
- Bouton "Envoyer la demande"

**API :**
- `POST /api/palace-services/{id}/request`

**Payload :**
```json
{
  "requested_for": "2026-02-12 18:00",
  "description": "Besoin d'un chauffeur pour aéroport",
  "special_requirements": "Véhicule premium SVP"
}
```

**Fichiers :**
- `lib/screens/palace/request_palace_screen.dart`

---

#### 9.4 Mes Demandes Palace ⏱️ 4h
**Écran :**
- Liste demandes
- Card : Service, Numéro, Date, Prix, Statut

**API :**
- `GET /api/my-palace-requests`

**Fichiers :**
- `lib/screens/palace/my_palace_requests_screen.dart`

---

### PHASE 10 : PROFIL & PARAMÈTRES (Semaine 5-6)

#### 10.1 Profil Utilisateur ⏱️ 6h
**Écran :**
- Photo de profil (placeholder)
- Nom, Email
- Hôtel actuel
- Numéro de chambre
- Bouton "Modifier le profil"
- Bouton "Changer mot de passe"
- Bouton "Déconnexion" (rouge)

**API :**
- `GET /api/auth/profile`

**Fichiers :**
- `lib/screens/profile/profile_screen.dart`

---

#### 10.2 Changer Mot de Passe ⏱️ 4h
**Écran :**
- Champ "Mot de passe actuel"
- Champ "Nouveau mot de passe"
- Champ "Confirmer nouveau mot de passe"
- Validation en temps réel
- Bouton "Enregistrer"

**API :**
- `POST /api/auth/change-password`

**Payload :**
```json
{
  "current_password": "passer123",
  "password": "nouveauMDP123",
  "password_confirmation": "nouveauMDP123"
}
```

**Fichiers :**
- `lib/screens/profile/change_password_screen.dart`

---

#### 10.3 Notifications ⏱️ 4h
**Écran :**
- Liste de toutes les notifications reçues
- Card : Titre, Message, Date/Heure, Icon
- Badge "Non lu"
- Tap pour marquer comme lu
- Action selon type de notification

**Stockage Local :**
- Sauvegarder notifications dans `Hive`

**Fichiers :**
- `lib/screens/notifications/notifications_screen.dart`
- `lib/widgets/notification_tile.dart`
- `lib/services/local_notification_service.dart`

---

### PHASE 11 : NOTIFICATIONS PUSH (Semaine 6)

#### 11.1 Gestion Notifications ⏱️ 8h

**Types de Notifications :**
1. **Nouvelle commande confirmée**
   ```json
   {
     "title": "Commande confirmée",
     "body": "Votre commande #CMD-20260210-001 est confirmée",
     "type": "order_confirmed",
     "order_id": 123
   }
   ```
   **Action :** Ouvrir détail commande

2. **Changement statut commande**
   ```json
   {
     "title": "Commande en préparation",
     "body": "Votre commande #CMD-20260210-001 est en cours de préparation",
     "type": "order_status",
     "order_id": 123
   }
   ```
   **Action :** Ouvrir détail commande

3. **Réservation confirmée**
   ```json
   {
     "title": "Réservation confirmée",
     "body": "Votre réservation au Restaurant Gastronomique est confirmée",
     "type": "reservation_confirmed",
     "reservation_id": 45,
     "reservation_type": "restaurant"
   }
   ```
   **Action :** Ouvrir détail réservation

4. **Rappel réservation**
   ```json
   {
     "title": "Rappel : Réservation aujourd'hui",
     "body": "Votre réservation spa est à 14h aujourd'hui",
     "type": "reservation_reminder",
     "reservation_id": 78
   }
   ```

**Handlers :**
- Foreground : Afficher banner notification
- Background : Sauvegarder dans local DB
- Terminated : Sauvegarder et naviguer au tap

**Fichiers :**
- `lib/services/push_notification_service.dart`
- `lib/handlers/notification_handler.dart`

---

### PHASE 12 : OFFLINE & OPTIMISATIONS (Semaine 6)

#### 12.1 Mode Offline ⏱️ 8h

**Fonctionnalités Offline :**
- Voir le panier (stocké localement)
- Voir historique commandes (cache)
- Voir réservations (cache)
- Afficher profil (cache)

**Synchronisation :**
- Auto-sync au retour online
- Indicator "Mode Hors ligne" en header

**Packages :**
- `connectivity_plus` - Détecter connexion
- `hive` - Database locale

**Fichiers :**
- `lib/services/connectivity_service.dart`
- `lib/services/sync_service.dart`

---

#### 12.2 Caching Images ⏱️ 4h

**Package :**
- `cached_network_image`

**Configuration :**
```dart
CachedNetworkImage(
  imageUrl: imageUrl,
  placeholder: (context, url) => ShimmerLoading(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  fadeInDuration: Duration(milliseconds: 300),
)
```

**Fichiers :**
- `lib/widgets/cached_image.dart`

---

#### 12.3 State Management ⏱️ 6h

**Provider Pattern :**
- `AuthProvider` - Gestion authentification
- `CartProvider` - Gestion panier
- `OrdersProvider` - Gestion commandes
- `RestaurantsProvider` - Gestion restaurants
- etc.

**Package :**
- `provider` (déjà installé)

**Structure :**
```
lib/
  providers/
    auth_provider.dart
    cart_provider.dart
    orders_provider.dart
    restaurants_provider.dart
    spa_provider.dart
    ...
```

---

### PHASE 13 : TESTS & DÉBOGAGE (Semaine 7)

#### 13.1 Tests Unitaires ⏱️ 8h
- Tests services API
- Tests providers
- Tests modèles

**Fichiers :**
- `test/services/auth_service_test.dart`
- `test/providers/cart_provider_test.dart`

---

#### 13.2 Tests d'Intégration ⏱️ 8h
- Workflow complet commande
- Workflow réservation
- Workflow authentification

**Fichiers :**
- `integration_test/order_flow_test.dart`

---

#### 13.3 Tests UI ⏱️ 4h
- Tests widgets
- Tests navigation

---

### PHASE 14 : DÉPLOIEMENT (Semaine 7-8)

#### 14.1 Build Android ⏱️ 4h
- Configuration `build.gradle`
- Génération keystore
- Build APK/AAB
- Test sur devices réels

---

#### 14.2 Build iOS ⏱️ 4h
- Configuration Xcode
- Certificates & Provisioning
- Build IPA
- Test sur iPhone/iPad

---

#### 14.3 Publication Stores ⏱️ 8h
- Google Play Console
- App Store Connect
- Screenshots
- Descriptions
- Soumission review

---

## 📦 PACKAGES FLUTTER NÉCESSAIRES

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # UI
  cupertino_icons: ^1.0.6
  google_fonts: ^6.1.0
  
  # State Management
  provider: ^6.1.1
  
  # Navigation
  go_router: ^13.0.0
  
  # HTTP & API
  dio: ^5.4.0
  
  # Stockage
  shared_preferences: ^2.2.2
  flutter_secure_storage: ^9.0.0
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  
  # Firebase
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.10
  flutter_local_notifications: ^16.3.0
  
  # Images
  cached_network_image: ^3.3.1
  image_picker: ^1.0.7
  
  # Utils
  intl: ^0.19.0
  connectivity_plus: ^5.0.2
  url_launcher: ^6.2.3
  permission_handler: ^11.2.0
  
  # Animations
  lottie: ^3.0.0
  shimmer: ^3.0.0

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.1
  hive_generator: ^2.0.1
  build_runner: ^2.4.8
```

---

## 📊 ESTIMATION TEMPS TOTAL

| Phase | Durée Estimée | Semaines |
|-------|---------------|----------|
| Phase 1 : Auth & Setup | 14h | 1 |
| Phase 2 : Dashboard | 12h | 1 |
| Phase 3 : Room Service | 26h | 2 |
| Phase 4 : Commandes | 12h | 1 |
| Phase 5 : Restaurants | 24h | 2 |
| Phase 6 : Spa | 24h | 2 |
| Phase 7 : Excursions | 24h | 2 |
| Phase 8 : Blanchisserie | 18h | 1.5 |
| Phase 9 : Palace | 22h | 1.5 |
| Phase 10 : Profil | 14h | 1 |
| Phase 11 : Notifications | 8h | 0.5 |
| Phase 12 : Optimisations | 18h | 1.5 |
| Phase 13 : Tests | 20h | 1.5 |
| Phase 14 : Déploiement | 16h | 1 |
| **TOTAL** | **252h** | **~8 semaines** |

**Avec 1 développeur à 6h/jour = 42 jours ≈ 8-9 semaines**

---

## 🎯 PRIORITÉS

### Must Have (MVP - 4 semaines)
1. ✅ Authentification
2. ✅ Dashboard
3. ✅ Room Service complet (panier + commande)
4. ✅ Mes commandes
5. ✅ Notifications push basiques
6. ✅ Profil

### Should Have (5-6 semaines)
7. ✅ Restaurants & réservations
8. ✅ Spa & réservations
9. ✅ Excursions
10. ✅ Blanchisserie

### Nice to Have (7-8 semaines)
11. ✅ Services Palace
12. ✅ Mode offline avancé
13. ✅ Tests complets
14. ✅ Animations avancées

---

## 📱 ÉCRANS TOTAUX : ~35 ÉCRANS

1. Splash Screen
2. Login
3. Dashboard
4. Room Service - Catégories
5. Room Service - Articles
6. Room Service - Détail Article
7. Room Service - Panier
8. Room Service - Confirmation
9. Commandes - Liste
10. Commandes - Détail
11. Restaurants - Liste
12. Restaurants - Détail
13. Restaurants - Réservation
14. Restaurants - Mes Réservations
15. Spa - Liste Services
16. Spa - Détail Service
17. Spa - Réservation
18. Spa - Mes Réservations
19. Excursions - Liste
20. Excursions - Détail
21. Excursions - Booking
22. Excursions - Mes Bookings
23. Blanchisserie - Services
24. Blanchisserie - Demande
25. Blanchisserie - Mes Demandes
26. Palace - Services
27. Palace - Détail
28. Palace - Demande
29. Palace - Mes Demandes
30. Profil
31. Modifier Profil
32. Changer Mot de Passe
33. Notifications
34. Réservations (Vue consolidée)
35. Paramètres

---

## 🚀 COMMANDES UTILES

### Créer le projet
```bash
cd /path/to/terangaguest
flutter create terangaguest_app
cd terangaguest_app
```

### Installer dépendances
```bash
flutter pub get
```

### Générer code Hive
```bash
flutter packages pub run build_runner build
```

### Run app
```bash
flutter run
```

### Build APK
```bash
flutter build apk --release
```

### Build iOS
```bash
flutter build ios --release
```

---

## 📝 NOTES IMPORTANTES

1. **Design Cohérent :** Utiliser le design system défini (bleu marine + gold)
2. **Responsive :** Tester sur différentes tailles d'écran
3. **Accessibilité :** Ajouter labels pour screen readers
4. **Performance :** Optimiser images, lazy loading
5. **Sécurité :** Ne jamais stocker token en clair
6. **UX :** Feedback visuel immédiat pour toutes les actions
7. **Erreurs :** Messages d'erreur clairs et en français

---

**📱 APPLICATION MOBILE TERANGA GUEST - PRÊTE POUR LE DÉVELOPPEMENT ! 🚀**
