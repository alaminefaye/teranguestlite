# Spécification : Client (invité), code tablette et réservations par entreprise

## 1. Résumé de la compréhension

### Contexte
- **Chaque hôtel (entreprise)** a **ses propres clients** : un client n’appartient qu’à une seule entreprise. Un autre hôtel ne doit jamais voir les clients d’un autre.
- Une **tablette en chambre** est utilisée par le client pour : menu (room service), réservations spa/resto, achats, etc. Toutes ces actions doivent être **rattachées à ce client et à la chambre qu’il occupe**.
- La tablette n’a **pas de “client connecté”** au départ : si l’utilisateur ouvre le menu (ou l’app), il doit **saisir un code** pour s’identifier.
- Ce **code** :
  - est **réutilisable** (le même code pour le même client à chaque séjour) ;
  - **ne donne accès que si** le client a **une réservation de chambre valide** (pas une autre chose) ;
  - la **validité** dépend de la **date et de l’heure** de check-in et de check-out : l’accès est autorisé **uniquement** quand l’heure actuelle est **entre** l’heure de check-in et l’heure de check-out (pas seulement la date).
- Tant que la période (check-in → check-out) est valide, le client est considéré **actif** et peut utiliser la tablette ; en dehors de cette période, le code ne donne pas accès.

**Clarification tablette :** La tablette est connectée et les menus sont affichés sans code. Le code est demandé **uniquement au moment de valider une commande** (ou une réservation) : ainsi tout est rattaché au client et à la chambre. Après une première validation réussie, une session est conservée et les prochaines validations n’ont pas besoin de redemander le code. La **régénération du code** client se fait **uniquement par l’admin ou le gérant** dans le dashboard.

### Flux global
1. **Back-office (dashboard)**  
   - L’entreprise crée et gère ses **clients** (invités).  
   - L’entreprise crée des **réservations de chambre** : chambre, client, **date + heure** de check-in, **date + heure** de check-out.  
   - Chaque client dispose d’un **code** (ex. code à 6 chiffres) qu’il utilisera sur la tablette.

2. **Tablette en chambre**  
   - Au premier usage (ou si pas de session) : afficher **“Entrez votre code”**.  
   - Le client saisit son code.  
   - Le serveur vérifie :  
     - le code correspond à un **client** de l’entreprise ;  
     - ce client a **au moins une réservation de chambre** dont **maintenant** est **entre** check-in et check-out (date + heure) ;  
     - optionnel : si la tablette envoie un **numéro de chambre**, on peut vérifier que la réservation concerne bien cette chambre.  
   - Si tout est valide : on attache la **session tablette** à ce **client** et à la **chambre** (et à la réservation). Toutes les actions (commandes, réservations spa/resto, etc.) sont enregistrées pour ce client et cette chambre.  
   - Si invalide : message d’erreur (code incorrect ou pas de séjour valide).

3. **Isolation par entreprise**  
   - Les clients sont **filtrés par `enterprise_id`** : chaque entreprise ne voit et ne gère que ses clients.  
   - Les réservations sont déjà liées à une entreprise (via chambre / établissement).  
   - La validation du code se fait **dans le périmètre** de l’entreprise (et éventuellement de la chambre si on utilise le `room_id` de la tablette).

---

## 2. Ce qui existe déjà (à réutiliser / adapter)

- **Réservations** : table `reservations` avec `enterprise_id`, `user_id` (actuellement “guest”), `room_id`, `check_in` / `check_out` en **date** (sans heure), `status`, etc.
- **Chambres** : `rooms` avec `enterprise_id`, `room_number`, etc.
- **Entreprise** : `enterprises` ; tout est déjà scopé par `enterprise_id` côté dashboard.

---

## 3. Ce qu’il faut faire (plan d’exécution)

### 3.1 Modèle “Client” (invité) – par entreprise

