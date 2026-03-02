# 🚀 GUIDE DE DÉPLOIEMENT - TERANGUEST MOBILE

**Version :** 1.0.0  
**Date :** Mars 2026  
**Statut :** Production-Ready  

Pour les prérequis détaillés Play Store et App Store (signature, build, checklist), voir **[PRODUCTION-STORES.md](PRODUCTION-STORES.md)**.

---

## 📋 PRÉ-REQUIS

### Outils Nécessaires
```
✅ Flutter SDK 3.x installé
✅ Xcode 15+ (pour iOS)
✅ Android Studio (pour Android)
✅ Compte Apple Developer (iOS)
✅ Compte Google Play Console (Android)
✅ Certificats de signature
```

### Vérifications
```bash
flutter doctor -v
flutter --version
dart --version
```

---

## 🔧 CONFIGURATION PRÉ-DÉPLOIEMENT

### 1. Mise à Jour Version

**`pubspec.yaml`**
```yaml
version: 1.0.0+1

# Format: version_name+build_number
# 1.0.0 = version affichée utilisateur
# 1 = build number (incrémenter à chaque soumission store)
```

### 2. Configuration API

**`lib/config/api_config.dart`**
```dart
// ✅ Vérifier que l'URL production est activée
static const String baseUrl = 'https://teranguest.com/api';

// ❌ Désactiver l'URL de développement
// static const String baseUrl = 'http://localhost:8000/api';
```

### 3. Icônes & Splash Screen

**Générer les icônes (si modifiées) :**
```bash
flutter pub run flutter_launcher_icons
```

**Vérifier les fichiers :**
```
android/app/src/main/res/
├─ mipmap-hdpi/ic_launcher.png
├─ mipmap-mdpi/ic_launcher.png
├─ mipmap-xhdpi/ic_launcher.png
├─ mipmap-xxhdpi/ic_launcher.png
└─ mipmap-xxxhdpi/ic_launcher.png

ios/Runner/Assets.xcassets/AppIcon.appiconset/
```

### 4. Permissions

**Android (`android/app/src/main/AndroidManifest.xml`) :**
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

**iOS (`ios/Runner/Info.plist`) :**
```xml
<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <false/>
</dict>
```

---

## 📱 DÉPLOIEMENT iOS

### Étape 1 : Configuration Xcode

```bash
# Ouvrir le projet iOS
open ios/Runner.xcworkspace
```

**Dans Xcode :**
1. Sélectionner `Runner` dans le navigateur
2. Onglet `Signing & Capabilities`
3. Activer `Automatically manage signing`
4. Sélectionner votre Team
5. Bundle ID : `com.teranguest.terangaguest_app`
6. Vérifier Deployment Target : iOS 12.0+

### Étape 2 : Build Release

```bash
# Nettoyer
flutter clean

# Récupérer dépendances
flutter pub get

# Build iOS
flutter build ios --release
```

### Étape 3 : Archive avec Xcode

1. Xcode → Product → Archive
2. Attendre la fin du build
3. Organizer s'ouvre automatiquement
4. Sélectionner l'archive
5. Cliquer "Distribute App"
6. Choisir "App Store Connect"
7. Suivre l'assistant
8. Upload vers App Store Connect

### Étape 4 : App Store Connect

