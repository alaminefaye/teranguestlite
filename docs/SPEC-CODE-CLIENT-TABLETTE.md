# Spécification : Code client & session tablette

## 1. Contexte

- **Chaque hôtel** (établissement / enterprise) gère **ses clients** (invités) qu’il peut créer.
- La **tablette** (app Teranga Guest) est utilisée en chambre ou dans l’hôtel.
- Si **aucun client n’est connecté** sur la tablette → l’app demande d’**entrer un code**.
- Ce **code** identifie le client et permet d’associer toutes les actions (commandes, réservations) à ce **client** et à la **chambre** qu’il occupe.

## 2. Règles métier

### 2.1 Accès à la tablette

- **Pas de session** → écran « Entrez votre code ».
- Le client saisit son **code** (réutilisable pendant le séjour).
- Le code **ne donne accès que si** le client a une **réservation de chambre valide**.

### 2.2 Validité de la réservation

- **Valide** = la date et l’heure actuelles sont dans la plage :
  - **check_in (date + heure)** ≤ **maintenant** ≤ **check_out (date + heure)**.
- Si **avant** le check-in ou **après** le check-out → **pas d’accès** (message explicite).
- Seule une **réservation de chambre** (status confirmée ou check-in effectué) est prise en compte ; pas d’autre type de réservation.

### 2.3 Code réutilisable

- Le **même code** peut être utilisé plusieurs fois (ex. chaque fois que le client reprend la tablette).
- Il reste valide **uniquement** tant qu’il existe une réservation de chambre active (plage check-in / check-out valide à l’instant T).

### 2.4 Liaison des actions

- Toutes les actions effectuées depuis la tablette (room service, spa, restaurant, blanchisserie, palace, excursions) sont enregistrées pour :
  - le **client** (user_id / guest) identifié par le code ;
  - la **chambre** de sa réservation active (room_id).

## 3. Modèle de données (backend)

### 3.1 Existant à adapter

- **User** (rôle `guest`) = client de l’hôtel.
- **Reservation** = réservation de chambre (user_id, room_id, check_in, check_out, status).
- **Order** et autres entités ont déjà `user_id` et souvent `room_id` ou peuvent être dérivés de la session.

### 3.2 Évolutions

1. **User (guests)**  
   - Ajouter un champ **`tablet_code`** (string, nullable, unique par enterprise) pour les clients.  
   - Ce code est saisi sur la tablette pour ouvrir une session.

2. **Reservation**  
   - Utiliser **date + heure** pour la validité de la plage.  
   - Soit :
     - `check_in` / `check_out` en **datetime** (migration),  
     - soit conserver `date` et ajouter `check_in_time` / `check_out_time` (ex. "14:00", "11:00").  
   - La validation côté serveur :  
     `now() >= check_in_datetime AND now() <= check_out_datetime`  
     et `status IN ('confirmed', 'checked_in')`.

## 4. API (backend)

### 4.1 Connexion tablette par code

- **POST** `/api/tablet/session` (sans auth Bearer).
- **Body** : `{ "code": "1234" }` (ou `tablet_code`).
- **Comportement** :
  - Recherche d’un **User** (role guest) avec `tablet_code` = code, pour l’enterprise concernée (à déterminer : header `X-Enterprise-Id` ou domaine, ou premier enterprise si un seul).
  - Recherche d’une **Reservation** (chambre) pour ce user où :
    - `check_in` (date+heure) ≤ now ≤ `check_out` (date+heure),
    - `status` ∈ { confirmed, checked_in }.
  - Si trouvé : retourner un **token** (Sanctum ou token dédié) + infos **guest** (id, name, room_number, room_id, reservation_id, check_in, check_out) pour l’app.
  - Si non trouvé : **401** ou **422** avec message clair :
    - « Code invalide »
    - « Aucune réservation active pour cette période »
    - « Réservation pas encore active » (avant check-in)
    - « Réservation terminée » (après check-out).

### 4.2 Utilisation du token

- Les appels suivants (room service, spa, restaurant, etc.) utilisent le **même token**.
- Le backend associe déjà les créations (commandes, réservations) au `user_id` et peut imposer `room_id` depuis la session ou le user (room de la réservation active).

## 5. Application (Flutter)

### 5.1 Flux de démarrage

1. **Splash** → vérification de la session (token existant + optionnellement appel pour valider que la réservation est encore valide).
2. **Si pas de session (ou session expirée / réservation plus valide)** → afficher l’écran **« Entrez votre code »** (pas le login email/mot de passe).
3. **Si code valide** → enregistrer le token et les infos session (guest, room, dates) → aller au **Dashboard**.
4. **Si code invalide** → afficher le message d’erreur et rester sur l’écran code.

### 5.2 Écran « Code client »

- Champ de saisie du code (chiffres ou alphanumérique selon choix).
- Bouton « Valider » ou validation automatique après N caractères.
- Messages d’erreur clairs (code invalide, réservation pas active, expirée, etc.).

### 5.3 Session tablette

- Stocker le token (comme pour un login classique) et, si utile, **guest_id**, **room_id**, **reservation_id**, **check_in**, **check_out** pour affichage ou vérification côté app.
- Toutes les requêtes API utilisent ce token ; le backend lie automatiquement les actions au client et à la chambre.

### 5.4 Déconnexion / changement de client

- Option « Déconnexion » ou « Changer de client » qui supprime le token et redirige vers l’écran code.

## 6. Résumé des tâches

| # | Tâche |
|---|--------|
| 1 | Migration : ajouter `tablet_code` aux users (unique par enterprise) |
| 2 | Migration : check_in / check_out en datetime (ou ajout time) sur reservations |
| 3 | Backend : API POST `/tablet/session` (validation code + réservation valide, retour token + infos) |
| 4 | Backend : s’assurer que les APIs métier (orders, spa, etc.) utilisent user_id + room de la session |
| 5 | App : écran « Entrez votre code » |
| 6 | App : adapter Splash / Auth pour mode tablette (code au lieu de login si pas de session) |
| 7 | App : stocker session tablette et utiliser le token pour les appels |
| 8 | App : déconnexion / changer de client → retour écran code |
| 9 | Dashboard web : permettre de définir le `tablet_code` pour chaque client (user guest) |

## 7. Configuration tablette (app)

- **ApiConfig.tabletEnterpriseId** : à définir selon l’établissement (ex. `1`). Chaque tablette peut avoir une valeur différente (build flavor ou config par établissement).
- Connexion email reste disponible depuis l’écran code (« Connexion avec email »).

## 8. Définir le code client (dashboard)

- Les clients (users avec rôle `guest`) doivent avoir un **tablet_code** renseigné pour pouvoir se connecter sur la tablette.
- À faire côté dashboard : dans la fiche utilisateur (ou liste des clients), champ **Code tablette** (éditable), enregistré dans `users.tablet_code`. Le code est unique par établissement.

---

*Document créé pour la fonctionnalité « Code client & session tablette » – Teranga Guest.*
