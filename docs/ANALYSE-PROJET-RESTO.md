# Analyse du projet Resto (même dossier que Teranga Guest)

Projet analysé : `/Users/Zhuanz/Desktop/projets/web/resto` (même niveau que `terangaguest`).

---

## 1. Structure générale

- **Un seul dépôt** : backend Laravel à la racine + application Flutter dans le sous-dossier **`resto-app/`** (même idée que `terangaguest` + `terangaguest_app`).
- **Backend** : Laravel 11, MySQL/PostgreSQL, Sanctum, Spatie (rôles/permissions).
- **Frontend web** : Blade + Tailwind/Bootstrap pour le back-office (dashboard, caisse, gestion des tables, menu, commandes, réservations).
- **Mobile** : Flutter (Provider), dans `resto-app/` — app client (menu, commande, paiement, réservations, notifications).

---

## 2. Modèles et domaine métier

| Modèle | Rôle |
|--------|------|
| **User** | Staff (admin, manager, caissier, serveur) ou client ; `fcm_token`, Spatie HasRoles. |
| **Table** | Tables du restaurant : `numero`, `type` (simple/VIP/jeux), `capacite`, `statut` (libre, occupée, réservée, en paiement), `qr_code`, `prix` / `prix_par_heure`. |
| **Category** | Catégories du menu (entrées, plats, desserts, etc.). |
| **Product** | Produits du menu (nom, description, prix, image, catégorie, disponibilité). |
| **Commande** | Commande liée à une **table** et un **user** (serveur/caissier ou client). Statuts : attente, préparation, servie, terminée, annulée. Pivot `commande_produit` (quantité, prix_unitaire, notes, statut, servi). |
| **Paiement** / **Facture** | Paiements (espèces, Wave, Orange Money) et factures. |
| **Reservation** | Réservations de tables (création, confirmation, annulation). |
| **Client** | Fiche client (fidélité, historique). |
| **UserNotification** | Notifications en base (fallback quand FCM échoue). |

Pas de multi-tenant : une seule base = un seul restaurant.

---

## 3. Intégration Web ↔ API ↔ Mobile

### 3.1 Routes Web (back-office)

- **Auth** : login / logout.
- **Dashboard** : accueil.
- **Tables** : CRUD, régénération QR.
- **Menu** : CRUD catégories et produits, toggle disponibilité.
- **Commandes** : CRUD, ajout/retrait produits, changement statut.
- **Caisse** : liste commandes à payer, traitement paiement, facture (affichage + téléchargement), historique.
- **Réservations** : CRUD, confirmer / annuler.
- **Rôles** et **Utilisateurs** (sous permissions).

### 3.2 Routes API

- **Publiques** (sans auth) :
  - `GET /tables/{id}` — détails table (pour scan QR).
  - `GET /tables/{id}/menu` — menu pour une table (pour QR).
  - `GET /categories`, `GET /categories/{id}` — consultation menu.
  - `GET /produits`, `GET /produits/{id}`.
  - `GET /tables`, `GET /tables/libres`.
  - `POST /reservations/verifier-disponibilite`.
  - `POST /auth/register`, `POST /auth/login`.

- **Protégées (auth:sanctum)** :
  - **Auth** : logout, me, refresh, `POST /auth/fcm-token`.
  - **Dashboard** : `GET /dashboard/stats` (permission `view_dashboard`).
  - **Notifications** : liste, unread-count, mark read, mark all read.
  - **Tables** : QR, `PATCH /tables/{id}/statut`, CRUD + regenerate QR (permissions `update_table_status`, `manage_tables`).
  - **Catégories / Produits** : CRUD (permission `manage_menu`).
  - **Commandes** : liste, détail, create, update, add/remove produit, `POST /commandes/{id}/lancer`, `POST /commandes/{id}/marquer-servi`, `GET /commandes/{id}/facture`, `PATCH /commandes/{id}/statut` (permissions `view_orders`, `create_orders`, `update_orders`, `update_order_status`).
  - **Paiements** : liste, détail, create, espèces, confirmer (client), valider/échouer/annuler (staff), téléchargement facture (permissions `view_cashier`, `process_payments`, etc.).
  - **Réservations** : liste, create, show, `PATCH /confirmer`, `PATCH /annuler` (confirmer réservé à `manage_reservations`).

