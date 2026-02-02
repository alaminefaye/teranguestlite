# Teranga Guest — Spécification des 4 modules à développer

> **Référence visuelle :** barre d’icônes (fond noir) — 4 modules à coder.  
> **Projet :** application tablette / guest in-room (Laravel + Blade).

---

## Vue d’ensemble des 4 modules

| Icône | Module | Nom court | Description |
|-------|--------|------------|-------------|
| Lit (bed) | **Chambre** | `chambre` | Chambre, hébergement, réveil, infos chambre |
| Verre / cocktail | **Restaurant & Bar** | `restaurant` | Restaurant, room service, bar, boissons |
| Cloche (bell) | **Réception / Concierge** | `reception` | Réception, concierge, service client, alertes |
| Panier / panier à linge | **Commandes & Services** | `commandes` | Commandes, mini-bar, blanchisserie, achats |

---

## 1. Chambre (icône lit)

**Objectif :** tout ce qui concerne la chambre et le séjour.

### À coder

- **Infos chambre**
  - Numéro de chambre, type, équipements
  - Heures check-in / check-out
  - Plan de l’étage (optionnel)
- **Réveil (wake-up call)**
  - Demande d’appel réveil (heure, répétition)
  - Statut de la demande (en attente, confirmé, fait)
- **Préférences chambre**
  - Literie (oreillers, couverture)
  - Ne pas déranger / faire la chambre
- **Contrôle chambre** (si intégration prévue)
  - Lumière, climatisation, rideaux (liens ou placeholders pour API)

### Routes proposées

```
GET  /chambre              → page principale Chambre
GET  /chambre/infos        → infos chambre
POST /chambre/reveil       → demande réveil
GET  /chambre/preferences  → préférences
POST /chambre/preferences  → enregistrer préférences
```

### Backend (Laravel)

- Modèle : `Room`, `WakeUpCall`, `RoomPreference` (ou champs dans `reservations`)
- Contrôleur : `ChambreController` ou `RoomController`
- Tables : `rooms`, `wake_up_calls`, `room_preferences` (ou équivalent)

---

## 2. Restaurant & Bar (icône cocktail)

**Objectif :** commande de repas et boissons (room service, restaurant, bar).

### À coder

- **Menus**
  - Liste des menus (petit-déjeuner, déjeuner, dîner, bar)
  - Catégories : plats, boissons, snacks
  - Affichage prix, descriptions, images
- **Room service / Commande**
  - Panier (ajout, suppression, quantités)
  - Heure de livraison souhaitée
  - Instructions spéciales (allergies, remarques)
  - Récap et envoi de la commande
- **Suivi commande**
  - Statut : reçue, en préparation, en livraison, livrée
  - Historique des commandes du séjour

### Routes proposées

```
GET  /restaurant           → page Restaurant & Bar (menus)
GET  /restaurant/menus     → liste menus / catégories
GET  /restaurant/panier    → panier actuel
POST /restaurant/panier    → ajouter / modifier panier
POST /restaurant/commander → valider commande
GET  /restaurant/commandes → mes commandes (suivi)
GET  /restaurant/commandes/{id} → détail d’une commande
```

### Backend (Laravel)

- Modèles : `Menu`, `MenuItem`, `Category`, `Order`, `OrderItem`
- Contrôleur : `RestaurantController` ou `RoomServiceController`
- Tables : `menus`, `menu_items`, `categories`, `orders`, `order_items`

---

## 3. Réception / Concierge (icône cloche)

**Objectif :** contacter la réception, concierge, service client, alertes.

### À coder

- **Contacter la réception**
  - Liste des services : Réception, Concierge, Housekeeping, Maintenance, etc.
  - Appel / message (formulaire ou ouverture chat)
- **Demandes de service**
  - Type : info, problème technique, ménage, serviettes, etc.
  - Message libre + pièce jointe optionnelle
  - Suivi des demandes (en attente, en cours, résolu)
- **Alertes / notifications**
  - Affichage des messages envoyés par l’hôtel (maintenance, événements, infos)
- **Concierge**
  - Réservation restaurant, taxi, excursions (formulaires ou liens)

### Routes proposées

```
GET  /reception            → page Réception / Concierge
GET  /reception/services   → liste des services / départements
POST /reception/demande    → envoyer une demande (type + message)
GET  /reception/demandes  → mes demandes (suivi)
GET  /reception/alertes   → notifications / messages de l’hôtel
```

### Backend (Laravel)

