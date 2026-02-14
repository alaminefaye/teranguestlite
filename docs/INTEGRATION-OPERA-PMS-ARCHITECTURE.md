# Intégration Opera PMS avec TerangaGuest – Architecture & Solution Robuste

## 1. Contexte

### Situation
- **Opera PMS** : système Oracle de gestion hôtelière déjà utilisé par vos clients (hôtels partenaires).
- **Restriction** : vous n'avez **pas le droit de modifier** la base de données Oracle d'Opera.
- **Fourniture** : le partenaire (Orange ou l'hôtel) vous donnera accès au **PMS / API d'Opera** pour récupérer les données.
- **Objectif** : intégrer Opera avec votre application TerangaGuest **sans casser** votre projet actuel, tout en récupérant les vraies informations (réservations, clients, chambres).

---

## 2. Opera PMS – Ce qu'il offre

### 2.1 OHIP (Oracle Hospitality Integration Platform)
- **API REST** pour accéder aux données d'Opera Cloud (réservations, clients, chambres, folios).
- Plus de **2000+ recettes d'intégration** préconçues.
- Support des événements métier (webhooks) :
  - **NEW RESERVATION** : nouvelle réservation créée.
  - **UPDATE RESERVATION** : réservation modifiée.
  - **CHECK IN / CHECK OUT** : arrivée/départ du client.
  - **CANCEL** : annulation de réservation.
- **OAuth 2.0** pour sécuriser les connexions.

### 2.2 APIs Opera disponibles
- **Reservations API** : récupérer, créer, modifier des réservations.
- **Guests API** : profils clients (nom, email, téléphone, documents).
- **Rooms API** : statut des chambres (occupée, disponible, sale, hors service).
- **Folios API** : facturation, charges, paiements.

### 2.3 Opera on-premise (version locale)
- **OXI (Opera Exchange Interface)** : export XML/CSV des données vers des systèmes tiers.
- **OHIP Adapter** : connecter Opera on-premise à OHIP Cloud pour utiliser les APIs REST.

---

## 3. Architecture recommandée pour TerangaGuest

### Principe : **Hybride avec synchronisation unidirectionnelle**
Opera reste la **source de vérité** pour :
- Réservations de chambres (check-in, check-out, statut).
- Clients (guests) et leurs informations.
- Statut des chambres.

TerangaGuest gère :
- Services additionnels (room service, spa, restaurant, excursions, blanchisserie, palace).
- Commandes et demandes des clients (non dans Opera).

### Flux de données
```
Opera PMS (source de vérité)
    ↓ (API / Webhooks)
TerangaGuest (synchronisation) 
    → Tables "miroirs" : opera_reservations, opera_guests, opera_rooms
    → Tables actuelles : guests, reservations, rooms (liées par opera_id)
```

---

## 4. Solution technique robuste

### 4.1 Nouvelle structure de base de données

#### Tables "miroir" Opera (en lecture seule)
Créer des tables pour stocker les données synchronisées depuis Opera, **sans modifier** vos tables actuelles :

```sql
-- Réservations Opera
CREATE TABLE opera_reservations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    enterprise_id BIGINT NOT NULL,
    opera_reservation_id VARCHAR(50) NOT NULL UNIQUE,  -- ID dans Opera
    opera_confirmation_number VARCHAR(50),
    guest_name VARCHAR(255),
    guest_email VARCHAR(255),
    guest_phone VARCHAR(50),
    room_number VARCHAR(20),
    check_in DATETIME,
    check_out DATETIME,
    status VARCHAR(50),  -- confirmed, checked_in, checked_out, cancelled, no_show
    number_of_guests INT,
    rate_amount DECIMAL(10,2),
    raw_data JSON,  -- Données complètes d'Opera pour référence
    synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_enterprise (enterprise_id),
    INDEX idx_opera_id (opera_reservation_id),
    INDEX idx_room_number (room_number),
    INDEX idx_dates (check_in, check_out)
);

-- Clients Opera
CREATE TABLE opera_guests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    enterprise_id BIGINT NOT NULL,
    opera_guest_id VARCHAR(50) NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    nationality VARCHAR(100),
    id_document_type VARCHAR(50),
    id_document_number VARCHAR(100),
    raw_data JSON,
    synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_enterprise_opera_guest (enterprise_id, opera_guest_id),
    INDEX idx_email (email),
    INDEX idx_phone (phone)
);

-- Chambres Opera
CREATE TABLE opera_rooms (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    enterprise_id BIGINT NOT NULL,
    opera_room_id VARCHAR(50) NOT NULL,
    room_number VARCHAR(20),
    room_type VARCHAR(100),
    status VARCHAR(50),  -- vacant, occupied, dirty, out_of_order
    floor INT,
    features JSON,
    raw_data JSON,
    synced_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_enterprise_opera_room (enterprise_id, opera_room_id),
    INDEX idx_room_number (room_number)
);
```

#### Lien avec vos tables actuelles
Ajouter des colonnes de mapping dans vos tables existantes (sans les modifier structurellement) :

```sql
-- Table guests (clients TerangaGuest)
ALTER TABLE guests ADD COLUMN opera_guest_id VARCHAR(50) NULL;
ALTER TABLE guests ADD INDEX idx_opera_guest (opera_guest_id);

-- Table reservations (réservations de chambre TerangaGuest)
ALTER TABLE reservations ADD COLUMN opera_reservation_id VARCHAR(50) NULL;
ALTER TABLE reservations ADD INDEX idx_opera_reservation (opera_reservation_id);

-- Table rooms
ALTER TABLE rooms ADD COLUMN opera_room_id VARCHAR(50) NULL;
ALTER TABLE rooms ADD INDEX idx_opera_room (opera_room_id);
```

### 4.2 Service de synchronisation

Créer un service dédié (`app/Services/OperaSyncService.php`) qui :

1. **Récupère les données d'Opera** via l'API OHIP (ou webhooks).
2. **Enregistre dans les tables miroirs** (`opera_reservations`, `opera_guests`, `opera_rooms`).
3. **Crée ou met à jour vos tables actuelles** (`guests`, `reservations`, `rooms`) en liant par `opera_*_id`.
4. **Génère le code client** (access_code) pour les nouveaux guests synchronisés.

### 4.3 Scénarios de synchronisation

#### Scénario A : Synchronisation périodique (Cron)
- **Tous les X minutes** (ex. 5 min), un cron Laravel appelle l'API Opera pour récupérer les réservations modifiées depuis la dernière sync.
- Avantage : simple, robuste.
- Inconvénient : délai de 5 min entre Opera et TerangaGuest.

#### Scénario B : Webhooks Opera (temps réel)
- Opera envoie des webhooks à votre API lors d'événements (nouvelle réservation, check-in, check-out, annulation).
- Votre endpoint `/api/opera/webhook` reçoit les données et synchronise immédiatement.
- Avantage : temps réel.
- Inconvénient : nécessite que l'hôtel/Orange configure les webhooks Opera vers votre serveur.

#### Scénario C : Hybride (recommandé)
- **Webhooks** pour les événements critiques (nouvelle réservation, check-in, check-out).
- **Cron** (toutes les 30 min) pour rattraper les éventuels webhooks manqués et synchroniser le statut des chambres.

---

## 5. Flux d'intégration détaillé

### 5.1 Nouvelle réservation dans Opera

1. **Opera** : réceptionniste crée une réservation (client Jean Dupont, chambre 101, 10-15 février).
2. **Webhook/API** : Opera envoie un événement `NEW RESERVATION` ou votre cron récupère la nouvelle réservation.
3. **OperaSyncService** :
   - Enregistre dans `opera_reservations` (opera_reservation_id = "12345", check_in, check_out, etc.).
   - Cherche si un `Guest` existe déjà avec l'email/téléphone du client Opera.
     - Si **non** → crée un nouveau `Guest` dans votre table `guests` avec `opera_guest_id` et génère un **code client** (access_code).
     - Si **oui** → met à jour le `opera_guest_id`.
   - Cherche si une `Room` existe avec ce `room_number`.
     - Si **non** → crée la chambre dans `rooms` avec `opera_room_id`.
     - Si **oui** → met à jour le `opera_room_id`.
   - Crée une `Reservation` dans votre table `reservations` avec `opera_reservation_id`, liée au `guest_id` et `room_id`.
4. **Résultat** : le client peut maintenant utiliser son **code client** (généré par TerangaGuest) dans la tablette/app pour commander room service, réserver spa, etc. Les informations de réservation (dates, chambre) viennent d'Opera.

### 5.2 Check-in dans Opera

1. **Opera** : réceptionniste fait le check-in du client.
2. **Webhook/API** : événement `CHECK IN`.
3. **OperaSyncService** :
   - Met à jour `opera_reservations.status = 'checked_in'`.
   - Met à jour `reservations.status = 'checked_in'` dans TerangaGuest.
   - Met à jour `rooms.status = 'occupied'`.
4. **Résultat** : le séjour devient **actif** dans TerangaGuest (`check_in <= now <= check_out`), le client peut utiliser son code.

### 5.3 Check-out dans Opera

1. **Opera** : réceptionniste fait le check-out.
2. **Webhook/API** : événement `CHECK OUT`.
3. **OperaSyncService** :
   - Met à jour `opera_reservations.status = 'checked_out'`.
   - Met à jour `reservations.status = 'checked_out'` dans TerangaGuest.
   - Met à jour `rooms.status = 'vacant'` (ou `dirty` si Opera l'indique).
4. **Résultat** : le code client de ce séjour devient **invalide** (check_out dépassé), il ne peut plus commander.

### 5.4 Commande room service dans TerangaGuest

1. **Client** : commande depuis la tablette avec son code client.
2. **TerangaGuest** : vérifie que le code est valide + séjour actif (`reservations` où `opera_reservation_id` est lié).
3. **Ordre enregistré** dans `orders` de TerangaGuest (pas dans Opera).
4. **Facturation** : à la fin du séjour, vous pouvez **exporter les charges** (commandes, réservations spa) vers Opera via l'API "Folios" ou CSV, pour que l'hôtel les ajoute à la facture finale dans Opera.

---

## 6. Données à synchroniser

| Donnée | Source | Direction | Fréquence |
|--------|--------|-----------|-----------|
| Réservations de chambre | Opera → TerangaGuest | Unidirectionnelle | Webhooks + cron (30 min) |
| Clients (guests) | Opera → TerangaGuest | Unidirectionnelle | Webhooks + cron (30 min) |
| Statut des chambres | Opera → TerangaGuest | Unidirectionnelle | Cron (15 min) |
| Commandes (room service) | TerangaGuest seulement | Aucune (optionnel : export vers Opera folios) | — |
| Réservations spa/resto/excursions | TerangaGuest seulement | Aucune | — |

**Remarque** : les services additionnels (spa, restaurant, excursions) ne sont **pas dans Opera**, donc TerangaGuest est la source de vérité pour ces données. À la fin du séjour, vous pouvez exporter un récapitulatif de toutes les charges (commandes + réservations payantes) et le pousser vers Opera (API Folios) pour facturation centralisée dans Opera.

---

## 7. Architecture des services

### 7.1 Service `OperaSyncService.php`

Méthodes principales :
- **`syncReservations()`** : récupère les réservations depuis Opera (API ou webhook), met à jour `opera_reservations` et `reservations`.
- **`syncGuests()`** : récupère les profils clients, met à jour `opera_guests` et `guests`.
- **`syncRooms()`** : récupère le statut des chambres, met à jour `opera_rooms` et `rooms`.
- **`handleWebhook($event)`** : reçoit un webhook Opera (NEW_RESERVATION, CHECK_IN, etc.) et déclenche la sync ciblée.
- **`linkOrCreateGuest($operaGuestData)`** : cherche un guest existant (par email/phone) ou en crée un avec code client.
- **`linkOrCreateRoom($operaRoomData)`** : cherche une chambre existante (par room_number) ou en crée une.

### 7.2 Contrôleur webhook `OperaWebhookController.php`

```php
// Route : POST /api/opera/webhook
public function handle(Request $request) {
    $event = $request->input('event_type');  // NEW_RESERVATION, CHECK_IN, CHECK_OUT, CANCEL
    $data = $request->input('data');
    $enterpriseId = $this->identifyEnterprise($request);  // Via signature, header, ou config
    
    $syncService = app(OperaSyncService::class);
    $syncService->handleWebhook($event, $data, $enterpriseId);
    
    return response()->json(['success' => true]);
}
```

### 7.3 Commande artisan pour sync périodique

```bash
php artisan opera:sync --enterprise=1
```

Cron Laravel (dans `app/Console/Kernel.php`) :
```php
$schedule->command('opera:sync')->everyFifteenMinutes();
```

---

## 8. Exemple concret de flux

### Cas : Hôtel "Le Teranga" avec Opera

1. **Initialisation** :
   - L'hôtel vous donne l'accès OHIP (clés OAuth, URL de l'API, webhook URL).
   - Vous créez un `Enterprise` "Le Teranga" dans TerangaGuest avec une config `opera_api_url`, `opera_client_id`, `opera_client_secret`.

2. **Synchronisation initiale** (script one-time) :
   - Récupère toutes les réservations actives/futures d'Opera.
   - Importe dans `opera_reservations` + crée les `guests` et `rooms` liés.
   - Génère un code client pour chaque guest.

3. **Quotidien** :
   - **08:00** : réception crée une réservation dans Opera (client Marie, chambre 203, 12-14 fév).
   - **08:01** : webhook → TerangaGuest enregistre dans `opera_reservations` + crée `guests` (Marie) avec code **654321** + crée `reservations` liée.
   - **12:00** : réception fait le check-in dans Opera.
   - **12:01** : webhook → TerangaGuest met `reservations.status = 'checked_in'`.
   - **12:30** : Marie utilise la tablette chambre 203, entre le code **654321** → TerangaGuest vérifie la réservation (de type `opera_reservation_id` lié) → valide le séjour actif → elle peut commander.
   - **14:00** : Marie commande un massage spa depuis l'app → enregistré dans `spa_reservations` de TerangaGuest (pas dans Opera).
   - **14 fév 11:00** : réception fait le check-out dans Opera.
   - **14 fév 11:01** : webhook → TerangaGuest met `reservations.status = 'checked_out'` → code invalide.
   - **14 fév 11:30** : export des charges (room service, spa) vers Opera Folios (optionnel) pour facturation centralisée.

---

## 9. Avantages de cette architecture

| Avantage | Explication |
|----------|-------------|
| **Non invasif** | Vos tables actuelles ne sont pas cassées ; on ajoute seulement des colonnes `opera_*_id` pour lier. |
| **Source de vérité Opera** | Réservations/chambres/clients viennent d'Opera (pas de doublon, cohérence avec la réception). |
| **Services TerangaGuest** | Room service, spa, resto, excursions restent gérés par votre app (Opera ne les a pas). |
| **Résilience** | Si Opera est indisponible, les données miroirs restent en lecture (dernier état connu). |
| **Facturation centralisée** | À la fin du séjour, export des charges TerangaGuest vers Opera pour une facture unique. |
| **Multi-hôtel** | Chaque `enterprise` a sa propre config Opera (ou pas d'Opera si hôtel sans PMS externe). |

---

## 10. Ce qu'il ne faut PAS faire

- ❌ **Modifier directement la BDD Oracle d'Opera** : vous n'y avez pas accès et c'est interdit.
- ❌ **Dupliquer toute la logique d'Opera dans TerangaGuest** : trop complexe et fragile.
- ❌ **Créer des réservations de chambre dans TerangaGuest sans Opera** : les réservations de chambre doivent venir d'Opera (source de vérité).
- ❌ **Ignorer les webhooks / sync** : sans synchronisation, TerangaGuest n'aura jamais les bonnes réservations.

---

## 11. Plan d'implémentation

### Phase 1 : Infrastructure (1-2 jours)
- [ ] Créer les migrations pour `opera_reservations`, `opera_guests`, `opera_rooms`.
- [ ] Ajouter `opera_*_id` dans `guests`, `reservations`, `rooms`.
- [ ] Config par `Enterprise` : `opera_api_url`, `opera_client_id`, `opera_client_secret`, `opera_enabled`.

### Phase 2 : Service de sync (2-3 jours)
- [ ] `OperaSyncService` avec méthodes `syncReservations()`, `syncGuests()`, `syncRooms()`.
- [ ] Mapping Opera → TerangaGuest (ID, statuts, formats de date).
- [ ] Génération automatique du code client pour les guests synchronisés.

### Phase 3 : Webhooks (1 jour)
- [ ] Contrôleur `OperaWebhookController` pour recevoir les événements Opera.
- [ ] Route `/api/opera/webhook` (sécurisée par signature ou token).
- [ ] Gérer `NEW_RESERVATION`, `UPDATE_RESERVATION`, `CHECK_IN`, `CHECK_OUT`, `CANCEL`.

### Phase 4 : Cron / Commande artisan (1 jour)
- [ ] Commande `php artisan opera:sync --enterprise=X`.
- [ ] Cron Laravel : sync toutes les 15-30 minutes.
- [ ] Logs : enregistrer chaque sync (succès, erreurs, nombre de réservations synchronisées).

### Phase 5 : Dashboard (1 jour)
- [ ] Section "Synchronisation Opera" dans le dashboard.
- [ ] Afficher les dernières syncs, statut (OK / erreur), nombre de réservations importées.
- [ ] Bouton "Forcer la synchronisation maintenant".
- [ ] Config par hôtel : activer/désactiver Opera, saisir les credentials.

### Phase 6 : Export charges vers Opera (optionnel, 1-2 jours)
- [ ] À la fin du séjour (check-out), générer un CSV ou appel API Opera Folios avec toutes les charges TerangaGuest (room service, spa, etc.).
- [ ] Envoyer vers Opera pour facturation centralisée.

### Phase 7 : Tests & Validation (2 jours)
- [ ] Tester avec un environnement de test Opera (UAT).
- [ ] Valider les scénarios : nouvelle réservation, check-in, check-out, modification, annulation.
- [ ] Vérifier que les codes clients fonctionnent et que les séjours sont bien actifs/inactifs selon Opera.

---

## 12. Code exemple : OperaSyncService (structure)

```php
<?php

namespace App\Services;

use App\Models\Enterprise;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OperaSyncService
{
    /**
     * Synchronise les réservations d'Opera vers TerangaGuest pour une entreprise.
     */
    public function syncReservations(Enterprise $enterprise): int
    {
        if (!$enterprise->opera_enabled) {
            return 0;
        }

        $token = $this->getOperaToken($enterprise);
        $url = $enterprise->opera_api_url . '/reservations';
        
        // Récupérer les réservations depuis la dernière sync (lastSyncedAt)
        $response = Http::withToken($token)->get($url, [
            'modifiedSince' => $enterprise->opera_last_sync_at?->toIso8601String(),
            'status' => 'confirmed,checked_in,checked_out',
        ]);

        if (!$response->successful()) {
            Log::error("Opera sync failed for enterprise {$enterprise->id}");
            return 0;
        }

        $reservations = $response->json('data.reservations', []);
        $synced = 0;

        foreach ($reservations as $operaReservation) {
            try {
                $this->importReservation($enterprise, $operaReservation);
                $synced++;
            } catch (\Exception $e) {
                Log::error("Failed to import Opera reservation: " . $e->getMessage());
            }
        }

        $enterprise->update(['opera_last_sync_at' => now()]);
        return $synced;
    }

    /**
     * Importe une réservation Opera dans TerangaGuest.
     */
    private function importReservation(Enterprise $enterprise, array $operaData): void
    {
        $operaReservationId = $operaData['reservationId'];
        $operaGuestData = $operaData['guest'];
        $operaRoomData = $operaData['room'];

        // 1. Créer ou récupérer le Guest
        $guest = $this->linkOrCreateGuest($enterprise, $operaGuestData);

        // 2. Créer ou récupérer la Room
        $room = $this->linkOrCreateRoom($enterprise, $operaRoomData);

        // 3. Créer ou mettre à jour la Reservation
        $reservation = Reservation::updateOrCreate(
            ['opera_reservation_id' => $operaReservationId],
            [
                'enterprise_id' => $enterprise->id,
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'reservation_number' => $operaData['confirmationNumber'] ?? $operaReservationId,
                'check_in' => $operaData['arrivalDate'],
                'check_out' => $operaData['departureDate'],
                'guests_count' => $operaData['numberOfGuests'] ?? 1,
                'status' => $this->mapOperaStatus($operaData['status']),
                'total_price' => $operaData['totalAmount'] ?? 0,
            ]
        );

        Log::info("Reservation {$operaReservationId} synced for guest {$guest->name}");
    }

    /**
     * Crée ou lie un Guest depuis les données Opera.
     */
    private function linkOrCreateGuest(Enterprise $enterprise, array $operaGuestData): Guest
    {
        $operaGuestId = $operaGuestData['guestId'];
        
        // Chercher guest existant (par opera_guest_id ou email)
        $guest = Guest::where('enterprise_id', $enterprise->id)
            ->where(function($q) use ($operaGuestId, $operaGuestData) {
                $q->where('opera_guest_id', $operaGuestId)
                  ->orWhere('email', $operaGuestData['email'] ?? '');
            })
            ->first();

        if ($guest) {
            $guest->update([
                'opera_guest_id' => $operaGuestId,
                'name' => $operaGuestData['name'],
                'email' => $operaGuestData['email'] ?? $guest->email,
                'phone' => $operaGuestData['phone'] ?? $guest->phone,
            ]);
            return $guest;
        }

        // Créer un nouveau guest
        $guest = Guest::create([
            'enterprise_id' => $enterprise->id,
            'opera_guest_id' => $operaGuestId,
            'name' => $operaGuestData['name'],
            'email' => $operaGuestData['email'] ?? null,
            'phone' => $operaGuestData['phone'] ?? null,
            'nationality' => $operaGuestData['nationality'] ?? null,
            'access_code' => Guest::generateAccessCode($enterprise->id),
        ]);

        return $guest;
    }

    /**
     * Crée ou lie une Room depuis les données Opera.
     */
    private function linkOrCreateRoom(Enterprise $enterprise, array $operaRoomData): Room
    {
        $operaRoomId = $operaRoomData['roomId'];
        $roomNumber = $operaRoomData['roomNumber'];

        $room = Room::where('enterprise_id', $enterprise->id)
            ->where(function($q) use ($operaRoomId, $roomNumber) {
                $q->where('opera_room_id', $operaRoomId)
                  ->orWhere('room_number', $roomNumber);
            })
            ->first();

        if ($room) {
            $room->update([
                'opera_room_id' => $operaRoomId,
                'status' => $this->mapOperaRoomStatus($operaRoomData['status']),
            ]);
            return $room;
        }

        return Room::create([
            'enterprise_id' => $enterprise->id,
            'opera_room_id' => $operaRoomId,
            'room_number' => $roomNumber,
            'type' => $operaRoomData['roomType'] ?? 'Standard',
            'status' => $this->mapOperaRoomStatus($operaRoomData['status']),
            'floor' => $operaRoomData['floor'] ?? null,
            'capacity' => $operaRoomData['maxOccupancy'] ?? 2,
            'price_per_night' => $operaRoomData['rateAmount'] ?? 0,
        ]);
    }

    /**
     * Mapping des statuts Opera vers TerangaGuest.
     */
    private function mapOperaStatus(string $operaStatus): string
    {
        return match(strtoupper($operaStatus)) {
            'RESERVED', 'CONFIRMED' => 'confirmed',
            'INHOUSE', 'CHECKED_IN' => 'checked_in',
            'CHECKED_OUT' => 'checked_out',
            'CANCELLED' => 'cancelled',
            'NO_SHOW' => 'no_show',
            default => 'pending',
        };
    }

    private function mapOperaRoomStatus(string $operaStatus): string
    {
        return match(strtoupper($operaStatus)) {
            'VACANT', 'CLEAN' => 'available',
            'OCCUPIED' => 'occupied',
            'DIRTY' => 'dirty',
            'OUT_OF_ORDER', 'OUT_OF_SERVICE' => 'maintenance',
            default => 'available',
        };
    }

    /**
     * Récupère un token OAuth Opera (à cacher dans une méthode réutilisable).
     */
    private function getOperaToken(Enterprise $enterprise): string
    {
        // Cache le token (valable ~1h généralement)
        return cache()->remember("opera_token_{$enterprise->id}", 3600, function() use ($enterprise) {
            $response = Http::post($enterprise->opera_oauth_url, [
                'grant_type' => 'client_credentials',
                'client_id' => $enterprise->opera_client_id,
                'client_secret' => $enterprise->opera_client_secret,
            ]);
            return $response->json('access_token');
        });
    }
}
```

---

## 13. Gestion des codes clients

### Génération automatique
- Chaque **guest** synchronisé depuis Opera reçoit un **code client** (access_code) généré par TerangaGuest (6 chiffres).
- Ce code est **stocké uniquement dans TerangaGuest** (table `guests`).
- L'hôtel peut **imprimer ce code** (ou l'envoyer par email/SMS) au client lors du check-in.

### Régénération
- Si le code est compromis, le gérant peut le régénérer depuis le dashboard TerangaGuest (comme aujourd'hui).
- Avec la vérification `validated_at` (mise en place plus tôt), toute session basée sur l'ancien code sera invalidée.

---

## 14. Points d'attention

### Mapping des données
- Les **noms des champs** Opera peuvent différer (ex. `arrivalDate` vs `check_in`, `guestName` vs `name`). Il faut adapter le mapping dans `OperaSyncService`.
- Les **formats de date** Opera (souvent ISO8601) doivent être convertis en timestamps MySQL.

### Gestion des conflits
- Si un guest existe dans TerangaGuest (créé manuellement) et dans Opera, le lier par email/téléphone et ajouter `opera_guest_id`.
- Si une chambre existe dans TerangaGuest mais pas dans Opera (ou inversement), décider de la stratégie (créer, ignorer, ou alerter).

### Sécurité des webhooks
- Valider la signature ou utiliser un token secret pour authentifier les webhooks Opera (éviter qu'un tiers envoie de fausses données).

### Performance
- Ne pas synchroniser **toutes** les réservations à chaque fois : utiliser `modifiedSince` (date de dernière sync) pour récupérer seulement les changements.
- Pour la sync initiale (première fois), limiter à 1 an de réservations (ou selon les besoins).

---

## 15. Checklist de déploiement

- [ ] Obtenir les credentials OHIP (client_id, client_secret, URL API) depuis Orange / l'hôtel.
- [ ] Créer les tables `opera_*` et ajouter les colonnes `opera_*_id`.
- [ ] Implémenter `OperaSyncService` avec mapping Opera → TerangaGuest.
- [ ] Créer la commande artisan `opera:sync` et la tester en local.
- [ ] Configurer le webhook Opera → URL de votre serveur (`https://teranguest.com/api/opera/webhook`).
- [ ] Tester en environnement UAT d'Opera (sandbox).
- [ ] Déployer en production et activer le cron.
- [ ] Former le personnel de l'hôtel : "Le code client est généré par TerangaGuest, imprimez-le depuis le dashboard ou envoyez-le au client."

---

## 16. Résumé en une phrase

**Opera gère les réservations de chambre (source de vérité), TerangaGuest synchronise ces réservations via API/webhooks, génère un code client par guest, et gère tous les services additionnels (room service, spa, resto, excursions) sans toucher à Opera.**

---

## 17. Diagramme de flux

```
┌─────────────────┐
│  Opera PMS      │ (Réservations, clients, chambres)
│  (Oracle)       │
└────────┬────────┘
         │
         │ API REST / Webhooks
         │ (OHIP)
         ↓
┌─────────────────────────────────────────────────────┐
│  TerangaGuest Backend (Laravel)                     │
│                                                      │
│  1. OperaSyncService                                │
│     - Récupère depuis Opera                         │
│     - Enregistre dans opera_reservations, etc.      │
│     - Lie avec guests, rooms, reservations          │
│                                                      │
│  2. Tables miroirs : opera_* (lecture seule)        │
│  3. Tables actuelles : guests, reservations, rooms  │
│     (+ opera_*_id pour lier)                        │
│                                                      │
│  4. Services TerangaGuest (room service, spa, ...)  │
│     → Stockés uniquement dans TerangaGuest          │
└─────────────────┬───────────────────────────────────┘
                  │
                  │ API REST
                  ↓
          ┌───────────────┐
          │  App / Tablette│
          │  (Flutter)     │
          │                │
          │  - Code client │
          │  - Commandes   │
          │  - Réservations│
          └────────────────┘
```

---

## 18. Questions à poser à Orange / l'hôtel

1. **Version d'Opera** : Opera Cloud ou Opera on-premise (v5.x) ?
2. **Accès OHIP** : avez-vous déjà un compte OHIP ou faut-il le créer ?
3. **Credentials** : pouvez-vous fournir `client_id`, `client_secret`, URL de l'API OHIP ?
4. **Webhooks** : pouvez-vous configurer les webhooks Opera pour pointer vers notre serveur ?
5. **Environnement de test** : y a-t-il un environnement UAT/sandbox pour tester l'intégration avant la production ?
6. **Données existantes** : combien de réservations actives/futures à synchroniser lors de l'import initial ?
7. **Facturation** : voulez-vous qu'on exporte les charges TerangaGuest (room service, spa) vers Opera Folios, ou gérez-vous ça manuellement ?

---

## 19. Ressources

- [Documentation OHIP officielle](https://docs.oracle.com/en/industries/hospitality/integration-platform/)
- [Quick Start OHIP](https://docs.oracle.com/en/industries/hospitality/integration-platform/ohipu/t_quick_start_for_hoteliers.htm)
- [Opera Cloud Reservations API](https://docs.oracle.com/en/industries/hospitality/opera-cloud/)

---

**Conclusion** : cette architecture vous permet d'intégrer Opera **sans modifier votre projet actuel**, en ajoutant seulement des tables miroirs et un service de synchronisation. Opera reste la source de vérité pour les réservations de chambre, TerangaGuest gère les services additionnels, et tout fonctionne ensemble via des codes clients générés automatiquement.
