# Notifications push – Vue d’ensemble Web & Mobile

Ce document recense **tous les dossiers, fichiers et paramétrages** liés aux notifications (FCM / Firebase) côté **backend Laravel** et **app Flutter**.

---

## Côté Web (Laravel)

### 1. Fichiers et dossiers

| Rôle | Chemin |
|------|--------|
| **Service d’envoi** | `app/Services/FirebaseNotificationService.php` |
| **Contrôleur API FCM** | `app/Http/Controllers/Api/FcmTokenController.php` |
| **Provider Firebase** | `app/Providers/FirebaseServiceProvider.php` |
| **Credentials Firebase** | `storage/app/firebase/credentials.json` (ou chemin défini dans `.env`) |
| **Migration FCM (users)** | `database/migrations/2026_02_02_163904_add_fcm_token_to_users_table.php` |

### 2. Paramétrage (config / env)

| Élément | Fichier / Variable | Détail |
|--------|---------------------|--------|
| **Provider enregistré** | `bootstrap/providers.php` | `App\Providers\FirebaseServiceProvider::class` |
| **Credentials** | `.env` | `FIREBASE_CREDENTIALS=` chemin vers le JSON (ex. `storage/app/firebase/credentials.json`) |
| **Project ID** | `.env` | `FIREBASE_PROJECT_ID=terangaguest` (optionnel selon usage) |

Exemple dans `.env` :

```env
FIREBASE_CREDENTIALS=storage/app/firebase/credentials.json
FIREBASE_PROJECT_ID=terangaguest
```

### 3. Routes API (FCM)

Dans `routes/api.php`, sous le groupe `auth:sanctum` :

| Méthode | URI | Contrôleur | Rôle |
|---------|-----|------------|------|
| `POST` | `/api/fcm-token` | `FcmTokenController@store` | Enregistrer ou mettre à jour le token FCM |
| `DELETE` | `/api/fcm-token` | `FcmTokenController@destroy` | Supprimer le token (déconnexion) |

### 4. Base de données

- **Table** : `users`
- **Colonnes** : `fcm_token` (nullable), `fcm_token_updated_at` (nullable)

### 5. Où les notifications sont envoyées (backend)

Le service `FirebaseNotificationService` est utilisé dans :

- `app/Http/Controllers/Api/TabletSessionController.php` – nouvelle commande (checkout tablette)
- `app/Http/Controllers/Api/RoomServiceController.php` – nouvelle commande (room service auth) + alerte staff
- `app/Http/Controllers/Dashboard/OrderController.php` – changement de statut de commande (`notifyOrderStatusToClient`)
- `app/Http/Controllers/Api/RestaurantController.php` – confirmation réservation restaurant
- `app/Http/Controllers/Api/SpaServiceController.php` – confirmation réservation spa
- `app/Http/Controllers/Api/ExcursionController.php` – confirmation excursion
- `app/Http/Controllers/Api/LaundryServiceController.php` – confirmation demande blanchisserie
- `app/Http/Controllers/Api/PalaceServiceController.php` – confirmation demande palace

---

## Côté Mobile (Flutter)

### 1. Fichiers et dossiers

| Rôle | Chemin |
|------|--------|
| **Service FCM** | `terangaguest_app/lib/services/fcm_service.dart` |
| **Config API (endpoint FCM)** | `terangaguest_app/lib/config/api_config.dart` → `fcmToken` |
| **Initialisation Firebase** | `terangaguest_app/lib/main.dart` → `Firebase.initializeApp()` |
| **Enregistrement / suppression token** | `terangaguest_app/lib/providers/auth_provider.dart` (login / init / logout) |
| **Config Android** | `terangaguest_app/android/app/google-services.json` |
| **Config iOS** | `terangaguest_app/ios/Runner/GoogleService-Info.plist` |

### 2. Dépendances (pubspec.yaml)

```yaml
firebase_core: ^3.8.1
firebase_messaging: ^15.1.6
```

### 3. Paramétrage (config)

| Élément | Fichier | Détail |
|--------|---------|--------|
| **Endpoint FCM** | `lib/config/api_config.dart` | `static const String fcmToken = '/fcm-token';` (relatif à `baseUrl`) |
| **Base URL API** | `lib/config/api_config.dart` | `baseUrl` (ex. `https://teranguest.../api`) |

### 4. Flux d’utilisation