- **Créer** le modèle **Guest** (ou **Client**) :
  - `id`, `enterprise_id`, `name`, `email` (nullable), `phone` (nullable), **`access_code`** (code pour la tablette, ex. 6 chiffres), `notes` (optionnel), `timestamps`.
  - Contraintes : `enterprise_id` obligatoire ; `access_code` **unique par entreprise** (deux entreprises peuvent avoir le même code pour deux clients différents).
- **Migration** : table `guests` avec les champs ci-dessus, index sur `(enterprise_id, access_code)` pour la validation rapide du code.
- **Scope entreprise** : toutes les requêtes (liste, création, édition) filtrent par `enterprise_id` (middleware / scope existant ou équivalent).

### 3.2 Réservations : lien Client + date/heure

- **Lier la réservation au client (invité)** :  
  - Ajouter **`guest_id`** (nullable dans un premier temps pour ne pas casser l’existant) à la table `reservations`, clé étrangère vers `guests`.
- **Check-in / check-out avec heure** :  
  - Remplacer les champs **`check_in`** et **`check_out`** de type **date** par **datetime** (ou ajouter `check_in_time` et `check_out_time` et utiliser la combinaison date + time).  
  - Objectif : la condition “séjour valide” = `check_in <= now() <= check_out` (date + heure).
- **Modèle Reservation** :  
  - `belongsTo(Guest::class)` si `guest_id` présent ;  
  - cast `check_in` et `check_out` en `datetime` (si migration vers datetime).
- **Dashboard** :  
  - Lors de la création/édition d’une réservation : choix du **client** (liste des guests de l’entreprise) et saisie des **date + heure** de check-in et check-out.

### 3.3 Génération / gestion du code client

- **Règles** :  
  - Un code **réutilisable** par client (pas un code par réservation).  
  - Génération automatique (ex. 6 chiffres) à la création du client, avec unicité dans l’entreprise.  
  - **Régénération du code** : uniquement par **l’admin ou le gérant** de l’hôtel depuis le dashboard (bouton “Régénérer le code” sur la fiche client). Le client ne peut pas régénérer lui‑même son code.
- **Sécurité** :  
  - En API, ne renvoyer que “code valide / invalide” et les infos de session (client, chambre, réservation) si valide.

### 3.4 API “Validation du code” (tablette)

- **Endpoint** (ex. `POST /api/tablet/validate-code` ou `POST /api/guest-session`) :  
  - Body : `{ "code": "123456", "room_id": 42 }` (ou `room_number` si la tablette ne connaît que le numéro).  
  - `room_id` optionnel : si envoyé, on vérifie que la réservation valide est bien pour cette chambre.
- **Logique** :  
  1. Trouver un **Guest** avec `access_code = code` (et, si on connaît l’entreprise de la tablette, filtrer par `enterprise_id` ; sinon, passer par la chambre pour avoir l’entreprise).  
  2. Trouver une **Réservation** : `guest_id = guest.id`, `room_id` cohérent si fourni, `status` dans `['confirmed', 'checked_in']`, et **`check_in <= now() <= check_out`** (date + heure).  
  3. Si trouvé : retourner un **token de session** (ou simplement les infos : `guest_id`, `room_id`, `reservation_id`, nom du client, numéro de chambre) pour que la tablette les stocke et les envoie sur les prochaines requêtes.  
  4. Sinon : 401 ou 403 avec message “Code invalide” ou “Aucun séjour valide pour cette période”.
- **Authentification** :  
  - Soit la tablette a un token “device” par chambre (pour identifier l’entreprise + room), soit on envoie `room_id` / `room_number` et on déduit l’entreprise depuis la chambre. À trancher selon l’existant.

### 3.5 Application tablette (Flutter)

- **Menus affichés sans code** :  
  - La tablette est connectée ; le client peut **parcourir les menus** (room service, spa, resto, etc.) **sans saisir de code**.
