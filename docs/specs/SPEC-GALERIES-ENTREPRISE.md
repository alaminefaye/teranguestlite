# Spécification : Galeries pour les entreprises

> **Statut :** À valider avant implémentation  
> **Objectif :** Permettre aux entreprises de mettre en avant leur image (photo d’établissement) et de créer des albums de photos, visibles uniquement dans l’app mobile, dans la section **Hotel Infos** (et écrans concernés), avec une isolation stricte des données par entreprise.

---

## 1. Vue d’ensemble

- Chaque **entreprise** peut :
  - Définir une **image d’établissement** (photo principale de l’hôtel / établissement).
  - Créer des **albums** (ex. : « Piscine », « Chambres », « Restaurant », « Événements »).
  - Chaque album contient des **photos** (avec titre/description optionnels, ordre d’affichage).
- Dans l’**application mobile** :
  - Un **bloc « Galerie »** est ajouté dans **Hotel Infos** (livret d’accueil).
  - Le contenu affiché (image d’établissement + albums et photos) provient **uniquement** de l’entreprise de l’utilisateur (session tablette ou utilisateur connecté).
- **Isolation des données :** seules les données de l’entreprise courante sont visibles ; aucune donnée d’une autre entreprise ne doit être exposée (API et app scopées par `enterprise_id`).

---

## 2. Backend (Laravel)

### 2.1 Image d’établissement

- **Modèle :** `Enterprise` (existant).
- **Champs existants :** `logo`, `cover_photo` déjà présents.
- **Option A :** Réutiliser `cover_photo` comme « image d’établissement » pour la galerie (affichée en tête dans Hotel Infos).
- **Option B :** Ajouter un champ dédié `establishment_photo` (ou stocker dans `settings['gallery']` une clé `main_image_path`).
- **Dashboard :** Dans la section **Hotel Infos & Sécurité** (ou une sous-section **Galerie**), permettre d’uploader / modifier cette image.

### 2.2 Albums et photos

- **Nouveaux modèles (suggérés) :**
  - **`EnterpriseGalleryAlbum`**  
    - `id`, `enterprise_id`, `name`, `description` (nullable), `display_order`, `is_active`, `timestamps`.
  - **`EnterpriseGalleryPhoto`**  
    - `id`, `enterprise_gallery_album_id`, `path` (stockage fichier), `title` (nullable), `description` (nullable), `display_order`, `timestamps`.
- **Relations :**
  - `Enterprise` → `hasMany(EnterpriseGalleryAlbum::class)`.
  - `EnterpriseGalleryAlbum` → `belongsTo(Enterprise::class)`, `hasMany(EnterpriseGalleryPhoto::class)`.
  - `EnterpriseGalleryPhoto` → `belongsTo(EnterpriseGalleryAlbum::class)`.
- **Isolation :** Tous les modèles sont scopés par `enterprise_id` (trait `EnterpriseScopeTrait` ou scope global). Les contrôleurs et API ne renvoient que les albums/photos de l’entreprise courante.

### 2.3 Dashboard (web)

- **Menu / entrée :** Sous **Hotel Infos & Sécurité** (ou rubrique dédiée « Galerie »), accès à :
  - **Image d’établissement :** upload / remplacement (et optionnellement réutilisation du logo si besoin).
  - **Albums :** liste des albums (CRUD : créer, modifier, supprimer, réordonner).
  - **Photos par album :** pour chaque album, gestion des photos (ajout, ordre, titre/description, suppression).
- **Routes (exemples) :**
  - `GET/PUT  dashboard/enterprise-gallery` ou `dashboard/hotel-infos-security` (étendre la page existante avec un onglet « Galerie »).
  - `resource dashboard/gallery-albums` (index, create, store, edit, update, destroy).
  - `resource dashboard/gallery-albums.{album}.photos` (store, update, destroy pour les photos).
- **Fichiers :** Stockage sur disque (ex. `storage/app/public/enterprise-gallery/{enterprise_id}/...`) avec lien public via `asset('storage/...')`.
- **Taille maximale des images :** **20 Mo** par fichier (image d’établissement et photos d’albums). À faire respecter en validation côté backend (ex. règle `max:20480` en Ko, soit 20 Mo).

### 2.4 API (app mobile)

