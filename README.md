# 🏨 Teranga Guest - Système de Gestion Hôtelière SaaS

Application web et mobile multi-tenant pour la gestion complète d'hôtels et établissements d'hébergement.

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.0-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![Firebase](https://img.shields.io/badge/Firebase-Enabled-FFCA28?style=flat&logo=firebase)](https://firebase.google.com)

---

## 📋 À PROPOS

**Teranga Guest** est une solution SaaS complète de gestion hôtelière permettant à plusieurs établissements (hôtels, résidences, etc.) de gérer leurs opérations quotidiennes via une interface web moderne et une application mobile intuitive.

### ✨ Fonctionnalités Principales

- 🏢 **Multi-tenant** - Une application, plusieurs entreprises
- 👥 **Gestion des rôles** - Super Admin, Admin, Staff, Guest
- 🛏️ **Gestion des chambres** - Disponibilités, réservations, check-in/out
- 🍽️ **Room Service** - Commande de repas et articles
- 🍷 **Restaurants & Bars** - Réservations de tables
- 💆 **Spa & Bien-être** - Réservations de soins
- 👔 **Blanchisserie** - Demandes de service
- 🎯 **Services Palace** - Services premium personnalisés
- 🌴 **Excursions** - Réservations d'activités touristiques
- 🔔 **Notifications Push** - Firebase Cloud Messaging
- 📱 **Interface Tablet** - Optimisée pour tablettes en chambre

---

## 🚀 DÉMARRAGE RAPIDE

### Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & NPM
- Git

### Installation

```bash
# Cloner le repository
git clone https://github.com/alaminefaye/terangaguest.git
cd terangaguest

# Installer les dépendances PHP
composer install

# Installer les dépendances JS
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
# DB_DATABASE=teranga
# DB_USERNAME=root
# DB_PASSWORD=

# Exécuter les migrations et seeders
php artisan migrate:fresh --seed

# Compiler les assets
npm run build

# Lancer le serveur
php artisan serve
```

### Accès par Défaut

**Super Admin**
- Email: `admin@admin.com`
- Mot de passe: `passer123`

**Admin Hotel (King Fahd Palace)**
- Email: `admin@king-fahd-palace.com`
- Mot de passe: `passer123`

**Guest (Chambre de test)**
- Email: `guest@teranga.com`
- Mot de passe: `passer123`

---

## 📚 DOCUMENTATION

La documentation complète est disponible dans le dossier [`docs/`](./docs/)

### Documents Principaux

- **[docs/README.md](./docs/README.md)** - Index de la documentation
- **[docs/APPLICATION-WEB-100-COMPLETED.md](./docs/APPLICATION-WEB-100-COMPLETED.md)** - État complet de l'application web
- **[docs/FIREBASE-CONFIGURATION.md](./docs/FIREBASE-CONFIGURATION.md)** - Guide Firebase
- **[docs/PROJET-RECAP-GLOBAL.md](./docs/PROJET-RECAP-GLOBAL.md)** - Vue d'ensemble du projet

### Par Section

- **[docs/sessions/](./docs/sessions/)** - Historique des sessions de développement
- **[docs/phases/](./docs/phases/)** - Documentation par phase
- **[docs/modules/](./docs/modules/)** - Documentation des modules
- **[docs/guides/](./docs/guides/)** - Guides pratiques
- **[docs/specs/](./docs/specs/)** - Spécifications techniques

---

## 🏗️ ARCHITECTURE

### Stack Technique

**Backend**
- Laravel 11 (PHP 8.2)
- MySQL 8.0
- Laravel Sanctum (API)
- Firebase Admin SDK

**Frontend Web**
- Blade Templates
- Tailwind CSS 3
- Alpine.js
- Vite

**Mobile (En développement)**
- Flutter
- Firebase Cloud Messaging
- API REST Laravel

### Structure Multi-tenant

Chaque entreprise (hôtel) dispose de :
- Ses propres utilisateurs (admin, staff, guests)
- Ses propres données isolées
- Son propre dashboard
- Ses propres services et tarifs

Le Super Admin peut :
- Créer et gérer les entreprises
- Voir toutes les statistiques
- Gérer tous les utilisateurs

---

## 📱 MODULES DISPONIBLES

### ✅ Application Web (100% Complétée)

| Module | Statut | Description |
|--------|--------|-------------|
| Authentification | ✅ | Login, logout, changement MDP obligatoire |
| Super Admin | ✅ | Gestion entreprises et utilisateurs |
| Entreprises | ✅ | CRUD entreprises (hôtels) |
| Utilisateurs | ✅ | Gestion multi-rôles |
| Chambres | ✅ | Types, tarifs, disponibilités |
| Réservations | ✅ | Booking, check-in, check-out |
| Menus & Articles | ✅ | Catégories, prix, disponibilité |
| Commandes | ✅ | Room service, workflow statuts |
| Restaurants & Bars | ✅ | Horaires, capacité, réservations |
| Spa & Bien-être | ✅ | Services, durées, réservations |
| Blanchisserie | ✅ | Items, tarifs, demandes |
| Services Palace | ✅ | Services premium, demandes |
| Excursions | ✅ | Activités, participants, bookings |
| Interface Guest | ✅ | Hub services, commandes, réservations |
| Notifications | ✅ | Firebase configuré, prêt pour mobile |

### 🔄 API Mobile (En développement)

- Authentication endpoint
- CRUD endpoints pour toutes les entités
- FCM token management
- Documentation Swagger

### 📱 Application Mobile (Planifiée)

- Flutter iOS & Android
- Synchronisation offline
- Notifications push
- Interface intuitive

---

## 🎨 CAPTURES D'ÉCRAN

### Dashboard Super Admin
- Vue d'ensemble multi-entreprises
- Statistiques globales
- Gestion centralisée

### Dashboard Admin Hôtel
- Vue entreprise spécifique
- Gestion opérationnelle complète
- Statistiques détaillées

### Interface Guest (Tablet)
- Hub services centralisé
- Commande room service avec panier
- Réservations en un clic
- Suivi des commandes en temps réel

---

## 🔒 SÉCURITÉ

- ✅ Authentification sécurisée (Breeze + Sanctum)
- ✅ Isolation des données par entreprise
- ✅ Middleware de vérification d'appartenance
- ✅ Changement de mot de passe obligatoire à la première connexion
- ✅ Validation stricte des entrées
- ✅ Protection CSRF
- ✅ Hash des mots de passe (Bcrypt)
- ✅ Credentials Firebase sécurisés

---

## 🧪 TESTS

### Lancer les tests

```bash
# Tests unitaires
php artisan test

# Tests avec couverture
php artisan test --coverage
```

### Tests manuels

Voir le guide complet : [docs/guides/COMMENT-TESTER.md](./docs/guides/COMMENT-TESTER.md)

---

## 📊 STATISTIQUES DU PROJET

- **Contrôleurs :** 20+
- **Modèles :** 15+
- **Migrations :** 25+
- **Vues Blade :** 100+
- **Routes :** 150+
- **Lignes de code :** 15,000+

---

## 🗺️ ROADMAP

### ✅ Phase 1 - Fondations (Terminée)
- Architecture multi-tenant
- Authentification
- Super Admin

### ✅ Phase 2 - Gestion Hôtelière (Terminée)
- Chambres
- Réservations
- Menus & Commandes

### ✅ Phase 3 - Services Additionnels (Terminée)
- Restaurants & Bars
- Spa & Bien-être
- Blanchisserie
- Services Palace
- Excursions

### ✅ Phase 4 - Interface Guest (Terminée)
- Hub services
- Room service
- Réservations multiples
- Suivi temps réel

### 🔄 Phase 5 - API Mobile (En cours)
- API REST complète
- Documentation Swagger
- Tests automatisés
- Rate limiting

### 📱 Phase 6 - Application Mobile (Planifiée)
- Flutter iOS & Android
- Firebase integration
- Notifications push
- Mode offline

---

## 🤝 CONTRIBUTION

Les contributions sont les bienvenues !

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📄 LICENCE

Ce projet est sous licence privée. Tous droits réservés.

---

## 📞 CONTACT & SUPPORT

- **Repository :** [github.com/alaminefaye/terangaguest](https://github.com/alaminefaye/terangaguest)
- **Documentation :** [docs/README.md](./docs/README.md)
- **Issues :** [GitHub Issues](https://github.com/alaminefaye/terangaguest/issues)

---

## 🙏 REMERCIEMENTS

Développé avec ❤️ pour révolutionner la gestion hôtelière en Afrique.

**Technologies utilisées :**
- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [Firebase](https://firebase.google.com)
- [Flutter](https://flutter.dev) (à venir)

---

**Version :** 1.1.0  
**Date :** Février 2026  
**Statut :** ✅ Application Web 100% | 🔄 API Mobile en cours | 📱 Mobile à venir

**🚀 Teranga Guest - L'avenir de la gestion hôtelière**