- **Demande du code à la validation** :  
  - Quand le client tente de **valider une commande** (ex. envoyer une commande room service) ou **confirmer une réservation** (spa, resto, etc.) :  
    - Si **session existante** (guest_id + room_id en local) → utiliser cette session pour enregistrer la commande / réservation, **sans afficher l’écran code**.  
    - Si **pas de session** → afficher l’écran **“Entrez votre code”**, puis appeler l’API de validation avec `code` et `room_id` (si disponible).  
    - Si validation OK : enregistrer la **session** en local (guest_id, room_id, reservation_id) et **valider immédiatement** la commande / réservation.  
    - Si validation KO : afficher un message clair (code incorrect ou séjour invalide) et ne pas valider.
- **Appels API (commandes, réservations)** :  
  - Toujours envoyer la **session** (guest_id + room_id ou token) pour que le backend rattache tout au bon client et à la bonne chambre.
- **Déconnexion** :  
  - Bouton “Quitter” (ou équivalent) qui efface la session locale ; à la prochaine validation, le code sera redemandé.

### 3.6 Dashboard : CRUD Clients + réservations

- **Clients (Guests)** :  
  - Liste des clients de l’entreprise (filtrée par `enterprise_id`).  
  - Création : nom, email, téléphone, génération ou saisie du code.  
  - Édition / suppression (avec règles métier : par ex. ne pas supprimer si réservations existantes, ou les garder en historique).  
- **Réservations** :  
  - Création / édition : choix du **client** (guest), de la **chambre**, **date + heure** de check-in et check-out, statut, etc.  
  - Liste des réservations avec affichage du client et de la période (date + heure).

### 3.7 Rattachement des commandes / réservations au client et à la chambre

- **Room service (commandes)** :  
  - Les commandes existantes sont peut-être liées à `user_id` ou à une chambre. Il faudra **lier** à `guest_id` et `room_id` (ou garder une cohérence avec la session tablette : session = guest + room, et les commandes enregistrées avec ce contexte).
- **Spa, restaurant, excursions, etc.** :  
  - Idem : s’assurer que les réservations/achats faits depuis la tablette sont enregistrés avec le **guest_id** (et la chambre) de la session, pas avec un utilisateur “connecté” classique.

---

## 4. Résumé des livrables prévus

| # | Livrable | Détail |
|---|----------|--------|
| 1 | Modèle + migration **Guest** | Table `guests` (enterprise_id, name, email, phone, access_code, notes), scope entreprise, unicité du code par entreprise. |
| 2 | Migration **Reservations** | Ajout `guest_id` ; `check_in` / `check_out` en **datetime**. |
| 3 | Modèle **Reservation** | Relation `guest`, casts datetime pour check_in/check_out. |
| 4 | Dashboard **Clients** | CRUD clients (liste, créer, modifier, supprimer) avec gestion du code. |
| 5 | Dashboard **Réservations** | Création/édition avec choix du client (guest), date+heure check-in/check-out. |
| 6 | API **Validation code** | Endpoint tablette : code + optionnel room_id → validation date/heure → retour session (guest, room, reservation). |
| 7 | App **tablette** | Écran “Entrez votre code”, appel API, stockage session, envoi guest+room sur les requêtes métier. |
| 8 | Rattachement métier | Commandes / réservations spa-resto-etc. associées au guest et à la chambre de la session tablette. |

---

## 5. Points à valider avant d’exécuter

- **Tablette et entreprise** : la tablette connaît-elle son `room_id` (ou room_number) et/ou `enterprise_id` (config en dur ou renseignée à l’installation) ? Cela détermine si on envoie `room_id` à l’API de validation et si on restreint à “réservation pour cette chambre”.
- **User vs Guest** : aujourd’hui les réservations utilisent `user_id`. On garde **guest_id** en parallèle (réservations “tablette”) et on laisse **user_id** pour un autre flux (ex. résa par le staff pour un “User” invité) ou on migre tout vers **Guest** à terme ?
- **Format du code** : longueur (ex. 6 chiffres), caractères autorisés (chiffres uniquement, alphanumériques), génération auto ou saisie libre par le staff.

Une fois ces points validés et ce document approuvé, l’implémentation pourra suivre ce plan (MD comme référence).
