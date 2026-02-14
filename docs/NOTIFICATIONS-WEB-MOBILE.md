# Notifications push – Vue d’ensemble Web & Mobile

Ce document recense **tous les dossiers, fichiers et paramétrages** liés aux notifications (FCM / Firebase) côté **backend Laravel** et **app Flutter**.

---

## Processus – Envoi et réception des notifications

### 1. Configuration initiale (une fois)

| Étape | Où | Action |
|-------|-----|--------|
| 1.1 | Firebase Console | Télécharger le JSON du compte de service (Paramètres projet > Comptes de service > Générer une clé). |
| 1.2 | Serveur Laravel | Placer le fichier à la racine ou dans `storage/app/firebase/` (ex. `teranguest-74262-xxx.json`). |
| 1.3 | `.env` | Définir `FIREBASE_CREDENTIALS=...` et `FIREBASE_PROJECT_ID=...`. |
| 1.4 | Google Cloud | Activer l’API « Firebase Cloud Messaging API (V1) » pour le projet. |
| 1.5 | Dashboard Laravel | Créer les **accès tablettes** (utilisateurs « Client Chambre XXX ») et les lier aux chambres. |

### 2. Côté mobile – Enregistrement du token FCM

| Moment | Ce qui se passe |
|--------|------------------|
| **Au démarrage** | L’app initialise Firebase (`Firebase.initializeApp()`). |
| **Après connexion** (ou au lancement si déjà connecté) | L’app demande la permission notifications (iOS), récupère le **token FCM** du device, envoie `POST /api/fcm-token` avec ce token (auth Sanctum). Le backend enregistre `fcm_token` sur l’utilisateur connecté. |
| **À la déconnexion** | L’app envoie `DELETE /api/fcm-token` ; le backend met `fcm_token` à `null`. |

**Important** : Pour qu’une **chambre** reçoive les notifications, la tablette doit être **connectée au moins une fois** avec le compte « Client Chambre XXX » de cette chambre, afin que le token soit associé à ce compte en base.

### 3. Côté backend – Quand les notifications sont envoyées

| Événement | Où c’est déclenché | Qui reçoit |
|-----------|---------------------|------------|
| **Changement de statut de commande** (confirmée, en préparation, prête, etc.) | Dashboard > Commandes : clic sur le statut | Client de la chambre liée à la commande |
| **Nouvelle commande** (depuis tablette ou room service) | TabletSessionController, RoomServiceController | Client de la chambre |
| **Confirmation réservation restaurant** | RestaurantController | Client de la chambre |
| **Confirmation réservation spa** | SpaServiceController | Client de la chambre |
| **Confirmation excursion** | ExcursionController | Client de la chambre |
| **Demande blanchisserie** | LaundryServiceController | Client de la chambre |
| **Demande palace** | PalaceServiceController | Client de la chambre |

Le backend détermine le destinataire via **`getUserForRoom(room_id)`** : utilisateur **guest** avec `room_id` (ou `room_number`) correspondant et `fcm_token` non vide.

### 4. Commandes de test (backend)

```bash
php artisan fcm:test                    # Vérifier credentials
php artisan fcm:test --user=6           # Envoyer une notif test à l’user 6
php artisan fcm:check-room              # Chambres avec token FCM
php artisan fcm:check-room 101          # Vérifier la chambre 101
php artisan fcm:test --curl-oneline --user=6   # Commande curl (diagnostic proxy)
```

### 5. Limitation hébergement (proxy / 401)

Sur certains hébergements, un **proxy** supprime le header `Authorization` pour les requêtes vers `fcm.googleapis.com` → **401 THIRD_PARTY_AUTH_ERROR**.

- **Vérifier** : lancer la commande curl générée par `php artisan fcm:test --curl-oneline --user=6` sur le serveur. Si **HTTP_CODE:401**, le blocage est entre le serveur et Google.
- **Solutions** : demander à l’hébergeur de transmettre le header `Authorization` vers `fcm.googleapis.com`, ou envoyer les notifications depuis un **autre serveur** (VPS) avec le même code et credentials.

Détail : `docs/FIREBASE-CONFIGURATION.md` (section « Erreur 401 THIRD_PARTY_AUTH_ERROR »).

---

## Côté Web (Laravel)

### 1. Fichiers et dossiers

| Rôle | Chemin |
|------|--------|
| **Service d’envoi** | `app/Services/FirebaseNotificationService.php` |
| **Contrôleur API FCM** | `app/Http/Controllers/Api/FcmTokenController.php` |
| **Provider Firebase** | `app/Providers/FirebaseServiceProvider.php` |
| **Credentials Firebase** | Racine du projet **ou** `storage/app/firebase/` (nom du fichier dans `.env`) |
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
| **Base URL API** | `lib/config/api_config.dart` | `baseUrl` (ex. `https://teranguest.com/api`) |

### 4. Flux d’utilisation (où le token est récupéré et enregistré)

- **Au démarrage** : `main.dart` → `Firebase.initializeApp()`.
- **Après login ou au démarrage si déjà connecté** : `AuthProvider` appelle `_fcmService.registerTokenIfNeeded()` :
  - demande la permission notification (iOS),
  - récupère le token FCM du device,
  - envoie `POST /api/fcm-token` avec ce token (authentification Bearer = utilisateur connecté).
