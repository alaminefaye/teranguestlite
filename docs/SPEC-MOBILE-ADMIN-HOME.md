# Page d’accueil **Admin** – Application mobile Terangaguest

Objectif : définir la page que voient les administrateurs / staff quand ils se connectent dans l’app mobile, avec le **même design que l’app client** (grosses boxes de services), mais orientée **gestion des demandes et réservations**.

---

## 1. Contexte et vision

- Aujourd’hui :
  - L’app tablette est centrée sur le **client en chambre** (room service, spa, conciergerie, etc.).
  - Toute la **gestion** (commandes, réservations, états, etc.) se fait dans le **dashboard web**.
- Besoin :
  - Donner aux **administrateurs / staff** une **interface mobile** simple et tactile pour suivre et traiter :
    - toutes les **commandes** (room service, minibar, etc.),
    - toutes les **réservations** (spa, restaurants, excursions…),
    - toutes les **demandes** (blanchisserie, services palace, assistance d’urgence…),
    - et plus tard les **messages** (chat), notifications, etc.
- Contraintes UX :
  - Garder le **même style** que le front client tablette : fond bleu dégradé, accent or, grosses cartes / boxes.
  - Navigation ultra claire : **1 écran d’accueil admin** avec des **tuiles par module**.

---

## 2. Rôle cible et accès

- Rôles concernés :
  - `admin` de l’hôtel.
  - éventuellement `staff` avec droits limités.
- Comportement :
  - Après authentification dans l’app mobile, si l’utilisateur a un rôle staff/admin, il tombe sur **cette page d’accueil Admin** au lieu du menu client.
  - L’entreprise (hôtel) est connue via le compte (comme sur le dashboard web).

---

## 3. Navigation et layout

- Écran : `AdminHomeScreen` (nom à confirmer dans le code).
- Structure générale :
  - **AppBar** :
    - Titre : `Espace Administrateur` ou `Dashboard hôtel`.
    - Sous-titre : nom de l’hôtel + éventuellement date/heure.
  - **Grille de boxes** au centre :
    - Même composant que `ServiceCard` (ou variante sobre) pour garder l’identité visuelle.
    - Affichage en grille responsive (2 ou 3 colonnes selon la taille).
  - Option : un **badge** sur chaque box pour montrer le nombre de demandes / réservations en attente.

Pseudo-liste des tiles (première version) :

1. Commandes Room Service
2. Réservations Restaurants
3. Réservations Spa & Bien-être
4. Réservations Excursions & Activités
5. Demandes Blanchisserie
6. Demandes Services Palace / Conciergerie
7. Assistance & Urgence (appels médecin / sécurité)
8. Messages / Chat client (si actif)

Chaque tuile ouvre un écran spécialisé déjà existant côté web (à reproduire / adapter pour mobile) ou à créer.

---

## 4. Détail des tuiles (fonctionnel)

### 4.1 Commandes Room Service

- Accès :
  - Tuile : `Commandes Room Service`.
- Écran cible :
  - Liste des commandes par statut (`Nouvelle`, `En préparation`, `Prête`, `Livrée`, `Annulée`).
  - Filtres rapides par statut.
  - Chaque commande affiche au moins :
    - Chambre,
    - Client (quand dispo),
    - Montant,
    - Heure de création,
    - Statut actuel.
  - Détail commande :
    - Liste des items,
    - Instructions spéciales,
    - Actions de changement de statut (ex : “Confirmer”, “Marquer comme prête”, “Livrée”).

### 4.2 Réservations Restaurants

- Tuile `Réservations Restaurants`.
- Écran :
  - Liste des réservations à venir / du jour.
  - Filtres : par date, service (déjeuner, dîner), statut (confirmé, en attente, annulé).
  - Détail :
    - Client, chambre, nombre de personnes, horaire,
    - Notes,
    - Actions : confirmer / annuler / marquer comme honorée.

### 4.3 Réservations Spa & Bien-être

- Tuile `Spa & Bien-être`.
- Écran :
  - Liste des réservations spa (massages, soins, etc.).
  - Filtres par date, type de soin, thérapeute (plus tard).
  - Détail :
    - Client, chambre, type de soin, durée, horaire,
    - Actions : confirmer / replanifier / annuler.

### 4.4 Réservations Excursions & Activités

- Tuile `Excursions & Activités`.
- Écran :
  - Liste des excursions réservées.
  - Filtres : date, type d’activité, statut.
  - Détail :
    - Client, chambre, participants, point de rendez-vous,
    - Actions : confirmer / marquer comme réalisée / annuler.