- **Au démarrage** : `main.dart` → `Firebase.initializeApp()`.
- **Après login / init auth** : `AuthProvider` appelle `_fcmService.registerTokenIfNeeded()` (récupère le token FCM, envoie `POST /api/fcm-token` avec le token).
- **À la déconnexion** : `AuthProvider.logout()` appelle `_fcmService.unregisterToken()` (`DELETE /api/fcm-token`).

### 5. Réception des notifications (état actuel)

- **Envoi** : le backend envoie les notifications via Firebase (FCM).
- **Réception** : gérée par le SDK Firebase (notification en barre de statut quand l’app est en arrière-plan ou fermée).
- **À compléter si besoin** : écoute explicite en premier plan (`FirebaseMessaging.onMessage`), clic sur notification (`onMessageOpenedApp`), navigation vers un écran (ex. détail commande). Voir `docs/FIREBASE-CONFIGURATION.md` pour des exemples de code.

---

## Checklist rapide

### Web

- [ ] `storage/app/firebase/` existe et contient le fichier de credentials (ou chemin cohérent avec `FIREBASE_CREDENTIALS`).
- [ ] `.env` contient `FIREBASE_CREDENTIALS` et éventuellement `FIREBASE_PROJECT_ID`.
- [ ] `FirebaseServiceProvider` est listé dans `bootstrap/providers.php`.
- [ ] Migrations exécutées (colonnes `fcm_token`, `fcm_token_updated_at` sur `users`).
- [ ] Routes `POST/DELETE /api/fcm-token` protégées par `auth:sanctum`.

### Mobile

- [ ] `google-services.json` (Android) et `GoogleService-Info.plist` (iOS) présents et à jour.
- [ ] `Firebase.initializeApp()` dans `main.dart`.
- [ ] `FcmService` appelé après login et à l’init auth ; `unregisterToken` à la déconnexion.
- [ ] `api_config.dart` : `baseUrl` et `fcmToken` corrects pour l’environnement (prod / dev).

---

## Debug : pas de notification push reçue sur l’app

Quand le web change le statut d’une commande (confirmée, en préparation, etc.) et que l’app ne reçoit pas de notification :

### 1. Vérifier le token FCM pour la chambre (backend)

À la racine du projet Laravel :

```bash
# Voir les chambres qui ont un token enregistré
php artisan fcm:check-room

# Vérifier une chambre précise (id ou numéro)
php artisan fcm:check-room 101
php artisan fcm:check-room 17
```

- Si **« Aucun token FCM »** : la tablette n’a jamais enregistré de token pour ce compte.
- **À faire sur la tablette** : se connecter avec le compte **« Client Chambre XXX »** (email/mot de passe de l’accès tablette pour cette chambre). Au login, l’app envoie le token au backend. Sans cette connexion au moins une fois, aucune push ne part.

### 2. Consulter les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

Puis sur le web, passer une commande à l’étape suivante (ex. « Passer à Préparation »). Vous devriez voir soit :

- `sendToClientOfRoom: notification sent to user_id=...` → envoi réussi.
- `getUserForRoom: no guest user with FCM token for room_id=...` → aucun compte chambre avec token pour cette chambre.
- `sendToClientOfRoom: no recipient for room_id=...` → même cause.

### 3. Côté mobile (Flutter)

- Lancer l’app en **debug** et regarder la console :
  - Après connexion : `FCM: token enregistré côté serveur` → envoi du token OK.
  - `FCM: permission non accordée` → autoriser les notifications dans les réglages du téléphone/tablette.
  - `FCM register error: 401` → problème d’auth (token Sanctum invalide, etc.).
- La tablette doit être **connectée avec le compte de la chambre** (celui créé dans Dashboard > Accès tablettes pour la chambre 101). Les comptes admin/staff ne reçoivent pas les notifications « client chambre ».

### 4. Checklist rapide

- [ ] Accès tablette créé pour la chambre (Dashboard > Accès tablettes) et chambre bien reliée.
- [ ] Sur la tablette : connexion avec ce compte (Client Chambre XXX).
- [ ] Notifications autorisées pour l’app (réglages système).
- [ ] `php artisan fcm:check-room 101` indique un token pour cette chambre.
- [ ] Les logs Laravel ne montrent pas d’erreur Firebase (credentials, réseau).

---

## Documentation complémentaire

- **Configuration Firebase détaillée** : `docs/FIREBASE-CONFIGURATION.md`
- **Résumé validation client / notifications** : `docs/NOTIFICATIONS-CLIENT-RESUME-VALIDATION.md`