- **À la déconnexion** : `AuthProvider.logout()` appelle `_fcmService.unregisterToken()` (`DELETE /api/fcm-token`).

Donc le token est enregistré **uniquement pour l’utilisateur actuellement connecté**. Pour recevoir les notifications d’une chambre, la tablette doit être **connectée avec le compte « Client Chambre XXX »** (accès tablette) au moins une fois, afin que le token soit associé à ce compte en base.

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

## Tester les credentials Firebase (backend)

En cas d’erreur « Request is missing required authentication credential », vérifier que le fichier credentials est bien chargé :

```bash
php artisan fcm:test
```

Si tout est OK, vous verrez « Credentials chargés avec succès ». Pour envoyer une notification test à un utilisateur (ex. user_id 6) :

```bash
php artisan fcm:test --user=6
```

Vérifier aussi dans `storage/logs/laravel.log` la ligne « Firebase credentials loaded » avec le chemin du fichier. Si cette ligne n’apparaît pas lors d’un envoi, vider le cache : `php artisan config:clear` puis réessayer.

---

## Debug : pas de notification push reçue sur l’app

Quand le web change le statut d’une commande (confirmée, en préparation, etc.) et que l’app ne reçoit pas de notification :

### 0. Erreur « Request is missing required authentication credential »

Côté **backend** (credentials non pris en compte) :

1. **Tester le chargement** : `php artisan fcm:test`. Si ça échoue, le fichier credentials n’est pas trouvé ou est invalide.
2. **Vérifier le chemin** : dans `storage/logs/laravel.log`, chercher « Firebase credentials loaded » (le chemin absolu du fichier doit être correct).
3. **Emplacement du fichier** : le fichier doit être soit à la **racine du projet**, soit dans **`storage/app/firebase/`**. Le nom dans `.env` doit correspondre (ex. `FIREBASE_CREDENTIALS=teranguest-74262-bad96dcbc8cd.json`).
4. **En production** : s’assurer que le fichier JSON est bien déployé sur le serveur (il n’est pas dans Git) et que le chemin est le bon.
5. **Cache** : exécuter `php artisan config:clear` puis réessayer.

Côté **mobile** : le token est récupéré et envoyé au backend **uniquement après connexion** (login ou au démarrage si déjà connecté). Voir la section « Flux d’utilisation » ci‑dessus.

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

## Déploiement sur le serveur (Firebase credentials)

Le fichier de credentials Firebase **ne doit pas** être dans Git (il est dans `.gitignore`). Sur le serveur, faire une des deux options suivantes.

### Option A : Fichier à la racine du projet (recommandé)

1. **Sur le serveur**, dans le dossier du projet (même niveau que `composer.json`, `.env`) :
   - Envoyer le fichier `teranguest-74262-bad96dcbc8cd.json` (SFTP, SCP, ou créer le fichier et coller le contenu).
   - Exemple : `/var/www/terangaguest/teranguest-74262-bad96dcbc8cd.json`

2. **Dans le `.env` du serveur** (créer ou modifier) :
   ```env
   FIREBASE_CREDENTIALS=teranguest-74262-bad96dcbc8cd.json
   FIREBASE_PROJECT_ID=terangaguest
   ```

3. Vérifier les droits : le serveur web (ex. `www-data`) doit pouvoir lire le fichier :
   ```bash
   chmod 640 teranguest-74262-bad96dcbc8cd.json
   chown www-data:www-data teranguest-74262-bad96dcbc8cd.json
   ```

### Option B : Fichier dans `storage/app/firebase/`

1. **Sur le serveur** :
   ```bash
   mkdir -p storage/app/firebase
   ```
   Puis placer le JSON dans `storage/app/firebase/teranguest-74262-bad96dcbc8cd.json`.

2. **Dans le `.env` du serveur** :
   ```env
   FIREBASE_CREDENTIALS=teranguest-74262-bad96dcbc8cd.json
   FIREBASE_PROJECT_ID=terangaguest
   ```
   Le provider cherche aussi dans `storage/app/firebase/` si le fichier n’est pas à la racine.

3. **Droits** :
   ```bash
   chmod 640 storage/app/firebase/teranguest-74262-bad96dcbc8cd.json
   chown -R www-data:www-data storage/app/firebase
   ```

### Chemin absolu (optionnel)

Si tu préfères un chemin absolu dans `.env` (selon l’OS du serveur) :

- Linux : `FIREBASE_CREDENTIALS=/var/www/terangaguest/teranguest-74262-bad96dcbc8cd.json`
- Le provider utilise `realpath()` : un chemin absolu valide est pris tel quel.

### À ne pas faire

- Ne **pas** committer le fichier JSON dans Git (déjà ignoré via `teranguest-*.json`).
- Ne **pas** le mettre dans `public/` (accessible par URL = fuite de clé).

---

## Documentation complémentaire

- **Configuration Firebase détaillée** : `docs/FIREBASE-CONFIGURATION.md`
- **Résumé validation client / notifications** : `docs/NOTIFICATIONS-CLIENT-RESUME-VALIDATION.md`
