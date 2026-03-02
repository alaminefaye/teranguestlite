# Prérequis production – Play Store & App Store (v1.0.0)

Ce document liste tout ce qu’il faut pour publier **TeranGuest** en **version 1.0.0** sur Google Play et l’App Store, sans casser le projet.

---

## Version de l’app

- **Version utilisateur :** `1.0.0`
- **Build number :** `1`
- Défini dans `pubspec.yaml` : `version: 1.0.0+1`  
  (Android : `versionName` / `versionCode` ; iOS : `CFBundleShortVersionString` / `CFBundleVersion`)

---

## Android – Play Store

### 1. Signature release (obligatoire pour la prod)

Sans keystore, le build release utilise encore la clé debug (pour ne pas casser le build). Pour la **production**, il faut signer avec **votre** keystore.

**Créer le keystore (une seule fois) :**
```bash
cd terangaguest_app/android
keytool -genkey -v -keystore upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```
Répondre aux questions, **noter et conserver** les mots de passe.

**Créer `android/key.properties`** (fichier ignoré par git) :
```properties
storePassword=VOTRE_STORE_PASSWORD
keyPassword=VOTRE_KEY_PASSWORD
keyAlias=upload
storeFile=upload-keystore.jks
```
Si le `.jks` est ailleurs, mettre le chemin **relatif au dossier `android/`** (ex. `../upload-keystore.jks`).

Un exemple est dans `android/key.properties.example` (à copier en `key.properties` et à remplir).

### 2. Build release

```bash
flutter clean
flutter pub get
flutter build appbundle --release
```

Fichier généré : `build/app/outputs/bundle/release/app-release.aab`

### 3. Identifiants actuels

- **Application ID :** `com.teranguest.app`
- **Nom affiché :** Teranguest (dans `AndroidManifest.xml`)

### 4. Play Console – à préparer

- Compte [Google Play Console](https://play.google.com/console)
- Fiche Play Store : description courte/longue, captures d’écran, icône 512×512, Feature Graphic 1024×500
- Politique de confidentialité : `https://teranguest.com/politique-de-confidentialite`
- Classification du contenu, public cible, formulaire « Données de sécurité »
- Premier upload : créer l’application avec l’ID `com.teranguest.app` (ou l’avoir déjà créé avec ce même ID)

---

## iOS – App Store

### 1. Configuration Xcode

```bash
open terangaguest_app/ios/Runner.xcworkspace
```

- **Signing & Capabilities :** « Automatically manage signing », choisir votre **Team**
- **Bundle Identifier :** `com.teranguest.app` (déjà configuré)
- **Deployment Target :** iOS 12.0 ou plus (vérifier dans le projet)

### 2. Export compliance & réseau

Déjà configuré dans `ios/Runner/Info.plist` :

- **ITSAppUsesNonExemptEncryption** = `false` (évite le questionnaire export si vous n’utilisez que du HTTPS standard)
- **NSAppTransportSecurity** : pas d’accès HTTP arbitraire (connexions sécurisées)

### 3. Build & archive

```bash
flutter clean
flutter pub get
flutter build ios --release
```

Puis dans Xcode :

1. **Product → Archive**
2. Quand l’archive est prête : **Distribute App** → **App Store Connect** → suivre l’assistant
3. Téléverser le build vers App Store Connect

### 4. App Store Connect – à préparer

- Compte [App Store Connect](https://appstoreconnect.apple.com)
- Fiche : nom « TeranGuest », description, captures (6.7", 6.5", 5.5"), catégorie (ex. Voyage)
- **URL de la politique de confidentialité :** `https://teranguest.com/politique-de-confidentialite`
- Prix : Gratuit
- Sélectionner le build uploadé et soumettre pour review

---

## Vérifications avant soumission

- [ ] **Version** : `1.0.0` (et build 1) partout
- [ ] **API** : l’app pointe bien vers l’URL de production (ex. `https://teranguest.com/api`)
- [ ] **Android** : `key.properties` + keystore en place pour un **vrai** build signé release
- [ ] **iOS** : certificat et provisioning App Store valides, Bundle ID `com.teranguest.app`
- [ ] **Politique de confidentialité** accessible et à jour sur le site
- [ ] **Firebase** : `GoogleService-Info.plist` (iOS) et `google-services.json` (Android) configurés pour le projet de prod (notifications push)

---

## Résumé des commandes

| Plateforme | Commande |
|-----------|--------|
| Android (AAB pour Play Store) | `flutter build appbundle --release` |
| iOS (puis archive dans Xcode) | `flutter build ios --release` puis Product → Archive |

Aucune autre modification du code n’est nécessaire pour passer en 1.0.0 et préparer les deux stores ; la signature Android est optionnelle jusqu’à ce que vous créiez `key.properties`.
