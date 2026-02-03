# 🚀 DÉPLOYER LE FIX API SUR PRODUCTION

**Fix :** MenuCategory::items() → menuItems()  
**Fichier :** RoomServiceController.php  
**Urgence :** Haute (API cassée)  
**Temps :** 2 minutes

---

## ⚡ DÉPLOIEMENT RAPIDE (2 MINUTES)

### Option 1 : Git Pull (Recommandé)

**1. Push les changements (depuis votre Mac) :**

```bash
cd /Users/Zhuanz/Desktop/projets/web/terangaguest
git push origin main
```

**2. Se connecter au serveur :**

```bash
ssh votre-utilisateur@teranguest.universaltechnologiesafrica.com
```

**3. Sur le serveur, pull les changements :**

```bash
cd /home2/sema9615/terangaguest
git pull origin main
php artisan cache:clear
php artisan config:clear
```

**4. Tester l'API :**

```bash
curl https://teranguest.universaltechnologiesafrica.com/api/room-service/categories?available=1
```

**Résultat attendu :** HTTP 200 avec liste des catégories ✅

---

### Option 2 : Modification Directe (Plus Rapide)

**1. SSH vers le serveur :**

```bash
ssh votre-utilisateur@teranguest.universaltechnologiesafrica.com
cd /home2/sema9615/terangaguest
```

**2. Éditer le fichier :**

```bash
nano app/Http/Controllers/Api/RoomServiceController.php
```

**3. Trouver et modifier ces lignes :**

**Ligne 19 - AVANT :**
```php
$query = MenuCategory::with(['items' => function($q) {
```

**Ligne 19 - APRÈS :**
```php
$query = MenuCategory::with(['menuItems' => function($q) {
```

**Ligne 21 - AVANT :**
```php
}])->withCount('items');
```

**Ligne 21 - APRÈS :**
```php
}])->withCount('menuItems');
```

**Ligne 45 - AVANT :**
```php
'items_count' => $category->items_count,
```

**Ligne 45 - APRÈS :**
```php
'items_count' => $category->menu_items_count,
```

**4. Sauvegarder :**
```
Ctrl + X
Y (Oui)
Enter
```

**5. Clear cache :**
```bash
php artisan cache:clear
php artisan config:clear
```

---

## ✅ VÉRIFIER QUE ÇA FONCTIONNE

### Test 1 : API Directement

```bash
curl https://teranguest.universaltechnologiesafrica.com/api/room-service/categories?available=1
```

**Attendu :**
```json
{
  "success": true,
  "data": [...]
}
```

**Si erreur :** Le fix n'est pas appliqué, recommencer.

### Test 2 : App Mobile Flutter

**Sur votre Mac :**

```bash
cd terangaguest_app
flutter run
# Ou R pour hot restart si déjà lancé
```

**Dans l'app :**
1. Login avec `guest1@king-fahd-palace.com` / `passer123`
2. Dashboard → Tap "Room Service"
3. **Les catégories s'affichent** ✅

---

## 🔧 SI ÇA NE FONCTIONNE PAS

### Clear Tous les Caches

```bash
cd /home2/sema9615/terangaguest
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Restart PHP-FPM

```bash
sudo systemctl restart php8.2-fpm
# Ou
sudo systemctl restart php-fpm
```

### Restart Web Server

```bash
# Nginx
sudo systemctl restart nginx

# Ou Apache
sudo systemctl restart apache2
```

### Vérifier les Logs

```bash
tail -f /home2/sema9615/terangaguest/storage/logs/laravel.log
```

---

## 📊 RÉSUMÉ

### Problème

```
API: /api/room-service/categories
Erreur: Call to undefined method MenuCategory::items()
Code: 500
Impact: App mobile ne peut pas charger Room Service
```

### Solution

```
Changer: items → menuItems (3 endroits)
Fichier: RoomServiceController.php
Déploiement: Git pull + Clear cache
Test: API + App mobile
```

### Temps Total

```
Git Push: 10s
SSH: 5s
Git Pull: 10s
Clear cache: 5s
Test: 30s
-------
Total: ~1 minute
```

---

## 🎯 CHECKLIST

- [ ] **Connexion SSH** au serveur
- [ ] **cd** dans `/home2/sema9615/terangaguest`
- [ ] **git pull** origin main (ou édition directe)
- [ ] **php artisan cache:clear**
- [ ] **Test API** avec curl
- [ ] **Test app mobile** Flutter
- [ ] **Catégories s'affichent** ✅

---

## 🎉 APRÈS LE DÉPLOIEMENT

**Une fois l'API corrigée :**

1. **Relancer l'app Flutter** (hot restart: R)
2. **Login** si nécessaire
3. **Tap Room Service** → Catégories s'affichent ✅
4. **Profiter de l'app** fonctionnelle ! 🎊

---

**DÉPLOYER MAINTENANT ! 🚀**

Le fix est simple mais l'app ne fonctionnera pas sans lui !
