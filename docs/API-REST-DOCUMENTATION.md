# 📡 API REST - Teranga Guest

Documentation complète de l'API REST pour l'application mobile Teranga Guest.

**Version :** 1.0.0  
**Base URL :** `https://api.terangaguest.com/api`  
**Authentification :** Laravel Sanctum (Bearer Token)

---

## 📋 TABLE DES MATIÈRES

1. [Authentification](#authentification)
2. [Structure des Réponses](#structure-des-réponses)
3. [Gestion des Erreurs](#gestion-des-erreurs)
4. [Endpoints par Module](#endpoints-par-module)
5. [Modèles de Données](#modèles-de-données)
6. [Codes de Statut HTTP](#codes-de-statut-http)
7. [Pagination](#pagination)
8. [Filtres et Recherche](#filtres-et-recherche)

---

## 🔐 AUTHENTIFICATION

### 1. Connexion (Login)

**Endpoint:** `POST /api/auth/login`

**Corps de la requête:**
```json
{
  "email": "guest@teranga.com",
  "password": "passer123"
}
```

**Réponse (200 OK):**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 1,
      "name": "Guest Test",
      "email": "guest@teranga.com",
      "role": "guest",
      "enterprise_id": 1,
      "room_number": "101",
      "must_change_password": false
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer"
  }
}
```

### 2. Déconnexion (Logout)

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse (200 OK):**
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

### 3. Rafraîchir le Profil

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse (200 OK):**
```json
{
  "id": 1,
  "name": "Guest Test",
  "email": "guest@teranga.com",
  "role": "guest",
  "enterprise_id": 1,
  "enterprise": {
    "id": 1,
    "name": "King Fahd Palace",
    "logo": "logos/hotel.jpg"
  },
  "room_number": "101"
}
```

### 4. Changement de Mot de Passe

**Endpoint:** `POST /api/auth/change-password`

**Corps de la requête:**
```json
{
  "current_password": "passer123",
  "password": "nouveau_mot_de_passe",
  "password_confirmation": "nouveau_mot_de_passe"
}
```

---

## 📦 STRUCTURE DES RÉPONSES

### Réponse de Succès

```json
{
  "success": true,
  "message": "Opération réussie",
  "data": {
    // ... données
  },
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

### Réponse d'Erreur

```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": {
    "field_name": ["Le champ est requis"]
  }
}
```

---

## ❌ GESTION DES ERREURS

### Codes de Statut

| Code | Description | Utilisation |
|------|-------------|-------------|
| 200 | OK | Requête réussie |
| 201 | Created | Ressource créée |
| 400 | Bad Request | Données invalides |
| 401 | Unauthorized | Non authentifié |
| 403 | Forbidden | Accès refusé |
| 404 | Not Found | Ressource non trouvée |
| 422 | Unprocessable Entity | Validation échouée |
| 500 | Server Error | Erreur serveur |

---

## 🎯 ENDPOINTS PAR MODULE

### 📱 FCM Tokens

#### Enregistrer un Token
**POST** `/api/fcm-token`

```json
{
  "fcm_token": "device_token_here"
}
```

#### Supprimer un Token
**DELETE** `/api/fcm-token`

---

### 🍽️ ROOM SERVICE

#### Liste des Catégories de Menu
**GET** `/api/room-service/categories`

**Paramètres:**
- `available` (boolean) - Filtrer par disponibilité
- `search` (string) - Recherche par nom

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Petit-déjeuner",
      "description": "Menu du matin",
      "image": "categories/breakfast.jpg",
      "display_order": 1,
      "items_count": 12
    }
  ]
}
```

#### Liste des Articles de Menu
**GET** `/api/room-service/items`

**Paramètres:**
- `category_id` (integer) - Filtrer par catégorie
- `available` (boolean) - Articles disponibles
- `search` (string) - Recherche

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Omelette aux Légumes",
      "description": "Omelette fraîche...",
      "price": 3500.00,
      "formatted_price": "3 500 FCFA",
      "image": "menu/omelette.jpg",
      "preparation_time": 15,
      "is_available": true,
      "category": {
        "id": 1,
        "name": "Petit-déjeuner"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### Détails d'un Article
**GET** `/api/room-service/items/{id}`

#### Passer une Commande
**POST** `/api/room-service/checkout`

**Corps:**
```json
{
  "items": [
    {
      "menu_item_id": 1,
      "quantity": 2,
      "special_instructions": "Sans oignons"
    }
  ],
  "special_instructions": "Livrer en chambre 101",
  "delivery_time": "2026-02-02 13:30:00"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Commande passée avec succès",
  "data": {
    "id": 123,
    "order_number": "CMD-20260202-123",
    "total_amount": 7000.00,
    "formatted_total": "7 000 FCFA",
    "status": "pending",
    "items": [
      {
        "menu_item": {
          "id": 1,
          "name": "Omelette aux Légumes",
          "image": "menu/omelette.jpg"
        },
        "quantity": 2,
        "unit_price": 3500.00,
        "subtotal": 7000.00,
        "special_instructions": "Sans oignons"
      }
    ],
    "created_at": "2026-02-02 12:30:00"
  }
}
```

---

### 📦 COMMANDES

#### Mes Commandes
**GET** `/api/orders`

**Paramètres:**
- `status` (string) - pending, confirmed, preparing, ready, delivering, delivered, cancelled
- `page` (integer)
- `per_page` (integer) - Défaut: 15

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "order_number": "CMD-20260202-123",
      "total_amount": 7000.00,
      "formatted_total": "7 000 FCFA",
      "status": "confirmed",
      "status_label": "Confirmée",
      "items_count": 2,
      "created_at": "2026-02-02 12:30:00",
      "estimated_delivery": "2026-02-02 13:30:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 15
  }
}
```

#### Détails d'une Commande
**GET** `/api/orders/{id}`

**Réponse:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "order_number": "CMD-20260202-123",
    "total_amount": 7000.00,
    "formatted_total": "7 000 FCFA",
    "status": "confirmed",
    "status_label": "Confirmée",
    "special_instructions": "Livrer en chambre 101",
    "items": [
      {
        "id": 1,
        "menu_item": {
          "id": 1,
          "name": "Omelette aux Légumes",
          "image": "menu/omelette.jpg"
        },
        "quantity": 2,
        "unit_price": 3500.00,
        "subtotal": 7000.00,
        "special_instructions": "Sans oignons"
      }
    ],
    "created_at": "2026-02-02 12:30:00",
    "updated_at": "2026-02-02 12:35:00"
  }
}
```

#### Recommander
**POST** `/api/orders/{id}/reorder`

---

### 🍷 RESTAURANTS & BARS

#### Liste des Restaurants
**GET** `/api/restaurants`

**Paramètres:**
- `type` (string) - restaurant, bar, lounge
- `open_now` (boolean)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Le Gourmet",
      "type": "restaurant",
      "type_label": "Restaurant",
      "description": "Restaurant gastronomique...",
      "cuisine_type": "Français",
      "image": "restaurants/gourmet.jpg",
      "capacity": 50,
      "opening_hours": {
        "lundi": "12:00 - 23:00",
        "mardi": "12:00 - 23:00"
      },
      "is_open_now": true,
      "today_hours": "12:00 - 23:00"
    }
  ]
}
```

#### Détails d'un Restaurant
**GET** `/api/restaurants/{id}`

#### Réserver une Table
**POST** `/api/restaurants/{id}/reserve`

**Corps:**
```json
{
  "date": "2026-02-05",
  "time": "20:00",
  "guests": 4,
  "special_requests": "Table près de la fenêtre"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Réservation confirmée",
  "data": {
    "id": 45,
    "restaurant": {
      "id": 1,
      "name": "Le Gourmet"
    },
    "date": "2026-02-05",
    "time": "20:00",
    "guests": 4,
    "special_requests": "Table près de la fenêtre",
    "status": "confirmed"
  }
}
```

#### Mes Réservations Restaurants
**GET** `/api/my-restaurant-reservations`

---

### 💆 SPA & BIEN-ÊTRE

#### Liste des Services Spa
**GET** `/api/spa-services`

**Paramètres:**
- `category` (string) - massage, soin_visage, soin_corps, manucure, autre
- `available` (boolean)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Massage Relaxant",
      "category": "massage",
      "category_label": "Massage",
      "description": "Massage complet...",
      "price": 25000.00,
      "formatted_price": "25 000 FCFA",
      "duration": 60,
      "duration_text": "1h",
      "image": "spa/massage.jpg",
      "features": ["Huiles essentielles", "Musique relaxante"]
    }
  ]
}
```

#### Réserver un Service Spa
**POST** `/api/spa-services/{id}/reserve`

**Corps:**
```json
{
  "date": "2026-02-05",
  "time": "15:00",
  "special_requests": "Préférence thérapeute féminine"
}
```

#### Mes Réservations Spa
**GET** `/api/my-spa-reservations`

---

### 🌴 EXCURSIONS

#### Liste des Excursions
**GET** `/api/excursions`

**Paramètres:**
- `type` (string) - culturelle, aventure, plage, safari, ville
- `available` (boolean)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Visite de l'Île de Gorée",
      "type": "culturelle",
      "type_label": "Culturelle",
      "description": "Découverte historique...",
      "price_adult": 15000.00,
      "price_child": 7500.00,
      "formatted_price_adult": "15 000 FCFA",
      "formatted_price_child": "7 500 FCFA",
      "duration_hours": 4,
      "departure_time": "08:00",
      "image": "excursions/goree.jpg",
      "min_participants": 4,
      "max_participants": 20,
      "included": ["Transport", "Guide", "Déjeuner"],
      "not_included": ["Boissons", "Souvenirs"]
    }
  ]
}
```