### 3.3 App Flutter (`resto-app/lib`)

- **Config** : `config/api_config.dart` — `baseUrl = 'http://restaurant.universaltechnologiesafrica.com/api'` + tous les endpoints (auth, tables, categories, products, orders, payments, reservations, notifications).
- **Services** :
  - `api_service.dart` — client HTTP (Dio), baseUrl + Bearer token.
  - `auth_service.dart` — login, register, logout, me, stockage token.
  - `menu_service.dart` — catégories, produits.
  - `table_service.dart` — tables, `GET /tables/{id}/menu`.
  - `order_service.dart` — commandes (création, liste, détail, lancer, marquer servi, statut).
  - `payment_service.dart` — paiements (créer, espèces, confirmer, valider, facture).
  - `reservation_service.dart` — vérifier dispo, créer, liste, détail, confirmer, annuler.
  - `notification_service.dart` — liste notifs, unread count, mark read.
  - `invoice_service.dart` — facture.
  - `fcm_service.dart` — envoi du FCM token à l’API (`POST /auth/fcm-token`), gestion des notifications push.
- **State** : Provider — `AuthService`, `Cart`, `Favorites`.
- **Firebase** : `firebase_core`, `firebase_messaging`, `firebase_options.dart`, handler background.
- **Flux client** : scan QR table (format `/api/tables/{id}/menu` ou `/tables/{id}`) → menu → panier → création commande → lancer commande → paiement (Wave/Orange Money ou espèces côté staff) / facture.

---

## 4. Points d’intégration clés

1. **QR code table** : généré côté web, URL pointe vers l’API (menu ou détail table). L’app scanne le QR, extrait l’ID de table, charge le menu et permet de commander.
2. **Authentification** : même API pour web (session) et mobile (Sanctum token). L’app enregistre le token puis l’utilise pour toutes les routes protégées.
3. **FCM** : l’app envoie le token device via `POST /auth/fcm-token`. Le backend envoie les notifications push (et stocke en base en fallback). Même principe que Teranga Guest (FCM + polling si push bloqué).
4. **Commandes** : créées soit par le staff (web ou app), soit par le client (app) pour sa table ; workflow commun (attente → préparation → servie → terminée) + paiements et factures.
5. **Réservations** : vérification dispo en public ; création et gestion (confirmer/annuler) en authentifié, avec permissions côté staff.

---

## 5. Comparaison rapide avec Teranga Guest

| Aspect | Resto | Teranga Guest |
|--------|-------|----------------|
| Tenant | Single (un restaurant) | Multi-tenant (entreprises, hôtels) |
| Unité principale | Table (QR) | Chambre + User tablette (room_id / room_number) |
| Menu / commandes | Menu restaurant (catégories, produits), commande par table | Room service (menu par établissement), commande par chambre |
| Paiements | Caisse, Wave, Orange Money, factures | Contexte hôtel (facturation chambre, etc.) |
| Réservations | Réservation de tables | Réservations multi-services (restaurant, spa, excursions, etc.) |
| Mobile | Client qui scanne QR ou utilise l’app (commandes, paiements, résa) | Tablette par chambre (guest) + app pour certains services |
| Notifications | FCM + stockage en base (comme Teranga) | FCM + stockage en base (idem) |

---

## 6. Résumé

- **Resto** : un repo Laravel + Flutter dans le même dossier ; backend unique (pas de multi-tenant) ; tout tourne autour des **tables** (QR), du **menu**, des **commandes** et de la **caisse**, avec réservations et notifications FCM.
- **Intégration** : Web = back-office ; API = lien commun entre web et app ; App = client (scan QR, commande, paiement, réservations) avec auth Sanctum et FCM.
- Même pattern qu’avec Teranga Guest : API REST, Sanctum, FCM, notifications en base en secours — mais domaine métier et flux (table vs chambre, restaurant vs hôtel) différents.
