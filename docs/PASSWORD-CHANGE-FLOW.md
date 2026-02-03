# Flux de changement de mot de passe obligatoire

## Vue d'ensemble

Lorsqu'une nouvelle entreprise est créée, un compte administrateur est automatiquement créé avec un mot de passe par défaut (`passer123`). Pour des raisons de sécurité, l'administrateur doit obligatoirement changer son mot de passe lors de sa première connexion.

## Fonctionnalités implémentées

### 1. Création automatique de l'administrateur

Lorsqu'une entreprise est créée par un super admin :
- Un compte administrateur est automatiquement créé
- Email : `admin@{slug-entreprise}.com`
- Mot de passe par défaut : `passer123`
- Le flag `must_change_password` est défini à `true`

**Fichier :** `app/Http/Controllers/Admin/EnterpriseController.php` (ligne 78)

### 2. Middleware de vérification

Un middleware global `EnsurePasswordChanged` intercepte toutes les requêtes web authentifiées :
- Vérifie si l'utilisateur a le flag `must_change_password = true`
- Si oui, redirige automatiquement vers la page de changement de mot de passe
- Les routes exemptées :
  - `/auth/change-password` (GET et POST)
  - `/logout` (POST)

**Fichiers :**
- Middleware : `app/Http/Middleware/EnsurePasswordChanged.php`
- Configuration : `bootstrap/app.php` (lignes 21-23)

### 3. Page de changement de mot de passe

Une page dédiée avec le même design que la page de connexion :
- Utilise le layout `fullscreen-layout`
- Design moderne et cohérent avec l'interface de connexion
- Validation stricte :
  - Vérification du mot de passe actuel
  - Nouveau mot de passe doit contenir au moins 8 caractères
  - Nouveau mot de passe doit être différent de l'ancien
  - Confirmation du nouveau mot de passe

**Fichiers :**
- Vue : `resources/views/auth/change-password.blade.php`
- Contrôleur : `app/Http/Controllers/Auth/ChangePasswordController.php`
- Routes : `routes/web.php` (lignes 19-22)

## Flux utilisateur

```
1. Super Admin crée une nouvelle entreprise
   ↓
2. Système crée automatiquement un compte admin avec must_change_password=true
   ↓
3. Super Admin reçoit les credentials (email + mot de passe par défaut)
   ↓
4. Admin d'entreprise se connecte avec les credentials par défaut
   ↓
5. Après authentification, redirection vers le dashboard
   ↓
6. Middleware intercepte et détecte must_change_password=true
   ↓
7. Redirection automatique vers /auth/change-password
   ↓
8. Admin voit la page de changement avec le même design que la page de login
   ↓
9. Admin entre :
   - Mot de passe actuel (passer123)
   - Nouveau mot de passe (min. 8 caractères)
   - Confirmation du nouveau mot de passe
   ↓
10. Soumission du formulaire
    ↓
11. Validation et mise à jour :
    - Hash du nouveau mot de passe
    - must_change_password = false
    ↓
12. Redirection vers le dashboard approprié selon le rôle
```

## Tests manuels

### Test 1 : Création d'une nouvelle entreprise

1. Se connecter en tant que Super Admin
2. Créer une nouvelle entreprise
3. Noter les credentials affichés :
   - Email : `admin@{nom-entreprise}.com`
   - Mot de passe : `passer123`

### Test 2 : Première connexion de l'admin d'entreprise

1. Se déconnecter
2. Aller sur `/signin`
3. Se connecter avec les credentials de l'admin d'entreprise
4. **Vérifier** : Redirection automatique vers `/auth/change-password`
5. **Vérifier** : La page a le même design que la page de login (layout avec image à droite)
6. **Vérifier** : Message d'avertissement affiché : "Vous devez changer votre mot de passe avant de continuer."

### Test 3 : Changement de mot de passe

1. Sur la page `/auth/change-password`
2. Entrer :
   - Mot de passe actuel : `passer123`
   - Nouveau mot de passe : `nouveau-mot-de-passe-123`
   - Confirmer : `nouveau-mot-de-passe-123`
3. Cliquer sur "Changer mon mot de passe"
4. **Vérifier** : Redirection vers le dashboard
5. **Vérifier** : Message de succès affiché
6. **Vérifier** : Plus de redirection vers la page de changement

