# ⚡ QUICKSTART - 5 Minutes pour Lancer l'App

---

## 🎯 Objectif

Lancer l'application mobile TerangueST en **moins de 5 minutes**.

---

## 📋 Étape 1 : Vérifier Flutter

```bash
flutter doctor
```

**Résultat attendu :** ✅ Flutter SDK installé

---

## 📦 Étape 2 : Installer les Dépendances

```bash
cd terangaguest_app
flutter pub get
```

**Durée :** ~10 secondes

---

## 🔧 Étape 3 : Lancer le Backend

**Dans un nouveau terminal :**

```bash
cd /Users/Zhuanz/Desktop/projets/web/terangaguest
php artisan serve
```

**Vérifier :** http://localhost:8000 accessible

---

## 🚀 Étape 4 : Lancer l'App Mobile

```bash
flutter run
```

**Ou spécifier un device :**

```bash
# iPad Pro
flutter run -d "iPad Pro 13-inch (M5)"

# macOS
flutter run -d macos
```

**Durée :** ~30 secondes (première fois)

---

## 🔑 Étape 5 : Se Connecter

**Compte de test :**
```
Email: guest@teranga.com
Password: passer123
```

**Actions :**
1. Attendre le SplashScreen (2s)
2. Sur LoginScreen, entrer email + password
3. Tap "Se connecter"
4. **Dashboard affiché !** ✅

---

## 🎉 TERMINÉ !

Vous êtes maintenant dans l'application. Testez :

- 🍽️ **Room Service** → Commandez un article
- 👤 **Profil** → Icône en haut à droite
- 🛒 **Panier** → Badge rouge après ajout

---

## 🆘 Problèmes ?

### App ne démarre pas
```bash
flutter clean
flutter pub get
flutter run
```

### Backend non accessible
```bash
# Vérifier que le serveur tourne
php artisan serve

# Tester dans le navigateur
open http://localhost:8000
```

### Device non trouvé
```bash
# Lister les devices
flutter devices

# Ouvrir un simulateur
open -a Simulator
```

---

## 📚 Pour Aller Plus Loin

- **Guide de test complet :** `../docs/GUIDE-TEST-MOBILE-APP.md`
- **Documentation :** `README.md`
- **API :** `../docs/API-REST-DOCUMENTATION.md`

---

**⚡ PRÊT EN 5 MINUTES ! 🚀**
