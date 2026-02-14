# 🚀 DÉMARRAGE RAPIDE - TERANGUEST MOBILE

**Version :** 1.1.0  
**API :** Production (https://teranguest.com)

---

## ⚡ LANCER EN 3 COMMANDES

```bash
# 1. Aller dans le dossier
cd terangaguest_app

# 2. Installer les dépendances (si pas déjà fait)
flutter pub get

# 3. Lancer l'application
flutter run
```

**C'est tout ! 🎉**

---

## 🔑 CONNEXION

**Compte de test :**
```
Email: guest@teranga.com
Password: passer123
```

---

## 📱 DEVICES DISPONIBLES

### Voir les devices
```bash
flutter devices
```

### Lancer sur un device spécifique

**iPad Pro (Simulateur) :**
```bash
flutter run -d "iPad Pro 13-inch (M5)"
```

**macOS (Desktop) :**
```bash
flutter run -d macos
```

**Device physique (wireless) :**
```bash
flutter run -d "00008140-0001284C2ED8801C"
```

---

## 🎯 FONCTIONNALITÉS À TESTER

### 1. Authentification
- ✅ Login avec guest@teranga.com
- ✅ Auto-login au relancement
- ✅ Voir profil (icône haut droite)
- ✅ Changer mot de passe
- ✅ Déconnexion

### 2. Room Service
- ✅ Parcourir catégories
- ✅ Rechercher articles
- ✅ Ajouter au panier
- ✅ Badge panier 🔴
- ✅ Modifier panier
- ✅ Commander
- ✅ Confirmation

---

## 🔥 HOT RELOAD

Pendant `flutter run` :
```
r   → Hot reload (rapide)
R   → Hot restart (complet)
q   → Quitter
```

---

## 🌐 API DE PRODUCTION

**URL :** https://teranguest.com

**Avantages :**
- ✅ Pas besoin de backend local
- ✅ Accessible de partout
- ✅ HTTPS sécurisé
- ✅ Données réelles

---

## 🆘 AIDE RAPIDE

### Problème : App ne démarre pas
```bash
flutter clean
flutter pub get
flutter run
```

### Problème : Device non trouvé
```bash
flutter devices
# Puis lancer avec -d <device_id>
```

### Problème : Login échoue
- Vérifier connexion internet
- Vérifier identifiants
- Vérifier que l'API prod est en ligne

---

## 📚 DOCUMENTATION

- **README.md** → Documentation complète
- **QUICKSTART.md** → Guide 5 minutes
- **../docs/GUIDE-TEST-MOBILE-APP.md** → Tests détaillés

---

**🎊 PRÊT À TESTER ! LANCEZ L'APP ! 🚀**
