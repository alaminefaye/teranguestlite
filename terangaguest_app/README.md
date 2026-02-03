# 📱 Teranga Guest Mobile App

Application mobile Flutter pour la gestion des services hôteliers.

## 🎨 Design

L'application utilise un design élégant inspiré du King Fahd Palace Hotel :
- **Couleurs :** Bleu marine foncé (#0A1929) avec accents dorés (#D4AF37)
- **Typographie :** Playfair Display (titres) + Montserrat (corps)
- **Style :** Luxueux et élégant

## 🚀 Installation

### Prérequis
- Flutter SDK (>= 3.10.7)
- Dart SDK
- Android Studio / Xcode (selon la plateforme)

### Étapes

1. **Installer les dépendances**
```bash
flutter pub get
```

2. **Vérifier la configuration Flutter**
```bash
flutter doctor
```

3. **Lancer l'application**

**Sur émulateur Android :**
```bash
flutter run
```

**Sur émulateur iOS :**
```bash
flutter run -d "iPhone 15 Pro"
```

**Sur un appareil physique :**
```bash
flutter devices  # Liste les appareils connectés
flutter run -d <device_id>
```

## 📁 Structure du Projet

```
lib/
├── main.dart                 # Point d'entrée de l'application
├── config/
│   └── theme.dart           # Configuration du thème (couleurs, typo)
├── models/
│   └── service_item.dart    # Modèle pour les services
├── screens/
│   └── dashboard/
│       └── dashboard_screen.dart  # Écran principal (Home)
└── widgets/
    └── service_card.dart    # Card réutilisable pour les services
```

## 📱 Écrans Disponibles

### ✅ Dashboard (Home)
- **Fichier :** `lib/screens/dashboard/dashboard_screen.dart`
- **Features :**
  - Header avec logo hôtel, notifications et profil
  - Message de bienvenue
  - Grille 2x4 de 8 services :
    1. 🍽️ Room Service
    2. 🍷 Restaurants & Bars
    3. 💆 Spa & Bien-être
    4. 👑 Services Palace
    5. 🏖️ Excursions
    6. 👔 Blanchisserie
    7. 🛎️ Conciergerie
    8. 📞 Centre d'Appels
  - Footer avec logo et heure en temps réel

## 🎨 Thème et Design System

### Couleurs
```dart
Primary Dark:      #0A1929  (Background principal)
Primary Blue:      #1A2F44  (Background secondaire)
Accent Gold:       #D4AF37  (Accent principal)
Accent Gold Light: #E5C158  (Accent secondaire)
Text White:        #FFFFFF  (Texte principal)
Text Gray:         #B0B8C1  (Texte secondaire)
```

### Composants

#### Service Card
- Bordure dorée (1.5px)
- Icon 48x48px en or
- Titre centré en 2 lignes max
- Background transparent
- Border radius 16px
- Effet tap avec feedback visuel

## 🔧 Configuration

### Android

**Fichier :** `android/app/build.gradle`
- Min SDK Version : 21
- Target SDK Version : 34
- Compile SDK Version : 34

### iOS

**Fichier :** `ios/Runner/Info.plist`
- Minimum iOS Version : 12.0
- Support iPad et iPhone

## 📦 Packages Utilisés

```yaml
# State Management
provider: ^6.1.1

# Fonts
google_fonts: ^6.1.0

# Utils
intl: ^0.19.0  # Formatage dates/heures
```

## 🎯 Prochaines Étapes

### Phase 1 : Room Service
- [ ] Liste catégories menu
- [ ] Liste articles
- [ ] Détail article
- [ ] Panier
- [ ] Checkout

### Phase 2 : Restaurants
- [ ] Liste restaurants
- [ ] Détail restaurant
- [ ] Réservation table

### Phase 3 : Autres Services
- [ ] Spa & Bien-être
- [ ] Excursions
- [ ] Blanchisserie
- [ ] Services Palace

### Phase 4 : Authentification
- [ ] Splash screen
- [ ] Login
- [ ] Gestion token

### Phase 5 : Notifications
- [ ] Firebase setup
- [ ] Push notifications

## 🐛 Debugging

### Problèmes Courants

**1. Erreur "Gradle build failed"**
```bash
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
```

**2. Fonts Google ne se chargent pas**
```bash
flutter pub cache repair
flutter pub get
```

**3. Hot reload ne fonctionne pas**
- Appuyez sur `R` (majuscule) pour full restart
- Ou utilisez `flutter run --hot`

## 📸 Screenshots

### Dashboard
![Dashboard](screenshots/dashboard.png)

---

## 📞 Support

Pour toute question ou problème :
- **Email :** support@terangaguest.com
- **Documentation :** `/docs/MOBILE-APP-FONCTIONNALITES.md`

## 📄 Licence

Copyright © 2026 Teranga Guest. Tous droits réservés.
