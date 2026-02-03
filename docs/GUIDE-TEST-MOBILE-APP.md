# 🧪 GUIDE DE TEST - APPLICATION MOBILE

**Date :** 3 Février 2026  
**Version :** 1.1.0  
**Modules :** Room Service + Authentification

---

## 🎯 OBJECTIF

Ce guide vous accompagne pour **tester l'application mobile** TeranguEST avec le backend Laravel.

---

## 📋 PRÉREQUIS

### 1. Backend Laravel Lancé

```bash
# Dans le dossier backend
cd /Users/Zhuanz/Desktop/projets/web/terangaguest

# Lancer le serveur
php artisan serve
```

**Vérifier :** http://localhost:8000 doit être accessible

### 2. Base de Données Seedée

```bash
# Exécuter les seeders
php artisan db:seed
```

**Résultat attendu :**
- ✅ Users créés (guest@teranga.com)
- ✅ Entreprises créées
- ✅ Catégories de menu
- ✅ Articles de menu
- ✅ Restaurants, Spa, Excursions, etc.

### 3. Configuration API Mobile

**Fichier :** `terangaguest_app/lib/config/api_config.dart`

**Pour simulateur/émulateur :**
```dart
static const String baseUrl = 'http://localhost:8000/api';
```

**Pour device physique :**
```dart
// Remplacer par l'IP de votre Mac
static const String baseUrl = 'http://192.168.1.100:8000/api';
```

