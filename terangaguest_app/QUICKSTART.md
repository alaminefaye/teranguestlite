# 🚀 QUICKSTART - Teranga Guest Mobile

## ⚡ Lancement Rapide

### 1. Vérifier Flutter
```bash
flutter doctor
```

### 2. Installer les dépendances
```bash
cd terangaguest_app
flutter pub get
```

### 3. Lancer l'application

**Sur émulateur/simulateur :**
```bash
flutter run
```

**Sur appareil physique :**
```bash
flutter devices
flutter run -d <device_id>
```

---

## 📱 Émulateurs Recommandés

### Android
- **Pixel 7 Pro** (API 34)
- **Pixel 6** (API 33)

### iOS
- **iPhone 15 Pro** (iOS 17)
- **iPad Pro 12.9"** (iOS 17)

---

## 🎨 Ce Qui Est Prêt

✅ **Dashboard Principal**
- Design bleu marine + or élégant
- 8 services en grille 2x4
- Header avec notifications/profil
- Footer avec heure en temps réel

---

## 🔧 Commandes Utiles

### Hot Reload
```bash
# Pendant flutter run :
r  → Hot reload (rapide)
R  → Hot restart (complet)
q  → Quitter
```

### Clean Build
```bash
flutter clean
flutter pub get
flutter run
```

### Analyser le Code
```bash
flutter analyze
```

### Formater le Code
```bash
flutter format lib/
```

---

## 📸 Tester le Dashboard

1. **Lancer l'app** avec `flutter run`
2. **Voir** le dashboard avec 8 services
3. **Taper** sur n'importe quel service
4. **Voir** le SnackBar de confirmation

---

## 🐛 Problèmes Courants

### "No devices found"
```bash
# Android
flutter emulators
flutter emulators --launch <emulator_id>

# iOS  
open -a Simulator
```

### "Gradle build failed"
```bash
cd android
./gradlew clean
cd ..
flutter clean && flutter pub get
```

### "Font loading failed"
```bash
flutter pub cache repair
flutter clean && flutter pub get
```

---

## 📂 Structure Créée

```
lib/
├── main.dart                 ✅ App entry point
├── config/
│   └── theme.dart           ✅ Colors, fonts, theme
├── models/
│   └── service_item.dart    ✅ Service model
├── screens/
│   └── dashboard/
│       └── dashboard_screen.dart  ✅ Home screen
└── widgets/
    └── service_card.dart    ✅ Reusable service card
```

---

## 🎯 Prochaines Étapes

### À Développer Ensuite
1. **Room Service** (Priority #1)
   - Liste catégories
   - Liste articles
   - Panier

2. **Authentication**
   - Splash screen
   - Login screen
   - API integration

3. **Navigation**
   - Bottom navigation bar
   - Routes management

---

## 📞 Besoin d'Aide ?

Consultez :
- `README.md` - Documentation complète
- `docs/MOBILE-DASHBOARD-COMPLETE.md` - Dashboard détaillé
- `docs/MOBILE-APP-FONCTIONNALITES.md` - Toutes les fonctionnalités

---

**🎉 Prêt à développer ! Lancez `flutter run` et c'est parti ! 🚀**
