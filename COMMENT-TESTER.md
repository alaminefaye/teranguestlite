# 🧪 Comment tester l'application MAINTENANT

> **Serveur lancé :** http://localhost:8000 ✅

---

## Étape 1 : Ouvrir votre navigateur

Aller sur : **http://localhost:8000**

---

## Étape 2 : Se connecter

### Option A : Admin Hôtel (recommandé pour tester)
```
Email : admin@kingfahd.sn
Mot de passe : password
```

### Option B : Super Admin
```
Email : admin@admin.com
Mot de passe : passer123
```

---

## Étape 3 : Explorer l'application

### Si connecté en tant qu'Admin Hôtel

**Dashboard :**
- Vous verrez les statistiques de votre hôtel
- 8 chambres, X disponibles, Y occupées
- Check-ins/outs du jour
- Réservations récentes

**Menu sidebar (gauche) :**
- Dashboard
- Chambres
- Réservations
- Commandes (sous-menu - à développer)
- Services (sous-menu - à développer)
- Staff

**Actions à tester :**

1. **Chambres** (cliquer sur "Chambres" dans le menu)
   - Voir les 8 chambres existantes
   - Cliquer "Nouvelle chambre"
   - Créer une chambre (ex: 104, Simple, 80000 FCFA)
   - Sélectionner équipements (Wi-Fi, TV, etc.)
   - Uploader une image
   - Modifier une chambre existante
   - Voir détails d'une chambre
   - Essayer de supprimer une chambre

2. **Réservations** (cliquer sur "Réservations" dans le menu)
   - Voir les 3 réservations existantes
   - Cliquer "Nouvelle réservation"
   - Créer une réservation :
     - Sélectionner un client
     - Choisir dates (aujourd'hui + 5 jours par exemple)
     - Sélectionner une chambre
     - **Voir le prix se calculer en temps réel !**
   - Cliquer sur une réservation confirmée
   - Cliquer "Check-in" (la chambre devient "Occupée")
   - Cliquer "Check-out" (la chambre devient "Disponible")
   - Essayer d'annuler une réservation

3. **Filtres**
   - Filtrer les chambres par type (Suite, Deluxe, etc.)
   - Filtrer les réservations par statut
   - Rechercher une chambre par numéro
   - Rechercher une réservation par référence

---

### Si connecté en tant que Super Admin

**Dashboard Super Admin :**
- Statistiques globales de la plateforme
- 1 entreprise (King Fahd Palace Hotel)
- Nombre total d'utilisateurs

**Actions à tester :**

1. **Entreprises**
   - Voir la liste (1 entreprise)
   - Cliquer "Nouvelle entreprise"
   - Créer une deuxième entreprise (ex: Radisson Blu, Dakar)
   - Uploader un logo
   - Voir détails d'une entreprise
   - Modifier une entreprise
   - (**Ne pas supprimer King Fahd pour garder les données de test**)

2. **Vérification multi-tenant**
   - Créer une 2e entreprise
   - Se déconnecter
   - Se reconnecter avec admin@kingfahd.sn
   - **Vérifier que vous ne voyez que les chambres/réservations de King Fahd**
   - Se déconnecter
   - Se reconnecter avec admin@admin.com
   - **Vérifier que vous voyez les 2 entreprises**

---

## Étape 4 : Vérifier le multi-tenant

### Test important

1. Se connecter avec `admin@kingfahd.sn` / `password`
2. Aller sur `/dashboard/rooms`
3. **Vous devez voir uniquement les 8 chambres de King Fahd**
4. Créer une nouvelle chambre
5. Se déconnecter
6. Se connecter avec `admin@admin.com` / `passer123`
7. Créer une 2e entreprise (ex: Hotel ABC)
8. Se déconnecter
9. Se reconnecter avec `admin@kingfahd.sn`
10. **Vous ne devez toujours voir que les chambres de King Fahd** ✅

---

## 📱 Données de test disponibles

### Entreprise
- **King Fahd Palace Hotel** (Dakar, Sénégal)

### Chambres (8)
- 101, 102, 103 (Étage 1)
- 201, 202, 203 (Étage 2)
- 301, 302 (Étage 3)
- Types variés : Simple → Présidentielle
- Prix : 75,000 → 500,000 FCFA/nuit

### Réservations (3)
- Différents statuts pour tester les workflows
- Clients : Jean Dupont, Marie Martin, Pierre Bernard

---

## 🐛 Si vous rencontrez un problème

### Serveur ne répond pas
```bash
# Vérifier que le serveur tourne
# Ctrl+C pour arrêter
php artisan serve
```

### Erreur 404
```bash
# Vérifier les routes
php artisan route:list
```

### Erreur de base de données
```bash
# Réinitialiser la base
php artisan migrate:fresh --seed
```

### Erreur "enterprise_id required"
- Assurez-vous d'être connecté avec un compte qui a un enterprise_id
- Ou avec le super admin (qui bypass cette vérification)

---

## ✨ Fonctionnalités à essayer absolument

1. **Calcul prix temps réel** (création réservation)
   - Changer les dates → prix se met à jour
   - Changer la chambre → prix se met à jour

2. **Check-in / Check-out**
   - Créer une réservation confirmée
   - Faire le check-in
   - Voir que la chambre est passée à "Occupée"
   - Faire le check-out
   - Voir que la chambre est repassée à "Disponible"

3. **Filtres**
   - Filtrer chambres par type "Suite"
   - Filtrer réservations par statut "Confirmée"

4. **Multi-tenant**
   - Créer une 2e entreprise en super admin
   - Se connecter en admin d'un hôtel
   - Vérifier qu'on ne voit que ses données

---

## 🎯 Ce qu'il reste à développer

### Modules Admin (Phase 3)
- Menus & Articles (Room Service)
- Restaurants & Bars
- Services Spa
- Blanchisserie
- Services Palace
- Destination (Découvrir Dakar)
- Excursions

### Interface Guest (Phase 4)
- Layout tablette (fond bleu nuit, icônes or)
- 8 modules services
- 4 axes transversaux
- Header & Footer

### Mobile (Phase 5)
- Application mobile

---

**Bon test ! 🎉**

**L'application est prête à être explorée.**

Si tout fonctionne bien, on peut continuer avec la **Phase 3** (modules métier) ou passer à l'**interface Guest** (tablette) selon vos priorités.
