# Isolation des données par entreprise

Chaque entreprise (hôtel) ne doit voir et manipuler **que ses propres données**. Ce document décrit comment c’est garanti dans le projet.

## 1. Scope global (dashboard web)

- Les modèles concernés utilisent le trait **`EnterpriseScopeTrait`** (voir `app/Models/Scopes/EnterpriseScopeTrait.php`).
- Au chargement, un **scope global** est ajouté : toutes les requêtes sur ces modèles sont filtrées par  
  `enterprise_id = auth()->user()->enterprise_id` (sauf pour les **super_admin** qui n’ont pas d’`enterprise_id`).

**Modèles avec `EnterpriseScopeTrait`** (donc automatiquement filtrés par entreprise en contexte web authentifié) :

- Room, Reservation, Guest, Order  
- MenuCategory, MenuItem  
- LaundryService, LaundryRequest  
- SpaService, SpaReservation  
- Restaurant, RestaurantReservation  
- Excursion, ExcursionBooking  
- PalaceService, PalaceRequest  
- Vehicle, Tablet  
- AmenityCategory, LeisureCategory  

- **Route model binding** (ex. `Room $room`, `Reservation $reservation`) applique aussi ce scope : un utilisateur ne peut accéder qu’aux ressources de son entreprise.

## 2. API authentifiée (Sanctum)

- L’utilisateur est identifié par le token (`auth()->user()`).
- `auth()->user()->enterprise_id` est utilisé partout où c’est nécessaire.
- Les contrôleurs API qui chargent des données (réservations, commandes, spa, excursions, etc.) s’appuient soit sur le scope (modèles avec `EnterpriseScopeTrait` en contexte web), soit sur des `where('enterprise_id', $user->enterprise_id)` explicites dans l’API.
- **Profil / login** : les `hotel_infos` renvoyés sont ceux de l’entreprise de l’utilisateur ; si l’utilisateur a un `room_id` (compte tablette), le Wi‑Fi chambre est fusionné côté backend sans exposer d’autre entreprise.

## 3. API tablette (sans auth)

Les endpoints sous `/api/tablet/*` ne sont pas authentifiés (pas de token). L’isolation est assurée par la **logique métier** :

- **validate-code** : on part de la **chambre** (`room_id` ou `room_number`). Le `guest` est cherché avec `enterprise_id = $room->enterprise_id` et le code. La réservation est cherchée pour ce guest + cette room. Donc une tablette ne peut valider qu’un code pour une chambre de **son** hôtel (la chambre détermine l’entreprise).
- **validate-session** : on vérifie que `guest`, `room` et `reservation` correspondent et que `room->enterprise_id === guest->enterprise_id`. Pas d’accès aux données d’une autre entreprise.
- **hotel-infos** : **POST** avec `guest_id`, `room_id`, `reservation_id` (et optionnellement `validated_at`). Le serveur **valide la session** (même logique que validate-session) avant de renvoyer le livret d’accueil. On ne renvoie les infos que pour la chambre du séjour validé → **aucun accès aux infos d’une autre entreprise** (pas de fuite en envoyant un `room_id` arbitraire).
- **checkout** : même principe : on vérifie guest + room + réservation active et on crée la commande avec `enterprise_id = $room->enterprise_id`.

## 4. Contrôleurs dashboard

- **RoomController, ReservationController, GuestController, etc.** : ils utilisent les modèles scopés et/ou `auth()->user()->enterprise_id` (ex. `$validated['enterprise_id'] = auth()->user()->enterprise_id`). Les listes et formulaires ne montrent que les données de l’entreprise connectée.
- **TabletController, TabletAccessController** : filtrage explicite par `enterprise_id` sur Room, User, Tablet.

## 5. Chat & conversations

- **HotelConversation** a un `enterprise_id`. Les requêtes (dashboard, API, AppServiceProvider) filtrent toujours par `enterprise_id` (ex. `where('enterprise_id', $enterpriseId)` ou `$user->enterprise_id`). Pas de scope global sur ce modèle, mais l’isolation est assurée par ces filtres explicites.

## 6. Récapitulatif

| Contexte              | Mécanisme d’isolation |
|-----------------------|------------------------|
| Dashboard web         | Scope global `EnterpriseScopeTrait` + `auth()->user()->enterprise_id` |
| API Sanctum            | `$request->user()->enterprise_id` et modèles scopés / filtres explicites |
| API tablette (sans auth) | Validation stricte de la session (guest + room + réservation) ; pas d’accès par `room_id` seul pour hotel-infos |

En cas de nouvel endpoint ou nouveau modèle lié à une entreprise, vérifier soit l’usage du `EnterpriseScopeTrait`, soit un filtre explicite sur `enterprise_id` cohérent avec l’utilisateur ou la session.