#### Réserver une Excursion
**POST** `/api/excursions/{id}/book`

**Corps:**
```json
{
  "date": "2026-02-10",
  "adults": 2,
  "children": 1,
  "special_requests": "Régime végétarien"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Excursion réservée",
  "data": {
    "id": 67,
    "excursion": {
      "id": 1,
      "name": "Visite de l'Île de Gorée"
    },
    "date": "2026-02-10",
    "adults": 2,
    "children": 1,
    "total_price": 37500.00,
    "formatted_total": "37 500 FCFA",
    "status": "confirmed"
  }
}
```

#### Mes Réservations Excursions
**GET** `/api/my-excursion-bookings`

---

### 👔 BLANCHISSERIE

#### Liste des Services Blanchisserie
**GET** `/api/laundry-services`

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Chemise",
      "category": "vetements",
      "category_label": "Vêtements",
      "price": 1500.00,
      "formatted_price": "1 500 FCFA",
      "turnaround_hours": 24
    }
  ]
}
```

#### Demander un Service Blanchisserie
**POST** `/api/laundry/request`

**Corps:**
```json
{
  "items": [
    {
      "laundry_service_id": 1,
      "quantity": 3
    },
    {
      "laundry_service_id": 2,
      "quantity": 2
    }
  ],
  "pickup_time": "2026-02-03 09:00",
  "special_instructions": "Urgent"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Demande de blanchisserie enregistrée",
  "data": {
    "id": 89,
    "request_number": "LAU-20260202-089",
    "total_price": 8500.00,
    "formatted_total": "8 500 FCFA",
    "items": [
      {
        "service": {
          "id": 1,
          "name": "Chemise"
        },
        "quantity": 3,
        "unit_price": 1500.00,
        "subtotal": 4500.00
      }
    ],
    "pickup_time": "2026-02-03 09:00",
    "estimated_delivery": "2026-02-04 09:00",
    "status": "pending"
  }
}
```

#### Mes Demandes Blanchisserie
**GET** `/api/my-laundry-requests`

---

### 🎯 SERVICES PALACE

#### Liste des Services Palace
**GET** `/api/palace-services`

**Paramètres:**
- `category` (string) - conciergerie, transport, evenement, autre
- `premium` (boolean)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Réservation Restaurant Externe",
      "category": "conciergerie",
      "category_label": "Conciergerie",
      "description": "Réservation dans les meilleurs restaurants...",
      "price": 5000.00,
      "formatted_price": "5 000 FCFA",
      "price_on_request": false,
      "is_premium": true
    }
  ]
}
```

