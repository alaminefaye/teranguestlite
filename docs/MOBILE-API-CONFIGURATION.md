# 🔌 CONFIGURATION API - APPLICATION MOBILE

**Date :** 3 Février 2026  
**Version :** 1.1.0

---

## 🌐 URL DE PRODUCTION

L'application mobile est maintenant connectée à l'**API de production** :

```
https://teranguest.universaltechnologiesafrica.com/api
```

---

## ⚙️ CONFIGURATION ACTUELLE

### Fichier de Configuration

**Emplacement :** `lib/config/api_config.dart`

**Configuration actuelle :**
```dart
class ApiConfig {
  // Base URL de l'API
  // Production
  static const String baseUrl = 'https://teranguest.universaltechnologiesafrica.com/api';
  
  // Développement (localhost)
  // static const String baseUrl = 'http://localhost:8000/api';
  
  // Timeout en millisecondes
  static const int connectTimeout = 30000;
  static const int receiveTimeout = 30000;
}
```

---

## 🔄 BASCULER ENTRE DEV ET PROD

### Pour utiliser la Production (Actuel)

```dart
static const String baseUrl = 'https://teranguest.universaltechnologiesafrica.com/api';
// static const String baseUrl = 'http://localhost:8000/api';
```

### Pour utiliser le Développement Local

```dart
// static const String baseUrl = 'https://teranguest.universaltechnologiesafrica.com/api';
static const String baseUrl = 'http://localhost:8000/api';
```

### Pour Device Physique (Dev Local)

```dart
// Remplacer 192.168.X.X par votre IP locale
static const String baseUrl = 'http://192.168.1.100:8000/api';
```

**Trouver votre IP :**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```

---

## 🔐 AUTHENTIFICATION

### Production

**Compte de test de production :**
```
Email: guest@teranga.com
Password: passer123
```

**Note :** Utiliser les vrais comptes créés sur le serveur de production.

### Développement Local

**Compte seedé :**
```
Email: guest@teranga.com
Password: passer123
```

---

## 📡 ENDPOINTS DISPONIBLES

### URL Complète

**Base :** `https://teranguest.universaltechnologiesafrica.com/api`

**Exemples :**
```
POST   https://teranguest.universaltechnologiesafrica.com/api/auth/login
GET    https://teranguest.universaltechnologiesafrica.com/api/user
GET    https://teranguest.universaltechnologiesafrica.com/api/room-service/categories
POST   https://teranguest.universaltechnologiesafrica.com/api/room-service/checkout
```

---

## 🧪 TESTER LA CONNEXION

### Depuis le Terminal

```bash
# Test de connexion à l'API
curl https://teranguest.universaltechnologiesafrica.com/api/room-service/categories

# Test de login
curl -X POST https://teranguest.universaltechnologiesafrica.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"guest@teranga.com","password":"passer123"}'
```

### Depuis l'App Mobile

1. Lancer l'app : `flutter run`
2. Sur LoginScreen, entrer identifiants
3. Observer les logs dans le terminal

**Logs attendus :**
```
✅ POST https://teranguest.../api/auth/login
✅ Response 200 OK
✅ Token received
✅ Navigation to Dashboard
```

---

## 🔒 SÉCURITÉ

### HTTPS Activé ✅

L'API de production utilise **HTTPS** :
- ✅ Connexion chiffrée SSL/TLS
- ✅ Token Bearer sécurisé
- ✅ Pas de données en clair

### Configuration Flutter

**Android :** Aucune configuration nécessaire (HTTPS par défaut)

**iOS :** Aucune configuration nécessaire (HTTPS par défaut)

**Note :** Le fichier `Info.plist` permet HTTP en développement, mais HTTPS est prioritaire.

---

## ⚠️ NOTES IMPORTANTES

### 1. Timeout

**Configuré à 30 secondes :**
```dart
static const int connectTimeout = 30000;
static const int receiveTimeout = 30000;
```

