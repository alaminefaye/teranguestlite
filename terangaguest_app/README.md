# 📱 TerangueST Mobile App

**Version :** 1.1.0  
**Date :** 3 Février 2026  
**Plateforme :** Flutter (iOS/Android)  
**Statut :** 🚧 En développement - 3 modules complétés

---

## 🎯 DESCRIPTION

Application mobile de gestion hôtelière pour les clients, permettant de :
- 🔐 S'authentifier de manière sécurisée
- 🍽️ Commander Room Service
- 🍷 Réserver des tables au restaurant
- 💆 Réserver des services Spa
- 🏖️ Réserver des excursions
- 👔 Demander des services de blanchisserie
- 👑 Accéder aux services Palace premium

---

## ✅ MODULES COMPLÉTÉS

| Module | Statut | Écrans | Fonctionnalités |
|--------|--------|--------|-----------------|
| **Dashboard** | ✅ 100% | 1 | Accueil, 8 services, météo temps réel |
| **Authentification** | ✅ 100% | 4 | Login, auto-login, profil, logout |
| **Room Service** | ✅ 100% | 5 | Catégories, articles, panier, commande |
| Commandes | ⏳ 0% | 0 | - |
| Restaurants | ⏳ 0% | 0 | - |
| Spa | ⏳ 0% | 0 | - |
| Excursions | ⏳ 0% | 0 | - |
| Blanchisserie | ⏳ 0% | 0 | - |
| Services Palace | ⏳ 0% | 0 | - |

**Progression :** 3/9 modules = **33%** complété

---

## 🚀 QUICK START

### Prérequis

- Flutter SDK >= 3.10.7
- Dart SDK >= 3.0.0
- Xcode (pour iOS)
- Android Studio (pour Android)

### Installation

```bash
# Cloner le projet (si nécessaire)
cd /Users/Zhuanz/Desktop/projets/web/terangaguest/terangaguest_app

# Installer les dépendances
flutter pub get

# Vérifier la configuration
flutter doctor
```

### Lancer l'Application

```bash
# Lister les devices disponibles
flutter devices

# Lancer sur iPad Pro (simulateur)
flutter run -d "iPad Pro 13-inch (M5)"

# Lancer sur macOS
flutter run -d macos

# Lancer sur device physique
flutter run -d <device_id>
```

### Hot Reload

Pendant `flutter run` :
- `r` - Hot reload (rapide)
- `R` - Hot restart (complet)
- `q` - Quitter

---

## 🔧 CONFIGURATION

### Backend API

**Fichier :** `lib/config/api_config.dart`

**Développement (simulateur/émulateur) :**
```dart
static const String baseUrl = 'http://localhost:8000/api';
```

**Device physique :**
```dart
static const String baseUrl = 'http://192.168.X.X:8000/api';
```

**Trouver votre IP :**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```

### Compte de Test

```
Email: guest@teranga.com
Password: passer123
```

---

## 📦 PACKAGES UTILISÉS

```yaml
# State Management
provider: ^6.1.1

# HTTP & API
dio: ^5.4.0

# Storage
flutter_secure_storage: ^9.0.0
shared_preferences: ^2.2.2

# UI
google_fonts: ^6.1.0