**Trouver votre IP :**
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```

---

## 🚀 LANCER L'APPLICATION

### Option 1 : Sur iPad Pro (Simulateur)

```bash
cd terangaguest_app
flutter run -d "D4ED3836-48BF-4DDD-A2A6-9EC8EC92759D"
```

### Option 2 : Sur macOS

```bash
flutter run -d macos
```

### Option 3 : Sur Device Physique (wireless)

```bash
flutter run -d "00008140-0001284C2ED8801C"
```

**Important :** Modifier l'IP dans `api_config.dart` avant !

---

## ✅ TESTS À EFFECTUER

### 🔐 PHASE 1 : AUTHENTIFICATION

#### Test 1.1 : Splash Screen & Auto-Login

**Scénario :** Premier lancement (pas de token)

1. Lancer l'app
2. **Vérifier** :
   - ✅ SplashScreen s'affiche
   - ✅ Logo animé (fade + scale)
   - ✅ Texte "Bienvenue"
   - ✅ Loading indicator
   - ✅ Navigation automatique vers Login après 2s

**Résultat attendu :** LoginScreen affiché

---

#### Test 1.2 : Login avec Identifiants Valides

**Compte de test :**
```
Email: guest@teranga.com
Password: passer123
```

**Actions :**
1. Entrer l'email
2. Entrer le password
3. Cocher "Se souvenir de moi" (optionnel)
4. Tap "Se connecter"

**Vérifier :**
- ✅ Loading indicator pendant la requête
- ✅ Navigation vers Dashboard après succès
- ✅ Dashboard affiche le nom de l'hôtel

**Résultat attendu :** Connecté avec succès, Dashboard visible

---

#### Test 1.3 : Login avec Identifiants Invalides

**Actions :**
1. Entrer email invalide : `wrong@email.com`
2. Entrer password : `wrongpass`
3. Tap "Se connecter"

**Vérifier :**
- ✅ SnackBar rouge avec message d'erreur
- ✅ Reste sur LoginScreen
- ✅ Pas de navigation

**Résultat attendu :** Message "Identifiants invalides"

---

#### Test 1.4 : Validation Formulaire Login

**Actions :**
1. Laisser email vide → Tap "Se connecter"
   - **Vérifier :** "Veuillez entrer votre email"
2. Entrer email invalide `test@` → Tap
   - **Vérifier :** "Email invalide"
3. Entrer email valide, password vide → Tap
   - **Vérifier :** "Veuillez entrer votre mot de passe"
4. Entrer password < 6 chars → Tap
   - **Vérifier :** "Mot de passe trop court"

---

#### Test 1.5 : Auto-Login au Relancement

**Prérequis :** Être connecté

**Actions :**
1. Fermer l'app (Cmd+Q sur simulateur)
2. Relancer l'app
3. Observer

**Vérifier :**
- ✅ SplashScreen s'affiche brièvement
- ✅ Chargement automatique
- ✅ Navigation directe vers Dashboard
- ✅ Pas de LoginScreen

**Résultat attendu :** Auto-login réussi

---

### 👤 PHASE 2 : PROFIL

#### Test 2.1 : Voir le Profil

**Actions :**
1. Depuis Dashboard, tap icône profil (haut droite)

**Vérifier :**
- ✅ Navigation vers ProfileScreen
- ✅ Avatar avec initiale du nom
- ✅ Nom affiché
- ✅ Email affiché
- ✅ Numéro de chambre affiché
- ✅ Nom de l'hôtel affiché
- ✅ Rôle affiché ("Client")

---

#### Test 2.2 : Changer le Mot de Passe

**Actions :**
1. Depuis ProfileScreen, tap "Changer le mot de passe"
2. Entrer current password : `passer123`
3. Entrer new password : `NewPass123`
4. Confirmer : `NewPass123`
5. Tap "Enregistrer"

**Vérifier :**
- ✅ Loading indicator
- ✅ SnackBar vert "Succès"
- ✅ Retour au ProfileScreen

**Test de validation :**
6. Se déconnecter
7. Se reconnecter avec `NewPass123`
   - **Vérifier :** Login réussi

---

#### Test 2.3 : Validation Change Password

**Actions :**
1. Current password vide → Tap "Enregistrer"
   - **Vérifier :** "Champ requis"
2. New password < 8 chars → Tap
   - **Vérifier :** "Minimum 8 caractères"
3. New password sans majuscule → Tap
   - **Vérifier :** "Doit contenir une majuscule"
4. New password sans chiffre → Tap
   - **Vérifier :** "Doit contenir un chiffre"
5. Confirm password différent → Tap
   - **Vérifier :** "Les mots de passe ne correspondent pas"

---

#### Test 2.4 : Déconnexion

**Actions :**
1. Depuis ProfileScreen, tap "Déconnexion"
2. **Vérifier :** Dialog de confirmation s'affiche
3. Tap "Annuler"
   - **Vérifier :** Reste sur ProfileScreen
4. Tap "Déconnexion" à nouveau
5. Tap "Déconnexion" dans le dialog

**Vérifier :**
- ✅ Navigation vers LoginScreen
- ✅ Plus de retour possible vers Dashboard
- ✅ Token supprimé

**Test de validation :**
6. Relancer l'app
   - **Vérifier :** Pas d'auto-login, LoginScreen affiché

---

### 🍽️ PHASE 3 : ROOM SERVICE

#### Test 3.1 : Parcourir les Catégories

**Actions :**
1. Depuis Dashboard, tap "Room Service"

**Vérifier :**
- ✅ Navigation vers CategoriesScreen
- ✅ Grille 2 colonnes
- ✅ Cards élégantes avec bordures or
- ✅ Images des catégories
- ✅ Nombre d'articles par catégorie
- ✅ Badge panier (vide) en haut

**Résultat attendu :** Liste des catégories chargée depuis l'API

---

#### Test 3.2 : Parcourir les Articles

**Actions :**
1. Tap sur une catégorie (ex: "Petit-déjeuner")

**Vérifier :**
- ✅ Navigation vers ItemsScreen
- ✅ Titre = nom de la catégorie
- ✅ Liste des articles
- ✅ Cards avec images, prix, temps préparation
- ✅ Barre de recherche en haut

---

#### Test 3.3 : Recherche d'Articles

**Actions :**
1. Dans ItemsScreen, tap barre de recherche
2. Entrer "omelette"
3. Observer

**Vérifier :**
- ✅ Liste filtrée en temps réel
- ✅ Seulement articles matchant "omelette"
- ✅ Clear button (X) apparaît

4. Tap le X
   - **Vérifier :** Retour à la liste complète

---

#### Test 3.4 : Détail Article

**Actions :**
1. Tap sur un article

**Vérifier :**
- ✅ Navigation vers ItemDetailScreen
- ✅ Image en plein écran
- ✅ Nom, prix, description
- ✅ Temps de préparation
- ✅ Sélecteur de quantité (défaut: 1)
- ✅ Champ instructions spéciales

---

#### Test 3.5 : Ajouter au Panier

**Actions :**
1. Dans ItemDetailScreen
2. Tap "+" pour augmenter quantité → 2
3. Entrer instructions : "Sans oignons"
4. Tap "Ajouter au panier"

**Vérifier :**
- ✅ SnackBar vert "Article ajouté"
- ✅ Retour à ItemsScreen
- ✅ **Badge panier affiche "1"** 🔴

---

#### Test 3.6 : Badge Panier Temps Réel

**Actions :**
1. Ajouter 2-3 articles différents
2. Observer le badge panier

**Vérifier :**
- ✅ Badge se met à jour après chaque ajout
- ✅ Affiche le nombre d'articles (pas quantités totales)
- ✅ Visible sur tous les écrans (Categories, Items, Detail)

---

#### Test 3.7 : Voir le Panier

**Actions :**
1. Tap sur le badge panier (icône)

**Vérifier :**
- ✅ Navigation vers CartScreen
- ✅ Liste des articles ajoutés
- ✅ Images, noms, quantités, prix
- ✅ Instructions spéciales affichées
- ✅ Total calculé en bas
- ✅ Nombre total d'articles

---

#### Test 3.8 : Modifier le Panier

**Actions :**
1. Dans CartScreen
2. Tap "+" sur un article → quantité augmente
3. Tap "-" sur un article → quantité diminue
4. Tap icône poubelle → Dialog de suppression

**Vérifier :**
- ✅ Quantités se mettent à jour
- ✅ Prix sous-totaux recalculés
- ✅ Total général recalculé
- ✅ Badge panier se met à jour si suppression

---

#### Test 3.9 : Passer une Commande

**Actions :**
1. Dans CartScreen
2. (Optionnel) Ajouter instructions globales
3. Tap "Commander"

**Vérifier :**
- ✅ Loading indicator sur le bouton
- ✅ Requête API POST /checkout
- ✅ Navigation vers OrderConfirmationScreen
- ✅ Panier vidé automatiquement
- ✅ Badge panier = 0 (disparu)

---

#### Test 3.10 : Confirmation Commande

**Vérifier :**
- ✅ Animation succès (checkmark vert)
- ✅ Message "Commande confirmée !"
- ✅ Numéro de commande affiché
- ✅ Nombre d'articles
- ✅ Total correct
- ✅ Statut "En attente"
- ✅ Bouton "Retour à l'accueil"
- ✅ Bouton "Suivre ma commande"

**Actions :**
1. Tap "Retour à l'accueil"
   - **Vérifier :** Navigation vers Dashboard

---

#### Test 3.11 : Panier Vide

**Actions :**
1. Vider le panier (supprimer tous les articles)

**Vérifier :**
- ✅ Message "Votre panier est vide"
- ✅ Icône panier grisée
- ✅ Bouton "Parcourir le menu"
- ✅ Pas de bottom bar (Total + Commander)

---

### 🎨 TESTS UX

#### Test UX.1 : Navigation Fluide

**Actions :**
1. Parcourir : Dashboard → Room Service → Catégorie → Article → Panier
2. Utiliser le bouton retour à chaque étape

**Vérifier :**
- ✅ Transitions fluides
- ✅ Pas de lag
- ✅ Retour fonctionne correctement
- ✅ État préservé (panier, scroll)

---

#### Test UX.2 : Responsive Design

**Actions :**
1. Tester sur différentes tailles :
   - iPad Pro (grand écran)
   - iPhone (petit écran)
   - macOS (desktop)

**Vérifier :**
- ✅ Layout s'adapte
- ✅ Textes lisibles
- ✅ Boutons accessibles
- ✅ Images bien proportionnées

---

#### Test UX.3 : Pull-to-Refresh

**Actions :**
1. Dans CategoriesScreen, swipe vers le bas

**Vérifier :**
- ✅ Indicateur de chargement
- ✅ Liste se recharge
- ✅ Nouvelles données affichées

---

#### Test UX.4 : Pagination Automatique

**Actions :**
1. Dans ItemsScreen avec beaucoup d'articles
2. Scroller jusqu'en bas

**Vérifier :**
- ✅ Loading indicator apparaît
- ✅ Page suivante se charge automatiquement
- ✅ Articles ajoutés à la liste
- ✅ Scroll fluide

---

## 🐛 TESTS D'ERREURS

### Test Erreur 1 : Backend Offline

**Actions :**
1. Arrêter le backend (`Ctrl+C` dans php artisan serve)
2. Dans l'app, tap "Room Service"

**Vérifier :**
- ✅ Message d'erreur "Impossible de se connecter au serveur"
- ✅ Bouton "Réessayer"
- ✅ Pas de crash

**Résolution :**
3. Relancer le backend
4. Tap "Réessayer"
   - **Vérifier :** Catégories se chargent

---

### Test Erreur 2 : Mauvais Email Login

**Actions :**
1. LoginScreen
2. Email: `wrong@email.com`
3. Password: `anything`
4. Tap "Se connecter"

**Vérifier :**
- ✅ SnackBar rouge
- ✅ Message "Identifiants invalides"
- ✅ Reste sur LoginScreen

---

### Test Erreur 3 : Panier Vide Checkout

**Actions :**
1. CartScreen avec 0 articles
2. (Normalement impossible, mais tester)

**Vérifier :**
- ✅ Pas de bouton "Commander"
- ✅ Message "Panier vide"
- ✅ CTA "Parcourir le menu"

---

### Test Erreur 4 : Network Timeout

**Actions :**
1. Modifier timeout dans `api_service.dart` à 1ms
2. Essayer de charger catégories

**Vérifier :**
- ✅ Message d'erreur après timeout
- ✅ Bouton "Réessayer"
- ✅ Pas de crash

---

## 📱 TESTS DEVICE PHYSIQUE

### Configuration Backend

**Sur Mac :**
```bash
# Trouver l'IP
ifconfig en0 | grep inet
# Exemple: inet 192.168.1.100

