# Notifications client – Ce que j’ai compris (à valider)

## 1. Objectif

- Utiliser le fichier Firebase **`teranguest-74262-844fbd9b5264.json`** pour les notifications push.
- Envoyer des notifications **uniquement au client concerné** pour :
  - **Réservations** (spa, restaurant, excursions, blanchisserie, palace) : confirmation, etc.
  - **Commandes** (room service) : commande reçue, commande validée, changement de statut (en préparation, prête, livrée, annulée, etc.).

- **Ne jamais** envoyer à la mauvaise personne : un client ne doit recevoir que les notifications qui le concernent (sa chambre / son séjour).

---

## 2. Deux cas dans l’app

- **Tablette en chambre** : la tablette est reliée à une chambre ; le client s’identifie avec un **code client** (pas de compte “User” pour le client, juste le code). La tablette peut être connectée à un **compte User** (compte “tablette” avec `room_number` = numéro de chambre).
- **App mobile (client connecté)** : le client se connecte avec un compte User (email/mot de passe) et a un `room_number` + séjour actif.

Dans les deux cas, côté backend on a toujours pour une réservation/commande :
- **guest_id** (le client invité)
- **room_id** (la chambre)
- éventuellement **user_id** (si passée par un User).

Donc on sait **toujours** “à qui” et “à quelle chambre” la résa/commande est liée.

---

## 3. Association chambre ↔ “qui reçoit la notification”

Aujourd’hui :
- Les **commandes / réservations** ont **room_id** et **guest_id**.
- Le **FCM token** est stocké sur le modèle **User** (table `users`, colonnes `fcm_token`, `fcm_token_updated_at`).
- Un **User** peut avoir un **room_number** (chambre associée à ce compte = souvent la tablette de cette chambre).

Donc pour “envoyer la notification au client concerné” on peut faire :

- **Règle** : la notification pour une commande / réservation est envoyée au **User dont la chambre correspond** à la commande/réservation.
- Concrètement : à partir de la **room_id** de la commande/réservation, on récupère la **Room** (et son `room_number`), puis on trouve le **User** qui a ce **room_number** et le même **enterprise_id**. C’est ce User qui reçoit la notification (tablette de la chambre ou compte client de la chambre).

Résultat :
- Une commande pour la chambre 101 → notification uniquement au User lié à la chambre 101 (tablette ou compte de la chambre).
- Pas d’envoi à tout le monde, ni à un User d’une autre chambre.

---

## 4. Ce qu’il faut avoir côté “web / app”

- **Association chambre ↔ User (pour les notifications)**  
  Pour chaque chambre qui peut recevoir des notifications (tablette ou app), il faut qu’il existe un **User** avec :
  - `room_number` = numéro de cette chambre
  - `enterprise_id` = même entreprise que la chambre  
  et que ce User enregistre un **FCM token** (quand la tablette ou l’app se connecte et envoie le token via l’API actuelle).

Donc dans l’application web (dashboard) :
- Soit on **crée / gère des comptes “tablette”** par chambre (un User par chambre avec `room_number` rempli), et la tablette envoie le FCM token pour ce User.
- Soit on a déjà une **liste Chambres** et on “associe” chaque chambre à un User (par exemple en renseignant le `room_number` sur le User).  
L’idée est : **pour chaque chambre, on sait quel User = destinataire des notifications** (via `room_number` + `enterprise_id`).

Les **guests** sont déjà reliés aux réservations (donc aux chambres) : pas besoin de stocker un FCM token sur le Guest ; on cible par **chambre** → User de cette chambre.

---

## 5. Quand envoyer quoi (résumé)

| Événement | Qui est concerné | À qui envoyer la notification |
|-----------|------------------|------------------------------|
| Commande room service créée | Client de la chambre (guest_id + room_id) | User dont room_number = chambre de la commande (même entreprise) |
| Commande validée / statut changé | Même client/chambre | Même User (chambre concernée) |
| Réservation spa/resto/excursion/blanchisserie/palace confirmée | Client (guest_id + room_id) | User dont room_number = chambre de la réservation |

Donc : **toujours** à partir de la **room_id** de la commande/réservation → trouver le **User** de cette chambre → envoyer à son **fcm_token**. Si aucun User avec FCM pour cette chambre, ne pas envoyer (ou logger).

---

## 6. Ce qu’il ne faut pas faire

- Ne pas envoyer les notifications “client” (résa, commande, statut) à **tous** les users de l’entreprise.
- Ne pas envoyer au **User qui a fait l’action** (ex. réceptionniste) mais bien au **User de la chambre concernée** (tablette / client).
- Ne pas envoyer à un client une notification qui concerne une **autre chambre** : d’où la règle stricte **room_id → User avec ce room_number**.

---

## 7. Récap technique à faire après validation

1. **Config Firebase**  
   Utiliser `teranguest-74262-844fbd9b5264.json` (par ex. dans `.env` : `FIREBASE_CREDENTIALS=teranguest-74262-844fbd9b5264.json` ou chemin complet).

2. **Service de notification**  
   - Ajouter une méthode du type : “pour une **room_id** (et enterprise_id), récupérer le **User** qui a ce `room_number` et un `fcm_token` non nul”.
   - Pour **chaque** événement “client” (commande créée, commande validée, changement de statut, réservation confirmée) : utiliser la **room_id** de la commande/réservation, trouver ce User, envoyer la notification à son FCM token (et pas au `$request->user()` sauf si c’est justement le User de la chambre).

3. **Dashboard / app web**  
   S’assurer que chaque chambre qui doit recevoir des notifs est bien “associée” à un User (via `room_number` + FCM token enregistré par la tablette/app).

4. **Côté tablette / app**  
   La tablette (ou l’app) de la chambre continue d’enregistrer le FCM token sur le **User** qui a le `room_number` de cette chambre (endpoint actuel ou équivalent).

---

Si ce résumé correspond bien à ce que vous voulez (notifications uniquement pour le client concerné, via chambre → User avec FCM), je valide et on passe à l’implémentation concrète (code backend + éventuellement rappels dans le dashboard). Sinon, indiquez ce qui doit être ajusté (par ex. “on veut aussi notifier le guest par email”, “on veut lier Tablet à un User”, etc.).