- Modèles : `ServiceRequest`, `Department`, `HotelMessage` (alertes)
- Contrôleur : `ReceptionController`
- Tables : `departments`, `service_requests`, `hotel_messages`

---

## 4. Commandes & Services (icône panier)

**Objectif :** mini-bar, blanchisserie, achats, autres commandes (hors restaurant).

### À coder

- **Mini-bar**
  - Liste des articles (boissons, snacks) avec prix
  - Consommation : ajout au panier / inventaire
  - Validation et facturation (ligne sur la facture chambre)
- **Blanchisserie / pressing**
  - Types de service : lavage, repassage, pressing
  - Formulaire : type d’article, quantité, instructions, créneau souhaité
  - Tarifs et suivi de la demande
- **Autres commandes**
  - Articles de toilette (extra toiletries)
  - Equipements (fer, baby-sitter, etc.) selon offre hôtel
  - Panier unique ou listes séparées selon le besoin

### Routes proposées

```
GET  /commandes            → page Commandes & Services (accueil)
GET  /commandes/minibar    → liste mini-bar, consommation
POST /commandes/minibar    → enregistrer consommation / commande mini-bar
GET  /commandes/blanchisserie → formulaire blanchisserie, tarifs
POST /commandes/blanchisserie → envoyer demande blanchisserie
GET  /commandes/autres     → autres services (toiletries, équipements)
POST /commandes/autres     → envoyer demande
GET  /commandes/historique → historique des commandes (mini-bar, blanchisserie, etc.)
```

### Backend (Laravel)

- Modèles : `MinibarItem`, `MinibarConsumption`, `LaundryRequest`, `ExtraServiceRequest`
- Contrôleur : `CommandesController` ou `ServicesController`
- Tables : `minibar_items`, `minibar_consumptions`, `laundry_requests`, `extra_requests`

---

## Structure technique à mettre en place

### Frontend (Blade + Vite)

- **Layout guest / tablette**
  - Barre latérale ou barre d’icônes (les 4 icônes) sur fond noir
  - Zone de contenu principale selon le module actif
- **Composants réutilisables**
  - Cartes module (titre + icône + lien)
  - Formulaires (réveil, commande, demande)
  - Liste de suivi (commandes, demandes)
- **Assets**
  - Icônes : lit, cocktail, cloche, panier (SVG ou PNG) — alignées avec ton visuel

### Backend (Laravel)

- **Auth / contexte client**
  - Identification du client (session, token, ou lien chambre/réservation)
  - Middleware ou scope pour limiter les données à la chambre / réservation du client
- **API ou full Blade**
  - Soit tout en Blade (formulaires + redirect)
  - Soit API JSON pour la tablette (AJAX/fetch) + Blade pour l’admin
- **Notifications**
  - Envoi des demandes à la réception (email, base de données, ou intégration PMS)

### Base de données (idées de tables)

- `rooms` — chambres
- `reservations` — séjours (lien guest / chambre / dates)
- `wake_up_calls` — réveils
- `menus`, `menu_items`, `categories` — restaurant
- `orders`, `order_items` — commandes restaurant
- `minibar_items`, `minibar_consumptions` — mini-bar
- `laundry_requests` — blanchisserie
- `service_requests`, `departments` — réception / demandes
- `hotel_messages` — alertes / messages de l’hôtel

---

## Ordre de développement suggéré

1. **Chambre** — infos + réveil (simple, bon pour poser la structure et l’auth).
2. **Réception** — demandes de service (formulaire + suivi, utile pour tout le reste).
3. **Restaurant & Bar** — menus + panier + commande (cœur métier).
4. **Commandes & Services** — mini-bar + blanchisserie (variante de commandes).

---

## Récap : ce qui est à coder (aligné avec tes 4 icônes)

| # | Module | Icône | Pages à créer | Principales entités |
|---|--------|--------|----------------|---------------------|
| 1 | Chambre | Lit | Infos chambre, Réveil, Préférences | Room, WakeUpCall, RoomPreference |
| 2 | Restaurant & Bar | Cocktail | Menus, Panier, Commander, Suivi | Menu, Order, OrderItem |
| 3 | Réception | Cloche | Contacter, Demandes, Alertes | ServiceRequest, Department, HotelMessage |
| 4 | Commandes & Services | Panier | Mini-bar, Blanchisserie, Autres | MinibarItem, LaundryRequest, ExtraRequest |

Ce document sert de **spécification pour le développement** : tu peux t’y référer pour coder chaque module (routes, contrôleurs, modèles, vues) dans Teranga Guest.