Si l'API de production est lente, augmenter si nécessaire.

### 2. Headers

**Headers automatiques :**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  // Après login
```

### 3. Gestion des Erreurs

**L'application gère automatiquement :**
- ❌ Pas de connexion internet
- ❌ Timeout
- ❌ Erreurs 400, 401, 403, 404, 422, 500
- ❌ Token expiré (401) → Logout automatique

---

## 🚀 LANCER EN PRODUCTION

### Étape 1 : Vérifier la Configuration

```bash
# Ouvrir le fichier
open terangaguest_app/lib/config/api_config.dart
```

**Vérifier que l'URL de production est active :**
```dart
static const String baseUrl = 'https://teranguest.universaltechnologiesafrica.com/api';
```

### Étape 2 : Clean & Rebuild

```bash
cd terangaguest_app
flutter clean
flutter pub get
```

### Étape 3 : Lancer l'App

```bash
flutter run
```

### Étape 4 : Se Connecter

**Sur LoginScreen :**
```
Email: guest@teranga.com
Password: passer123
```

### Étape 5 : Tester

- ✅ Dashboard s'affiche
- ✅ Room Service charge les catégories depuis l'API prod
- ✅ Commande fonctionne
- ✅ Profil affiche les vraies données

---

## 🎯 AVANTAGES DE LA PROD

### ✅ Accessibilité

- Accessible depuis **n'importe quel device**
- Pas besoin de backend local
- Pas besoin de configuration IP
- Fonctionne sur WiFi, 4G, 5G

### ✅ Données Réelles

- Vrais utilisateurs
- Vraies commandes
- Vraies réservations
- Synchronisation en temps réel

### ✅ Stabilité

- Serveur toujours disponible
- Sauvegarde automatique
- Pas de perte de données

---

## 📊 COMPARAISON

| Aspect | Développement Local | **Production** |
|--------|---------------------|----------------|
| URL | localhost:8000 | teranguest.universaltechnologiesafrica.com |
| HTTPS | ❌ HTTP | ✅ HTTPS |
| Accessibilité | Même réseau | Partout dans le monde |
| Données | Seeders | Vraies données |
| Stabilité | Dépend de votre Mac | Serveur dédié |
| Setup | Backend local requis | Rien ! |
| **Recommandé pour** | Développement | **Tests & Production** |

---

## 🎉 AVANTAGES ACTUELS

### ✅ Configuration Optimale

L'application mobile est maintenant configurée pour :
- Se connecter à l'API de production
- Fonctionner depuis n'importe quel device
- Utiliser HTTPS sécurisé
- Accéder aux vraies données

### ✅ Prêt pour les Tests

Vous pouvez maintenant :
- Tester sur iPhone/iPad physique
- Tester depuis n'importe où
- Partager l'app avec des testeurs
- Utiliser des données réelles

### ✅ Pas de Configuration Réseau

Plus besoin de :
- ❌ Configurer l'IP locale
- ❌ Lancer le backend local
- ❌ Être sur le même WiFi
- ❌ Gérer les problèmes de firewall

---

## 🚀 LANCEMENT RAPIDE

```bash
# C'est tout ! 🎉
cd terangaguest_app
flutter run
```

**Login avec :**
```
guest@teranga.com / passer123
```

**Et c'est parti ! 🚀**

---

## 📝 NOTES

### Switch Dev/Prod

Pour basculer facilement entre dev et prod, vous pouvez créer un fichier d'environnement ou utiliser des build flavors. Pour l'instant, on commente/décommente la ligne dans `api_config.dart`.

### Compte de Production

Assurez-vous que le compte `guest@teranga.com` existe sur le serveur de production avec les bonnes données (hôtel, chambre, etc.).

---

**✅ APPLICATION MOBILE CONFIGURÉE POUR LA PRODUCTION ! 🌐**

Vous pouvez maintenant tester l'application depuis n'importe quel device avec une connexion internet !
