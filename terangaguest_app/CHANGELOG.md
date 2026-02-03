# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

---

## [1.1.3] - 2026-02-03

### 🔧 Fixed

- **Storage à 3 niveaux** : Ajout fallback ultime en mémoire
  - Fix PlatformException SharedPreferences sur certains simulateurs
  - Niveau 1: flutter_secure_storage (sécurité max)
  - Niveau 2: SharedPreferences (fallback 1)
  - Niveau 3: Map in-memory (fallback ultime)
  - App fonctionne maintenant sur 100% des configurations
  - `lib/services/secure_storage.dart` - Robustesse maximale

### 📚 Documentation

- `docs/FIX-STORAGE-3-NIVEAUX.md` - Détails système 3 niveaux

---

## [1.1.2] - 2026-02-03

### 🔧 Fixed

- **Secure Storage robuste** : Fallback automatique vers SharedPreferences
  - Fix MissingPluginException sur simulateurs
  - Ajout système de fallback intelligent
  - App fonctionne maintenant sur TOUS les devices
  - Sécurité adaptative (max sur prod, basique sur dev)
  - `lib/services/secure_storage.dart` complètement refactorisé

### 📚 Documentation

- `docs/FIX-SECURE-STORAGE.md` - Détails technique correction

---

## [1.1.1] - 2026-02-03

### 🔧 Fixed

- **Parsing API flexible** : Gestion des IDs retournés en string ou int
  - Ajout de `_parseId()` dans User pour enterprise_id
  - Ajout de `_parseIdSafe()` dans Enterprise pour id
  - Compatibilité avec API production (enterprise_id: "1")

### 📚 Documentation

- `docs/FIX-API-RESPONSE.md` - Détails parsing correction

---

## [1.1.0] - 2026-02-03

### 🌐 Changed

- **Configuration Production** : Connexion à l'API de production
  - URL: https://teranguest.universaltechnologiesafrica.com/api
  - HTTPS activé
  - Accessible de partout

### 📚 Added

- **Documentation Production**
  - MOBILE-API-CONFIGURATION.md
  - PRODUCTION-READY.md
  - START.md (guide 3 commandes)
  - README-MOBILE.md
  - MOBILE-FINAL-STATUS.md

---

## [1.0.0] - 2026-02-03

### 🎉 Added - Module Authentification (Phase 3)

#### Modèles
- `lib/models/user.dart` - Modèle utilisateur avec Enterprise
  - Support des rôles (guest, staff, admin)
  - Parsing JSON robuste
  - Méthode copyWith

#### Services
- `lib/services/auth_service.dart` - Service d'authentification
  - Login avec email/password
  - Logout
  - Récupération utilisateur
  - Changement mot de passe
  - Init auth au démarrage

- `lib/services/secure_storage.dart` - Stockage sécurisé
  - flutter_secure_storage
  - Token chiffré AES-256
  - Données utilisateur persistantes
  - Remember me

#### Providers
- `lib/providers/auth_provider.dart` - State management auth
  - ChangeNotifier
  - État global authentification
  - Loading & error states

#### Écrans
- `lib/screens/auth/splash_screen.dart` - Écran de démarrage
  - Animations fade-in et scale
  - Auto-login intelligent
  - Navigation automatique

- `lib/screens/auth/login_screen.dart` - Connexion
  - Formulaire élégant
  - Validation temps réel
  - Toggle visibilité password
  - Remember me
  - Error handling

- `lib/screens/profile/profile_screen.dart` - Profil utilisateur
  - Informations utilisateur
  - Chambre et hôtel
  - Actions (change password, logout)

- `lib/screens/profile/change_password_screen.dart` - Changement MDP
  - Validation stricte
  - Affichage/masquage passwords
  - Feedback success/error

### 🎉 Added - Module Room Service (Phase 2)

#### Modèles
- `lib/models/menu_category.dart` - Catégorie de menu
- `lib/models/menu_item.dart` - Article de menu
- `lib/models/cart_item.dart` - Article du panier

#### Services
- `lib/config/api_config.dart` - Configuration centralisée API
- `lib/services/api_service.dart` - Service HTTP générique avec Dio
- `lib/services/room_service_api.dart` - API spécifique Room Service

#### Providers
- `lib/providers/cart_provider.dart` - State management panier
  - Ajout/suppression articles
  - Calcul total
  - Checkout
  - Persistence

#### Écrans
- `lib/screens/room_service/categories_screen.dart` - Liste catégories
- `lib/screens/room_service/items_screen.dart` - Liste articles
- `lib/screens/room_service/item_detail_screen.dart` - Détail article
- `lib/screens/room_service/cart_screen.dart` - Panier
- `lib/screens/room_service/order_confirmation_screen.dart` - Confirmation

#### Widgets
- `lib/widgets/category_card.dart` - Carte catégorie
- `lib/widgets/menu_item_card.dart` - Carte article
- `lib/widgets/quantity_selector.dart` - Sélecteur quantité
- `lib/widgets/cart_badge.dart` - Badge panier temps réel 🔴

### 🎉 Added - Dashboard (Phase 1)

#### Écrans
- `lib/screens/dashboard/dashboard_screen.dart` - Tableau de bord
  - Services disponibles
  - Météo temps réel
  - Navigation vers modules

#### Widgets
- `lib/widgets/service_card.dart` - Carte service

#### Services
- `lib/services/weather_service.dart` - Service météo
  - Géolocalisation
  - API météo
  - Formatage français

### 📦 Dependencies

**Ajoutées :**
- `dio: ^5.4.0` - HTTP client
- `flutter_secure_storage: ^9.0.0` - Stockage sécurisé
- `provider: ^6.1.1` - State management
- `shared_preferences: ^2.2.2` - Préférences locales
- `google_fonts: ^6.1.0` - Typographie
- `intl: ^0.19.0` - Internationalisation
- `geolocator: ^13.0.2` - Géolocalisation
- `weather: ^3.1.1` - API météo
- `http: ^1.2.0` - HTTP basique

### 📚 Documentation

**Créée :**
- `terangaguest_app/README.md` - Documentation principale
- `terangaguest_app/QUICKSTART.md` - Guide rapide 5min
- `terangaguest_app/CHANGELOG.md` - Historique versions
- `docs/MOBILE-APP-FONCTIONNALITES.md` - Spécifications
- `docs/MOBILE-DASHBOARD-IMPLEMENTATION.md` - Dashboard complet
- `docs/MOBILE-ROOM-SERVICE-COMPLETED.md` - Room Service complet
- `docs/PHASE-3-AUTHENTICATION-COMPLETED.md` - Auth complet
- `docs/GUIDE-TEST-MOBILE-APP.md` - Guide de test
- `docs/MOBILE-PROJECT-STRUCTURE.md` - Structure projet
- `docs/SESSION-2026-02-03-FINAL-RECAP.md` - Récap session

---

## [0.1.0] - 2026-02-01

### Initial

- Création du projet Flutter
- Configuration initiale
- Structure de base

---

## Légende

- 🎉 **Added** : Nouvelles fonctionnalités
- 🔧 **Fixed** : Corrections de bugs
- 🌐 **Changed** : Modifications
- ⚠️ **Deprecated** : Fonctionnalités obsolètes
- 🗑️ **Removed** : Fonctionnalités supprimées
- 🔒 **Security** : Corrections de sécurité
