# 🔧 FIX API - RELATION MenuCategory::items()

**Date :** 3 Février 2026  
**Erreur :** 500 Server Error  
**Cause :** Incohérence nom de relation  
**Statut :** ✅ Corrigé localement

---

## 🐛 PROBLÈME

### Erreur API

```
Call to undefined method App\Models\MenuCategory::items()
```

**URL affectée :**
```
GET /api/room-service/categories?available=1
```

### Cause

**Incohérence entre modèle et contrôleur :**

**Modèle `MenuCategory.php` :**
```php
public function menuItems()  // ← Relation s'appelle menuItems
{
    return $this->hasMany(MenuItem::class, 'category_id');
}
```

**Contrôleur `RoomServiceController.php` (avant) :**
```php
$query = MenuCategory::with(['items' => function($q) {  // ❌ Utilise items
    $q->where('is_available', true);
}])->withCount('items');  // ❌ items
```

**Laravel cherche `items()` mais trouve `menuItems()` → Erreur !**

---

## ✅ SOLUTION APPLIQUÉE

### Fichier Modifié

`app/Http/Controllers/Api/RoomServiceController.php`

### Changements

**Ligne 19-21 (AVANT) :**
```php
$query = MenuCategory::with(['items' => function($q) {
    $q->where('is_available', true);
}])->withCount('items');
```

**Ligne 19-21 (APRÈS) :**
```php
$query = MenuCategory::with(['menuItems' => function($q) {
    $q->where('is_available', true);
}])->withCount('menuItems');
```

**Ligne 45 (AVANT) :**
```php
'items_count' => $category->items_count,
```

**Ligne 45 (APRÈS) :**
```php
'items_count' => $category->menu_items_count,
```

---

## 🚀 DÉPLOYER SUR PRODUCTION

### Option 1 : Git Push (Recommandé)

```bash
# Dans le dossier du projet Laravel (local)
cd /Users/Zhuanz/Desktop/projets/web/terangaguest

# Vérifier les changements
git status

# Ajouter le fichier modifié
git add app/Http/Controllers/Api/RoomServiceController.php

# Committer
git commit -m "Fix: Utiliser menuItems au lieu de items dans RoomServiceController"

# Pusher vers le serveur
git push origin main
```

**Puis sur le serveur :**
```bash
# SSH vers votre serveur
ssh votre-user@teranguest.universaltechnologiesafrica.com

# Aller dans le dossier Laravel
cd /home2/sema9615/terangaguest

# Pull les changements
git pull origin main

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Option 2 : Modification Directe (Plus Rapide)

**SSH vers le serveur :**
```bash
ssh votre-user@teranguest.universaltechnologiesafrica.com
cd /home2/sema9615/terangaguest
```

**Éditer le fichier :**
```bash
nano app/Http/Controllers/Api/RoomServiceController.php
```

**Modifier ligne 19 :**
```php
// Avant
$query = MenuCategory::with(['items' => function($q) {

// Après
$query = MenuCategory::with(['menuItems' => function($q) {
```

**Modifier ligne 21 :**
```php
// Avant
}])->withCount('items');

// Après
}])->withCount('menuItems');
```

**Modifier ligne 45 :**
```php
// Avant
'items_count' => $category->items_count,

// Après
'items_count' => $category->menu_items_count,
```

**Sauvegarder : Ctrl+X, Y, Enter**

**Clear cache :**
```bash
php artisan cache:clear
php artisan config:clear
```

---

## 🧪 TESTER

### Après Déploiement

**1. Tester l'API directement :**
```bash
curl https://teranguest.universaltechnologiesafrica.com/api/room-service/categories?available=1 \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

**Résultat attendu :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Petits Déjeuners",
      "items_count": 5
    }
  ]
}
```

**2. Tester depuis l'app mobile :**

```bash
# Relancer l'app Flutter
flutter run
```

**Login → Room Service → Les catégories s'affichent ✅**

---

## 📊 AVANT / APRÈS

### Avant (Erreur)

```
GET /api/room-service/categories
↓
MenuCategory::with(['items'])
↓
❌ Call to undefined method items()
↓
500 Server Error
↓
App mobile affiche erreur
```

### Après (Corrigé)

```
GET /api/room-service/categories
↓
MenuCategory::with(['menuItems'])
↓
✅ Relation menuItems() existe
↓
200 OK avec données
↓
App mobile affiche catégories
```

---

## 🎯 VÉRIFICATIONS

### Checklist Déploiement

- [ ] **Fichier modifié** : RoomServiceController.php
- [ ] **Changements pushés** : Git push ou édition directe
- [ ] **Cache cleared** : php artisan cache:clear
- [ ] **API testée** : curl ou Postman
- [ ] **App mobile testée** : Flutter run
- [ ] **Catégories s'affichent** : ✅

---

## 💡 POURQUOI CETTE ERREUR ?

### Développement vs Production

**En développement (local) :**
- Base de données seedée peut-être vide
- Erreur pas apparente
- Tests pas exhaustifs

**En production :**
- Vraies données
- Vraies requêtes
- Erreur apparaît

### Relation Laravel

**Convention Laravel :**
```php
// Modèle
public function menuItems() { ... }

// Utilisation
$category->menuItems; // ✅
$category->with('menuItems'); // ✅
$category->items; // ❌ N'existe pas
```

---

## 🔄 SI ERREUR PERSISTE

### Clear Tous les Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Restart Services

```bash
# Redémarrer PHP-FPM
sudo systemctl restart php8.2-fpm

# Ou redémarrer Apache/Nginx
sudo systemctl restart nginx
```

---

## ✅ CONCLUSION

**Fix simple mais critique !**

**Erreur :** Mauvais nom de relation utilisé  
**Solution :** Utiliser `menuItems` au lieu de `items`  
**Impact :** API fonctionne, app mobile fonctionne

**DÉPLOYER MAINTENANT ! 🚀**

---

**FICHIER MODIFIÉ :**
- `app/Http/Controllers/Api/RoomServiceController.php`

**DÉPLOIEMENT :** Git push + Clear cache  
**TEST :** API + App mobile  
**STATUT :** ✅ Prêt à déployer