### Test 4 : Validations

#### Test 4a : Mot de passe actuel incorrect
1. Entrer un mauvais mot de passe actuel
2. **Vérifier** : Message d'erreur "Le mot de passe actuel est incorrect."

#### Test 4b : Nouveau mot de passe identique à l'ancien
1. Entrer le même mot de passe comme nouveau
2. **Vérifier** : Message d'erreur "Le nouveau mot de passe doit être différent de l'ancien."

#### Test 4c : Nouveau mot de passe trop court
1. Entrer un mot de passe de moins de 8 caractères
2. **Vérifier** : Message d'erreur "Le mot de passe doit contenir au moins 8 caractères."

#### Test 4d : Confirmation non correspondante
1. Entrer deux mots de passe différents
2. **Vérifier** : Message d'erreur "La confirmation du mot de passe ne correspond pas."

### Test 5 : Protection des routes

1. Après connexion avec un compte nécessitant le changement de mot de passe
2. Essayer d'accéder directement à :
   - `/dashboard`
   - `/profile`
   - Toute autre page protégée
3. **Vérifier** : Redirection systématique vers `/auth/change-password`

### Test 6 : Déconnexion

1. Sur la page `/auth/change-password`
2. Cliquer sur "Retour / Se déconnecter" en haut à gauche
3. **Vérifier** : Déconnexion réussie
4. **Vérifier** : Retour à la page de login

## Base de données

### Migration concernée

Fichier : `database/migrations/2026_02_02_163443_add_must_change_password_to_users_table.php`

```sql
ALTER TABLE users ADD COLUMN must_change_password BOOLEAN DEFAULT FALSE AFTER password;
```

### Modèle User

Le champ `must_change_password` est :
- Dans les attributs `fillable`
- Casté en `boolean`
- Par défaut à `false`

## Routes

```php
// Page de changement de mot de passe (GET)
GET /auth/change-password → auth.change-password.form

// Soumission du changement (POST)
POST /auth/change-password → auth.change-password.update

// Déconnexion
POST /logout → logout
```

## Sécurité

### Points de sécurité implémentés

1. **Validation du mot de passe actuel** : L'utilisateur doit connaître son mot de passe actuel
2. **Mot de passe différent** : Le nouveau mot de passe ne peut pas être identique à l'ancien
3. **Complexité minimale** : Au moins 8 caractères requis
4. **Hachage sécurisé** : Utilisation de `Hash::make()` de Laravel
5. **Protection CSRF** : Token CSRF sur le formulaire
6. **Middleware global** : Impossible de contourner le changement de mot de passe

### Recommandations futures

1. Ajouter des règles de complexité supplémentaires :
   - Au moins une majuscule
   - Au moins un chiffre
   - Au moins un caractère spécial

2. Implémenter un historique des mots de passe :
   - Empêcher la réutilisation des X derniers mots de passe

3. Ajouter une expiration des mots de passe :
   - Forcer le changement tous les 90 jours

4. Implémenter un système de tokens temporaires :
   - Lien de réinitialisation par email au lieu d'un mot de passe par défaut

## Fichiers modifiés/créés

### Fichiers modifiés
1. `resources/views/auth/change-password.blade.php` - Design mis à jour pour correspondre à la page de login
2. `app/Http/Middleware/EnsurePasswordChanged.php` - Amélioration de la gestion des routes exemptées

### Fichiers existants (déjà en place)
1. `app/Http/Controllers/Admin/EnterpriseController.php` - Création auto de l'admin avec must_change_password=true
2. `app/Http/Controllers/Auth/ChangePasswordController.php` - Logique de changement de mot de passe
3. `app/Models/User.php` - Attribut must_change_password
4. `bootstrap/app.php` - Enregistrement du middleware global
5. `routes/web.php` - Routes de changement de mot de passe

## Conclusion

Le système de changement de mot de passe obligatoire est maintenant pleinement fonctionnel avec :
- ✅ Design cohérent avec la page de login
- ✅ Redirection automatique lors de la première connexion
- ✅ Validations strictes
- ✅ Protection des routes
- ✅ Expérience utilisateur fluide