#### Demander un Service Palace
**POST** `/api/palace-services/{id}/request`

**Corps:**
```json
{
  "requested_for": "2026-02-05 19:00",
  "description": "Réservation pour 4 personnes au restaurant La Paix",
  "special_requirements": "Table VIP"
}
```

#### Mes Demandes Palace
**GET** `/api/my-palace-requests`

---

## 📊 MODÈLES DE DONNÉES

### User

```json
{
  "id": 1,
  "name": "Guest Test",
  "email": "guest@teranga.com",
  "role": "guest",
  "enterprise_id": 1,
  "department": null,
  "room_number": "101",
  "must_change_password": false,
  "fcm_token": "device_token...",
  "created_at": "2026-01-01 10:00:00"
}
```

### Order

```json
{
  "id": 123,
  "order_number": "CMD-20260202-123",
  "user_id": 1,
  "enterprise_id": 1,
  "room_id": 5,
  "total_amount": 7000.00,
  "formatted_total": "7 000 FCFA",
  "status": "confirmed",
  "status_name": "Confirmée",
  "special_instructions": "...",
  "items": [],
  "created_at": "2026-02-02 12:30:00",
  "updated_at": "2026-02-02 12:35:00"
}
```

---

## ⚙️ PAGINATION

Tous les endpoints de liste supportent la pagination.

