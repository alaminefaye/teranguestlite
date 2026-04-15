# TerangaGuest Lite — Structure modules (Mobile vitrine) & Management Web

## Objectif
Créer une version **vitrine / information client** (Lite) qui conserve le **design** de l’app, mais restructure la **navigation par modules (boxes)**, et supprime toute fonctionnalité **transactionnelle**.

Le web “management” doit afficher **uniquement** les menus nécessaires pour gérer le contenu des modules Lite.

## Scope (d’après le PDF fourni)
### Inclus
- Consultation d’informations
- Navigation simple par modules
- Accès rapide aux services (au sens “infos”)

### Exclu (désactivé partout)
- Réservation
- Commande
- Paiement
- Room service (commande)
- Envoi de demandes (conciergerie / coach / tennis / etc.)

## Navigation cible (modules)
Menu principal (boxes) — 8 modules :
1. Hôtel
2. Restaurants
3. Bars
4. Animations
5. Activités
6. Séminaires
7. Chambre
8. Contact

## Règle UX générale (Lite)
- Un tap sur une box doit **toujours** mener à une page **liste / détail** (lecture seule).
- Aucun écran ne doit proposer : bouton “Réserver”, “Commander”, “Envoyer la demande”, “Checkout”, etc.
- Si une donnée n’existe pas côté admin, l’écran doit afficher un état vide propre (pas de spinner infini).

## Mapping module → écrans Mobile (existant) + ajustements
### 1) Hôtel
**Contenu attendu (PDF)** : présentation, Wi‑Fi, plan, règlement, contacts hôtel.
- Source backend : `Enterprise.settings['hotel_infos']` + `Enterprise.address/phone/email`.
- Écran existant : `HotelInfosScreen` (infos + plan + pratiques).
- Ajustement Lite : vérifier que les infos se chargent en vitrine via `/api/vitrine/enterprise` (déjà en place).

### 2) Restaurants
**Contenu attendu (PDF)** : liste restaurants, horaires, description, images. Pas de réservation.
- Source backend : `Restaurant` (type = `restaurant`, horaires, image, description).
- Écran existant : liste restaurants + détail (réservation déjà retirée côté vitrine).
- Ajustements :
  - filtrer `type=restaurant` (client-side ou via query `?type=restaurant`).
  - garantir que le détail n’affiche aucun bouton de réservation.

### 3) Bars
**Contenu attendu (PDF)** : liste bars, horaires, carte boissons (PDF/image).
- Source backend existante : `Restaurant` avec `type=bar/pool_bar/cafe`.
- Écran existant : réutiliser la liste restaurants, filtrée sur les bars.
- Donnée manquante potentielle : “carte boissons” (PDF/image) — à ajouter côté modèle/admin si nécessaire.

### 4) Animations
**Contenu attendu (PDF)** : programme, journal, liste activités, events.
- Source backend existante la plus proche : `Announcements` (annonces/vidéos) + éventuellement `LeisureCategory` (activités).
- Écran cible : une page “Animations” qui affiche :
  - événements / annonces (lecture seule)
  - éventuellement catégories/activités (lecture seule)
- Données manquantes potentielles : “journal”/“programme” structurés (à modéliser si besoin).

### 5) Activités
**Contenu attendu (PDF)** : sport, piscine, spa, loisirs, excursions (info seulement).
- Sources backend existantes :
  - Spa: `SpaService` (lecture seule)
  - Loisirs/Sport: `LeisureCategory`
  - Excursions: `Excursion`
- Écrans existants :
  - Spa list/detail (réservation supprimée)
  - Leisure screens (certains écrans affichaient encore “demande”, à éliminer en Lite)
  - Excursions list/detail (réservation supprimée)
- Ajustements Lite :
  - retirer tout wording “réservation” dans les sous-titres
  - retirer les formulaires/demandes (tennis/golf/coach/etc.) en Lite

### 6) Séminaires
**Contenu attendu (PDF)** : salles, capacités, équipements, contact.
- Constat : pas de module “séminaires” dédié identifié dans le backend actuel.
- Options d’implémentation (à valider) :
  - (A) Nouveau module “SeminarRooms” (CRUD + vitrine)
  - (B) Réutiliser `Establishments` si ce contenu représente les salles (peu probable)
  - (C) Stocker une page “Séminaires” dans `Enterprise.settings` (texte + images)

### 7) Chambre
**Contenu attendu (PDF)** : boutons appel direct (réception/sécurité/concierge), guide utilisation équipements, numéros utiles, infos pratiques.
- Sources existantes :
  - `Guides` (catégories/items) pour les infos et numéros utiles
  - `Hotel Infos` pour infos pratiques
  - Écrans existants “services chambre/logistique” à transformer en lecture seule (si nécessaire)
- Ajustement Lite :
  - ne pas proposer d’envoi de demande
  - uniquement afficher les numéros / infos et éventuellement permettre “appeler” (téléphone) ou “ouvrir map”.

### 8) Contact
**Contenu attendu (PDF)** : contacts directs.
- Source backend : `Enterprise.address/phone/email` + éventuellement liens (map/site web) dans `settings`.
- Écran cible : page contact lecture seule (tel/mail/map).

## Management Web (Admin Hôtel) — menus à conserver vs retirer
### À conserver (gestion de contenu Lite)
- Entreprise : logo + cover photo (super admin)
- Hotel Infos & Sécurité (wifi/plan/règlement/pratiques + chatbot_url si utile)
- Restaurants (CRUD)
- Excursions (CRUD)
- Spa services (CRUD)
- Loisirs / catégories (CRUD)
- Annonces & Vidéos (Animations)
- Galerie (si utilisée par module Hôtel)
- (Option) Amenities categories (si utilisé en info-only; sinon à retirer)

### À retirer/masquer en Lite (transactionnel / opérationnel)
- Réservations & demandes (toutes)
- Commandes
- Facturation
- Stocks
- Staff / Tablettes
- Rapports / Avis clients
- Tout écran qui existe uniquement pour traiter des demandes/commandes

## Paramètres d’environnement (proposés)
- `VITRINE_ENTERPRISE_ID` (déjà ajouté au `.env.example`) : fixe l’entreprise affichée par la vitrine.
- (Proposé) `TERANGUEST_LITE=true` : permet d’activer la sidebar “Lite” côté web admin sans casser la version complète.

## Plan de réalisation (étapes sans bug)
1) Geler la structure 8 modules (mobile) + mapping.
2) Implémenter la nouvelle page dashboard mobile (boxes) sans changer le design.
3) En Lite, neutraliser toutes actions transactionnelles (et supprimer les wording “réservation/commande”).
4) Simplifier le menu web admin (via flag) pour ne garder que la gestion du contenu Lite.
5) Traiter les modules manquants (Bars: carte, Animations: programme/journal, Séminaires: modèle ou settings).
6) Vérifications : `flutter analyze`, `flutter test`, `php -l`, smoke routes web.

## Ce que j’ai compris / décision actuelle
- La version Lite doit être **100% vitrine** : uniquement consultation (liste + détail).
- Les “boxes” doivent correspondre exactement aux **8 modules** du PDF.
- Le web admin doit servir uniquement à **alimenter le contenu** des modules Lite (pas d’ops).