# Utils
intl: ^0.20.2
geolocator: ^13.0.3
weather: ^3.1.1
```

---

## 🏗️ STRUCTURE DU PROJET

```
lib/
├── config/
│   ├── theme.dart              # Design system
│   └── api_config.dart         # Configuration API
│
├── models/
│   ├── user.dart               # Modèle utilisateur
│   ├── menu_category.dart      # Catégorie menu
│   ├── menu_item.dart          # Article menu
│   └── cart_item.dart          # Article panier
│
├── services/
│   ├── api_service.dart        # Client HTTP
│   ├── auth_service.dart       # Authentification
│   ├── room_service_api.dart   # API Room Service
│   ├── secure_storage.dart     # Stockage sécurisé
│   └── weather_service.dart    # Météo
│
├── providers/
│   ├── auth_provider.dart      # State auth
│   └── cart_provider.dart      # State panier
│
├── screens/
│   ├── auth/
│   │   ├── splash_screen.dart
│   │   └── login_screen.dart
│   ├── dashboard/
│   │   └── dashboard_screen.dart
│   ├── room_service/
│   │   ├── categories_screen.dart
│   │   ├── items_screen.dart
│   │   ├── item_detail_screen.dart
│   │   ├── cart_screen.dart
│   │   └── order_confirmation_screen.dart
│   └── profile/
│       ├── profile_screen.dart
│       └── change_password_screen.dart
│
├── widgets/
│   ├── service_card.dart
│   ├── category_card.dart
│   ├── menu_item_card.dart
│   ├── quantity_selector.dart
│   └── cart_badge.dart
│
└── main.dart                   # Entry point
```

---

## 🧪 TESTER L'APPLICATION

### Guide Complet

**Voir :** `../docs/GUIDE-TEST-MOBILE-APP.md`

### Tests Rapides

**1. Authentification**
```
1. Lancer l'app → SplashScreen
2. Login avec guest@teranga.com / passer123
3. Vérifier Dashboard affiché
4. Tap icône profil → Voir profil
5. Déconnexion → Retour login
```

**2. Room Service**
```
1. Dashboard → Tap "Room Service"
2. Tap une catégorie
3. Tap un article
4. Modifier quantité + instructions
5. Ajouter au panier → Badge +1
6. Tap badge panier
7. Commander → Confirmation
```

---

## 🎨 DESIGN

### Palette de Couleurs

- **Primary Dark:** `#0A1929` (Background principal)
- **Primary Blue:** `#1A2F44` (Background secondaire)
- **Accent Gold:** `#D4AF37` (Accent principal)
- **Text White:** `#FFFFFF` (Texte principal)
- **Text Gray:** `#B0B8C1` (Texte secondaire)

### Typographie

- **Titres :** Playfair Display
- **Corps :** Montserrat

---

## 📚 DOCUMENTATION

### Fichiers de Documentation

```
docs/
├── GUIDE-TEST-MOBILE-APP.md                    # Guide de test
├── MOBILE-DASHBOARD-IMPLEMENTATION.md          # Dashboard
├── MOBILE-ROOM-SERVICE-COMPLETED.md            # Room Service
├── PHASE-3-AUTHENTICATION-COMPLETED.md         # Authentification
├── SESSION-2026-02-03-FINAL-RECAP.md           # Récap session
└── MOBILE-APP-FONCTIONNALITES.md               # Specs complètes
```

### API Documentation

**Backend :** `docs/API-REST-DOCUMENTATION.md`

---

## 🐛 TROUBLESHOOTING

### App ne se lance pas

```bash
flutter clean
flutter pub get
flutter run
```

### Backend non accessible

**Vérifier :**
1. Backend lancé : `php artisan serve`
2. URL correcte dans `api_config.dart`
3. Firewall (device physique)

### Images ne se chargent pas

**Vérifier :**
1. Permission Internet (AndroidManifest.xml)
2. URLs d'images complètes dans l'API
3. Backend accessible

---

## 📊 MÉTRIQUES

| Métrique | Valeur |
|----------|--------|
| Fichiers Dart | 27 |
| Lignes de code | ~4200 |
| Écrans | 10 |
| Widgets | 5 |
| Services | 5 |
| Providers | 2 |
| Erreurs | 0 ✅ |

---

## 🔜 ROADMAP

### Février 2026
- ✅ Dashboard
- ✅ Authentification
- ✅ Room Service
- ⏳ Commandes & Historique
- ⏳ Restaurants & Bars

### Mars 2026
- ⏳ Spa & Bien-être
- ⏳ Excursions
- ⏳ Blanchisserie
- ⏳ Services Palace

### Avril 2026
- ⏳ Notifications Push
- ⏳ Bottom Navigation
- ⏳ Mode Offline
- ⏳ Tests complets

### Mai 2026
- ⏳ Déploiement iOS App Store
- ⏳ Déploiement Android Play Store

---

## 👥 ÉQUIPE

**Développeur :** AI Assistant (Claude)  
**Designer :** Inspiré King Fahd Palace Hotel  
**Backend :** Laravel API

---

## 📞 SUPPORT

**Documentation :** Voir dossier `docs/`  
**Issues :** Contactez l'équipe de développement  
**Tests :** Voir `GUIDE-TEST-MOBILE-APP.md`

---

## 📄 LICENSE

Propriétaire - TerangueST © 2026

---

**📱 TERANGA GUEST - L'EXCELLENCE AU BOUT DES DOIGTS ! ✨**