1. Aller sur [appstoreconnect.apple.com](https://appstoreconnect.apple.com)
2. My Apps → Sélectionner TerangueST
3. Préparer la soumission :
   - Screenshots (6.7", 6.5", 5.5")
   - Description
   - Mots-clés
   - Catégorie : Travel
   - Prix : Gratuit
4. Sélectionner le build uploadé
5. Soumettre pour review

**Délai review Apple : 1-3 jours**

---

## 🤖 DÉPLOIEMENT ANDROID

### Étape 1 : Configuration Gradle

**`android/app/build.gradle`**
```gradle
android {
    namespace "com.teranguest.terangaguest_app"
    compileSdkVersion 34

    defaultConfig {
        applicationId "com.teranguest.terangaguest_app"
        minSdkVersion 21
        targetSdkVersion 34
        versionCode 2
        versionName "2.0.1"
    }

    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            shrinkResources true
        }
    }
}
```

### Étape 2 : Signing Key

**Créer la clé (1ère fois seulement) :**
```bash
keytool -genkey -v -keystore ~/upload-keystore.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias upload

# Sauvegarder le mot de passe !
```

**`android/key.properties` (créer) :**
```properties
storePassword=VOTRE_STORE_PASSWORD
keyPassword=VOTRE_KEY_PASSWORD
keyAlias=upload
storeFile=/Users/VOTRE_NOM/upload-keystore.jks
```

**`android/app/build.gradle` (ajouter) :**
```gradle
def keystoreProperties = new Properties()
def keystorePropertiesFile = rootProject.file('key.properties')
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
}

android {
    signingConfigs {
        release {
            keyAlias keystoreProperties['keyAlias']
            keyPassword keystoreProperties['keyPassword']
            storeFile keystoreProperties['storeFile'] ? file(keystoreProperties['storeFile']) : null
            storePassword keystoreProperties['storePassword']
        }
    }
}
```

**⚠️ NE PAS COMMIT `key.properties` et `upload-keystore.jks` !**

### Étape 3 : Build Release

```bash
# Nettoyer
flutter clean

# Récupérer dépendances
flutter pub get

# Build Android App Bundle
flutter build appbundle --release

# Ou APK (moins recommandé)
# flutter build apk --release --split-per-abi
```

**Fichier généré :**
```
build/app/outputs/bundle/release/app-release.aab
```

### Étape 4 : Google Play Console

1. Aller sur [play.google.com/console](https://play.google.com/console)
2. Sélectionner l'app (ou créer)
3. Production → Créer une version
4. Upload `app-release.aab`
5. Remplir les informations :
   - Notes de version
   - Screenshots (téléphone, tablette)
   - Description courte et longue
   - Catégorie : Voyages et infos locales
   - Icône 512×512
   - Feature Graphic 1024×500
6. Contenu :
   - Classification du contenu
   - Public cible
   - Confidentialité
7. Soumettre pour review

**Délai review Google : Quelques heures à 1 jour**

---

## 🧪 TESTS PRÉ-DÉPLOIEMENT

### Tests Manuels Obligatoires
```
✅ Installer build release sur device physique
✅ Tester connexion API production
✅ Vérifier toutes les fonctionnalités
✅ Tester offline (messages d'erreur)
✅ Vérifier performance (pas de lag)
✅ Tester sur iOS (si déploiement iOS)
✅ Tester sur Android (si déploiement Android)
```

### Commandes de Test
```bash
# Installer release sur device Android
flutter install --release

# Logs en temps réel
flutter logs
```

---

## 📊 MONITORING POST-DÉPLOIEMENT

### Métriques à Surveiller
```
✅ Taux de crash (< 1%)
✅ Temps de chargement (< 2s)
✅ Taux d'adoption (installs/jour)
✅ Reviews utilisateurs
✅ Taux de rétention (J1, J7, J30)
```

### Outils Recommandés
- **Firebase Crashlytics** : Suivi des crashes
- **Firebase Analytics** : Comportement utilisateur
- **Sentry** : Error tracking
- **App Store Connect Analytics** : Métriques iOS
- **Google Play Console** : Métriques Android

---

## 🔄 MISES À JOUR FUTURES

### Processus
```
1. Développer nouvelles features
2. Incrémenter version dans pubspec.yaml
3. Tester en local
4. Build release
5. Upload vers stores
6. Attendre validation
7. Publier
8. Surveiller métriques
```

### Versions Sémantiques
```
MAJOR.MINOR.PATCH+BUILD

MAJOR : Changements incompatibles
MINOR : Nouvelles fonctionnalités
PATCH : Corrections de bugs
BUILD : Incrémenté à chaque build
```

**Exemples :**
```
2.0.1+2 → Version actuelle
2.0.2+3 → Correction bug
2.1.0+4 → Nouvelle feature
3.0.0+5 → Breaking changes
```

---

## ⚠️ CHECKLIST FINALE

```
✅ API en production configurée
✅ Version incrémentée
✅ Icônes configurés
✅ Permissions configurées
✅ Signing keys configurés
✅ Build release réussi
✅ Tests manuels effectués
✅ Screenshots préparés
✅ Descriptions rédigées
✅ Politique de confidentialité prête
✅ Support email configuré
✅ Monitoring configuré
```

---

## 📞 SUPPORT

### Informations à Fournir
```
Email : support@teranguest.com
Site web : https://teranguest.com
Politique confidentialité : https://teranguest.com/privacy
Conditions d'utilisation : https://teranguest.com/terms
```

---

## 🎊 RÉSUMÉ DES COMMANDES

### iOS
```bash
flutter clean
flutter pub get
flutter build ios --release
# Puis Xcode → Archive → Upload
```

### Android
```bash
flutter clean
flutter pub get
flutter build appbundle --release
# Puis upload build/app/outputs/bundle/release/app-release.aab
```

---

## 📈 MÉTRIQUES DE SUCCÈS

**Objectifs :**
```
- 1000 installs en 1 mois
- Note moyenne > 4.5/5
- Taux de crash < 1%
- Taux de rétention J7 > 40%
```

---

**🚀 APPLICATION PRÊTE POUR LE DÉPLOIEMENT ! 🚀**

**Bonne chance pour la mise en production ! 🎉**

---

**© 2026 TerangueST - Déploiement Production**