# S'assurer que le firewall autorise les connexions
```

**Dans l'app :**
```dart
// lib/config/api_config.dart
static const String baseUrl = 'http://192.168.1.100:8000/api';
```

### Lancer sur Device

```bash
flutter run -d "00008140-0001284C2ED8801C"
```

**Vérifier :**
- ✅ App se lance
- ✅ API accessible (catégories se chargent)
- ✅ Login fonctionne
- ✅ Commandes passent

---

## 🎯 CHECKLIST COMPLÈTE

### Authentification
- [ ] Splash screen s'affiche
- [ ] Login avec compte valide
- [ ] Login avec compte invalide (erreur)
- [ ] Auto-login au relancement
- [ ] Token stocké sécurisé
- [ ] Profile screen accessible
- [ ] Change password fonctionne
- [ ] Logout fonctionne

### Room Service
- [ ] Liste catégories chargée
- [ ] Liste articles chargée
- [ ] Recherche fonctionne
- [ ] Détail article affiché
- [ ] Ajout au panier
- [ ] Badge panier temps réel
- [ ] Modification panier
- [ ] Suppression d'article
- [ ] Passage commande
- [ ] Confirmation affichée

### UX
- [ ] Animations fluides
- [ ] Navigation sans bug
- [ ] Pull-to-refresh
- [ ] Pagination automatique
- [ ] Messages d'erreur clairs
- [ ] Loading indicators
- [ ] Responsive design

### Performance
- [ ] Pas de lag
- [ ] Images chargent vite
- [ ] API répond < 2s
- [ ] Hot reload fonctionne
- [ ] Pas de crash

---

## 🎉 RÉSULTAT ATTENDU

À la fin de ces tests, vous devriez avoir :
- ✅ Connexion fonctionnelle
- ✅ Parcours complet Room Service
- ✅ Commande passée avec succès
- ✅ Profil accessible
- ✅ 0 bug bloquant

---

## 📸 SCREENSHOTS À PRENDRE

Pour la documentation :
1. SplashScreen
2. LoginScreen
3. Dashboard
4. CategoriesScreen
5. ItemsScreen avec recherche
6. ItemDetailScreen
7. CartScreen avec articles
8. OrderConfirmationScreen
9. ProfileScreen
10. ChangePasswordScreen

---

## 🆘 TROUBLESHOOTING

### Problème : API non accessible

**Symptôme :** "Impossible de se connecter au serveur"

**Solutions :**
1. Vérifier backend lancé : `php artisan serve`
2. Vérifier URL dans `api_config.dart`
3. Vérifier firewall (device physique)
4. Tester avec Postman : `http://localhost:8000/api/room-service/categories`

### Problème : Images ne se chargent pas

**Symptôme :** Placeholders partout

**Solutions :**
1. Vérifier URLs d'images dans l'API
2. Vérifier permissions Internet
3. Tester URL d'image dans navigateur

### Problème : Hot Reload ne fonctionne pas

**Solutions :**
1. Appuyer sur `R` (restart complet)
2. Ou `flutter run --hot`

---

**🧪 GUIDE DE TEST - PRÊT POUR VALIDER L'APPLICATION ! ✅**

**Durée estimée :** 45-60 minutes pour tous les tests

**Bonne chance ! 🚀**