- **Endpoints (scopés par entreprise / session) :**
  - **Tablette (session invité) :** l’entreprise est connue via la chambre / le code invité → on retourne la galerie de cette entreprise.
  - **Utilisateur connecté (staff / guest) :** l’entreprise est celle de l’utilisateur → même principe.
- **Contenu à exposer :**
  - **Image d’établissement :** URL (ex. `cover_photo` ou `establishment_photo`).
  - **Albums :** liste des albums actifs, avec pour chaque album la liste des photos (URL, titre, description, ordre).
- **Exemples de routes API :**
  - `GET /api/tablet/hotel-infos` : étendre la réponse existante avec un bloc `gallery` (image d’établissement + albums avec photos).
  - Ou : `GET /api/enterprise-gallery` (ou inclus dans la réponse « hotel infos ») pour l’app quand l’utilisateur est connecté (auth).
- **Sécurité :** Vérifier que chaque requête est bien associée à une seule `enterprise_id` et que les réponses ne contiennent que les données de cette entreprise.

---

## 3. Application mobile (Flutter)

### 3.1 Données

- **Modèle(s) :** Adapter ou créer des modèles pour :
  - Image d’établissement (URL).
  - Album : `id`, `name`, `description`, `photos` (liste).
  - Photo : `id`, `url`, `title`, `description`.
- Le bloc « Galerie » dans **Hotel Infos** ne doit afficher que les données reçues pour l’entreprise courante (déjà garanties par l’API).

### 3.2 Interface

- **Hotel Infos (livret d’accueil) :**
  - Ajouter une **section « Galerie »** (ou « Notre établissement ») :
    - En tête : **image d’établissement** (grande image ou bannière) si présente.
    - Ensuite : **liste des albums** (cartes ou listes avec titre, éventuellement première photo en miniature).
    - Au tap sur un album : navigation vers un écran (ou bottom sheet) listant les **photos de l’album** (galerie / grille, avec titre/description si fournis).
- **Écrans concernés :** Le contenu « Galerie » est centralisé dans **Hotel Infos**. Si besoin ultérieur (ex. écran dédié « Galerie » dans le menu), les mêmes données API peuvent être réutilisées.
- **Cas sans contenu :** Si l’entreprise n’a ni image d’établissement ni albums, la section Galerie peut être masquée ou afficher un message discret (« Galerie à venir »).

---

## 4. Récapitulatif des livrables

| Zone | Livrable |
|------|----------|
| **Backend** | Modèles `EnterpriseGalleryAlbum`, `EnterpriseGalleryPhoto` ; migrations ; scope `enterprise_id` ; contrôleurs dashboard (CRUD albums + photos) ; extension réglages « image d’établissement » (ou utilisation `cover_photo`). |
| **API** | Réponse « hotel infos » (tablette + auth) enrichie avec bloc `gallery` (image d’établissement + albums avec photos), scopée par entreprise. |
| **Dashboard** | Page / onglet Galerie sous Hotel Infos : image d’établissement, gestion albums, gestion photos par album. |
| **App mobile** | Bloc Galerie dans l’écran **Hotel Infos** (image d’établissement + liste albums → détail photos par album) ; modèles et appels API associés. |

---

## 5. Isolation et sécurité

- Toutes les requêtes (dashboard et API) doivent être filtrées par `enterprise_id` (utilisateur connecté ou session tablette).
- Aucune URL d’image ou donnée d’album/photo d’une autre entreprise ne doit être retournée.
- Validation des uploads (types de fichiers, **taille max 20 Mo par image**) côté backend.

---

## 6. À valider avant de commencer

1. **Image d’établissement :** Réutiliser `Enterprise.cover_photo` ou ajouter un champ dédié (ou clé dans `settings`) ?
2. **Emplacement dashboard :** Onglet « Galerie » dans la page existante **Hotel Infos & Sécurité** ou page séparée « Galerie » dans le menu ?
3. **« Écritures » :** Confirmer que l’affichage Galerie est bien uniquement dans **Hotel Infos** (et éventuellement un écran dédié « Galerie » plus tard), ou préciser quels autres écrans doivent afficher la galerie.
4. **Ordre d’implémentation souhaité :** Backend + API d’abord, puis dashboard, puis app mobile — ou autre ordre.

Une fois ce document validé, l’implémentation pourra suivre cette spec.
