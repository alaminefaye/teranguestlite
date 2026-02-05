# CRUD « Autres services » – État et à faire

## ✅ Spa & Bien-être (terminé)

- **Dashboard** : liste avec image, boutons **Créer**, **Voir**, **Modifier**, **Supprimer**.
- **Création** : formulaire avec nom, catégorie, durée, prix, statut, ordre, en vedette, description, **upload image/icône** (max 30 Mo).
- **Modification** : même formulaire + affichage de l’image actuelle.
- **Détail** : fiche avec image, prix, durée, statut, boutons Modifier / Supprimer.
- **Mobile** : l’app Flutter consomme l’API `/api/spa-services` ; les images sont servies via `storage/` (lien symbolique `public/storage` → `storage/app/public`).

---

## ⏳ Blanchisserie (à faire)

- **Contrôleur** : `app/Http/Controllers/Dashboard/LaundryServiceController.php` – méthodes vides.
- **Modèle** : `LaundryService` (name, category, description, price, turnaround_hours, status, display_order). Pas de champ `image` aujourd’hui.
- **À prévoir** :
  1. (Optionnel) Migration : ajouter colonne `image` à `laundry_services` si besoin d’icône/image par service.
  2. Implémenter index, create, store, show, edit, update, destroy dans le contrôleur.
  3. Vues : `resources/views/pages/dashboard/laundry-services/` (index, create, edit, show) sur le même principe que Spa (liste + formulaires + upload image si colonne ajoutée).

---

## ⏳ Services Palace (à faire)

- **Contrôleur** : `app/Http/Controllers/Dashboard/PalaceServiceController.php` – méthodes vides.
- **Modèle** : `PalaceService` (name, category, description, **image**, price, price_on_request, status, is_premium, display_order).
- **À prévoir** :
  1. Implémenter le CRUD complet dans le contrôleur (validation + upload image comme Spa).
  2. Vues : `resources/views/pages/dashboard/palace-services/` (index, create, edit, show) avec upload **image/icône** comme pour Spa.

---

## ⏳ Excursions (à faire)

- **Contrôleur** : `app/Http/Controllers/Dashboard/ExcursionController.php` – méthodes vides.
- **Modèle** : `Excursion` (name, type, description, **image**, price_adult, price_child, duration_hours, departure_time, included, not_included, min/max_participants, status, is_featured, display_order).
- **À prévoir** :
  1. Implémenter le CRUD complet (validation + upload image).
  2. Vues : `resources/views/pages/dashboard/excursions/` (index, create, edit, show) avec champs spécifiques (prix adulte/enfant, durée, horaire, inclus/non inclus, etc.) et **image**.

---

## Web vs mobile

- **Dashboard web** : CRUD complet (créer, modifier, supprimer, upload image/icône) pour chaque type de service.
- **App mobile** : lecture seule via API ; les images renvoyées par l’API (URL vers `storage/`) s’affichent dans l’app. Aucun changement API nécessaire pour afficher les images une fois le CRUD et l’upload en place.

---

## Récap besoins par module

| Module        | Index | Create | Edit | Show | Delete | Image/icône |
|---------------|-------|--------|------|------|--------|-------------|
| Spa           | ✅    | ✅     | ✅   | ✅   | ✅     | ✅          |
| Blanchisserie | ❌    | ❌     | ❌   | ❌   | ❌     | Optionnel   |
| Palace        | ❌    | ❌     | ❌   | ❌   | ❌     | ✅ (déjà)   |
| Excursions    | ❌    | ❌     | ❌   | ❌   | ❌     | ✅ (déjà)   |