### 4.5 Demandes Blanchisserie

- Tuile `Blanchisserie`.
- Écran :
  - Liste des demandes de blanchisserie.
  - Filtres : “Nouveau”, “En cours”, “Livré”.
  - Détail :
    - Chambre, type de service (pressing, lavage, etc.),
    - Détails éventuels, prix estimé,
    - Actions : prendre en charge, marquer comme prêt, livré.

### 4.6 Demandes Services Palace / Conciergerie

- Tuile `Services Palace / Conciergerie`.
- Écran :
  - Liste des demandes issues des services Palace :
    - ex : “Demander un taxi”, “Préparer une surprise”, etc.
  - Détail :
    - Service demandé, chambre, client, heure, notes,
    - Actions : accepter, marquer comme réalisé.

### 4.7 Assistance & Urgence (côté staff)

- Tuile `Assistance & Urgence`.
- Écran :
  - Liste des demandes d’assistance médecin et urgence sécurité.
  - Informations clés :
    - Chambre,
    - Client (si connu via réservation),
    - Heure de la demande,
    - Statut (nouvelle, en cours, prise en charge, clôturée).
  - Actions :
    - “Marquer comme prise en charge”,
    - “Clôturer la demande” avec éventuellement un commentaire interne.

### 4.8 Messages / Chat client (optionnel, plus tard)

- Tuile `Messages` ou `Chat client`.
- Écran :
  - Liste des conversations client ↔ hôtel.
  - Badges “non lu”.
  - Détail : fil de discussion, possibilité de répondre depuis le mobile.

---

## 5. Comportement commun des écrans de liste

Pour chaque module (commandes, réservations, demandes) :

- Filtre par **période** (au moins “Aujourd’hui / Demain / Toutes”).
- Filtre par **statut**.
- Tri par **date** (plus récent en haut).
- Cliquer sur un item → ouvre un écran de **détail** avec :
  - informations complètes,
  - boutons d’action principaux (changer de statut).
- Possibilité de **pull-to-refresh**.

---

## 6. Design et composants UI

- Palette :
  - Fond : gradient bleu (comme l’app client).
  - Accent : or.
- Composants :
  - `ServiceCard` ou variante :
    - bordure dorée,
    - icône,
    - label centré,
    - éventuellement un **badge** avec un compteur (ex : “5 en attente”).
  - Listes :
    - fond sombre,
    - cartes simples avec bordure légère et texte clair.
- Responsiveness :
  - Mobile portrait : 2 colonnes de boxes maximum.
  - Tablette : 3 colonnes possibles.

---

## 7. Données et API (haut niveau)

À réutiliser autant que possible :

- Endpoints existants (web) pour :
  - commandes room service,
  - réservations spa / restaurant / excursions,
  - demandes blanchisserie / services palace,
  - assistance & urgence (nouvelle table ou réutilisation de PalaceRequest).
- TODO technique :
  - Vérifier quelles routes API existent déjà pour le dashboard web (JSON).
  - Exposer, si besoin, des endpoints dédiés “staff mobile” (filtrés par `enterprise_id` de l’utilisateur).
  - Gérer pagination, filtres et sécurité (auth token staff/admin).

---

## 8. Ce que j’ai compris et ce que je dois faire ensuite

### Ce que j’ai compris de ta demande

- Tu veux une **nouvelle page d’accueil dans l’app mobile**, réservée aux **administrateurs / staff**, qui reprenne :
  - le **même design que la partie client** (grosses cartes / boxes),
  - mais pour gérer **tout ce qui se passe dans l’hôtel** :
    - commandes,
    - réservations,
    - demandes,
    - urgences, etc.
- Quand un admin se connecte dans l’app, au lieu de voir le menu client, il voit **cette grille de services**.
- Depuis cette page, il peut entrer dans chaque module pour **traiter les demandes** (changer les statuts, voir le détail, etc.).

### Ce que je dois faire ensuite (implémentation à venir)

1. Créer l’écran `AdminHomeScreen` côté Flutter :
   - appBar admin,
   - grille de `ServiceCard` pour chaque module.
2. Brancher la logique d’auth :
   - si `user.role` est `admin` ou `staff`, rediriger vers `AdminHomeScreen` plutôt que l’accueil client.
3. Pour chaque tuile, créer / réutiliser un écran de liste :
   - connexion aux API existantes ou nouvelles routes,
   - affichage des items avec filtres et actions.
4. Ajouter progressivement les modules (room service d’abord, puis spa/resto, etc.) en gardant la même UX.

Ce fichier sert de base pour valider la vision et les fonctionnalités avant de passer au code.