**Paramètres:**
- `page` (integer) - Numéro de page (défaut: 1)
- `per_page` (integer) - Éléments par page (défaut: 15, max: 100)

**Réponse:**
```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 73
  },
  "links": {
    "first": "http://api.../resource?page=1",
    "last": "http://api.../resource?page=5",
    "prev": null,
    "next": "http://api.../resource?page=2"
  }
}
```

---

## 🔍 FILTRES ET RECHERCHE

### Filtres Communs

- `search` - Recherche textuelle
- `sort_by` - Champ de tri (ex: name, created_at)
- `sort_order` - asc ou desc
- `per_page` - Nombre d'éléments par page

### Exemples

```
GET /api/room-service/items?search=omelette&available=1&per_page=20
GET /api/orders?status=pending&sort_by=created_at&sort_order=desc
GET /api/restaurants?type=restaurant&open_now=1
```

---

## 🔔 NOTIFICATIONS PUSH

Les notifications sont envoyées automatiquement via Firebase Cloud Messaging pour :

- Nouvelle commande confirmée
- Changement de statut de commande
- Confirmation de réservation
- Rappels de rendez-vous
- Messages du staff

**Format de Notification:**
```json
{
  "title": "Commande #CMD-20260202-123",
  "body": "Votre commande est prête",
  "data": {
    "type": "order_status",
    "order_id": "123",
    "order_number": "CMD-20260202-123",
    "status": "ready",
    "screen": "OrderDetails"
  }
}
```

---

## 🧪 ENVIRONNEMENTS

### Développement
- **Base URL:** `http://localhost:8000/api`
- **Timeout:** 30s

### Production
- **Base URL:** `https://api.terangaguest.com/api`
- **Timeout:** 30s
- **Rate Limit:** 60 requêtes/minute

---

## 📝 NOTES IMPORTANTES

1. **Authentification Requise:** Tous les endpoints (sauf login) nécessitent un token Bearer
2. **Enterprise Scoping:** Les données sont automatiquement filtrées par entreprise
3. **Timestamps:** Format ISO 8601 (YYYY-MM-DD HH:MM:SS)
4. **Images:** URLs relatives, préfixer avec le domaine
5. **Montants:** En FCFA, format décimal (2 chiffres après virgule)

---

## 🔐 SÉCURITÉ

- ✅ HTTPS obligatoire en production
- ✅ Tokens Sanctum avec expiration
- ✅ Rate limiting activé
- ✅ Validation stricte des entrées
- ✅ Protection CSRF désactivée pour API
- ✅ CORS configuré

---

## 📞 SUPPORT

**Documentation complète :** `/docs/API-REST-DOCUMENTATION.md`  
**Postman Collection :** À venir  
**Swagger UI :** À venir

---

**Version :** 1.0.0  
**Dernière mise à jour :** 02 Février 2026  
**Statut :** 🔄 En développement
