# Spécification : Annonces et publicités (vidéos / affiches)

> **Statut :** À valider ensemble avant développement  
> **Objectif :** Permettre de diffuser des annonces publicitaires (affiche image + vidéo) dans l’application mobile, avec deux niveaux (super admin universel + annonces par entreprise), affichage en popup, et lecture en boucle sur l’écran de veille.

---

## 1. Vue d’ensemble

- **Contenu d’une annonce :**
  - Une **affiche** (image) et/ou une **vidéo**.
  - Au moins un des deux doit être présent (affiche seule, vidéo seule, ou les deux).
- **Affichage :** sous forme de **popup** (overlay) dans l’app, avec **fermeture discrète** : petit bouton (position et taille définies, ex. coin haut droit) et **tap à l’extérieur du popup = fermeture**.
- **Comportement vidéo :** quand la lecture de la vidéo est **terminée**, le popup se ferme automatiquement (disparition de l’annonce). **Son (audio) : désactivé par défaut** (vidéo en muet) ; l’utilisateur peut **activer le son** s’il le souhaite (bouton ou contrôle pour activer l’audio).
- **Comportement affiche (image) seule :** si l’annonce est une **image sans vidéo**, elle s’affiche pendant une **durée en minutes configurable par annonce** (paramètre « durée d’affichage »). **Par défaut : 1 minute.** Le client (super admin ou entreprise) peut modifier cette durée pour chaque annonce dans le dashboard.
- **Box Annonces dans l’app :** sur l’écran **Hotel Infos & Sécurité**, un **encadré / box « Annonces »** affiche les annonces destinées à cet utilisateur (celles qu’il est censé voir). L’utilisateur peut les consulter directement depuis cet écran.
- **Statistiques :** enregistrer le **nombre de vues** (affichages) par annonce. Dans le dashboard (super admin et entreprise), afficher ces statistiques pour chaque annonce (ex. « X vues »).
- **Notifications :** si une annonce est en cours et qu’une **notification** arrive (ex. nouveau message, commande), on **met la vidéo en pause** (si c’est une vidéo) et on **affiche le message de la notification** ; l’utilisateur peut traiter la notification puis reprendre ou fermer l’annonce.
- **Deux niveaux d’annonces :**
  1. **Super admin (universel) :** le super admin peut créer des annonces et choisir **pour quelles entreprises** elles s’affichent (ciblage par liste d’entreprises).
  2. **Entreprise :** chaque entreprise peut créer et gérer **ses propres** annonces (affichées uniquement aux utilisateurs de cette entreprise).
- **Tablette inactive :** quand la tablette **reste inactive** (on ne l’utilise pas), c’est à ce moment qu’on affiche les annonces (écran de veille, séquence).
- **Moments d’affichage :** au lancement si besoin, et surtout sur l’**écran de veille** quand l’app n’est pas utilisée.
- **Écran de veille (idle) :** quand la **tablette reste inactive** (pas utilisée), afficher **toutes les annonces en séquence** ; à la fin de la dernière, **recommencer depuis la première** (boucle infinie) jusqu’à ce que l’utilisateur reprenne l’app. **Pas** d’annonces pendant une utilisation active.

---

## 2. Comportement du popup annonce

| Règle | Détail |
|-------|--------|
| **Fermeture manuelle** | **Validé** : (1) Bouton de fermeture **discret** : position définie (ex. **coin haut droit**), **taille petite** (icône croix). (2) **Tap à l’extérieur du popup = fermeture** du popup. L’utilisateur peut ainsi fermer à tout moment. |
| **Fin de vidéo** | Si l’annonce contient une vidéo et que celle-ci est lue jusqu’à la fin → **fermeture automatique** du popup (l’annonce disparaît). |
| **Audio vidéo** | **Validé** : le **son est désactivé par défaut** (vidéo en muet). L’utilisateur a la **possibilité d’activer le son** (bouton ou icône « activer l’audio »). |
| **Affiche seule (image)** | Si l’annonce est une **affiche sans vidéo** : affichage pendant la **durée configurée** pour cette annonce (en minutes), puis **disparition automatique**. **Par défaut : 1 minute** ; le client peut changer cette valeur par annonce dans le dashboard. Fermeture manuelle possible à tout moment (bouton discret). |
| **Pause + notification** | **Validé** : pendant qu’une annonce est affichée, si une **notification** arrive → **mettre en pause la vidéo** (si c’est une vidéo) et **afficher le message de la notification**. L’utilisateur peut traiter la notification ; ensuite il peut reprendre la vidéo ou fermer l’annonce. |

---

## 3. Niveaux d’annonces et ciblage

### 3.1 Super admin (annonces universelles)

- Le **super admin** peut créer des annonces (affiche + vidéo optionnelles).
- Pour chaque annonce, il peut **définir la liste des entreprises** concernées :
  - Soit « toutes les entreprises » (annonce globale).
  - Soit une sélection d’entreprises → l’annonce s’affiche **uniquement** dans l’app pour les utilisateurs (tablette / client) de ces entreprises.
- **Ordre d’affichage :** les annonces super admin et les annonces entreprise sont **mélangées** (pas de priorité stricte ; ordre commun pour la séquence).

### 3.2 Entreprise (annonces propres)

- Chaque **entreprise** a accès à un espace pour gérer **ses propres** annonces (affiche et/ou vidéo).
- Ces annonces s’affichent **uniquement** pour les utilisateurs dont la session / le compte est lié à cette entreprise.
- Pas d’accès aux annonces d’autres entreprises.

---

## 4. Contenu d’une annonce (données)

- **Affiche (image) :** fichier image (URL après upload), optionnel.
- **Vidéo :** fichier vidéo (URL après upload), optionnel. En lecture : **audio désactivé par défaut** ; l’utilisateur peut activer le son.
- Au moins **un des deux** (affiche ou vidéo) obligatoire.
- Métadonnées utiles : titre (optionnel), ordre d’affichage, dates de début/fin de diffusion, actif/inactif.
- **Durée d’affichage (affiche seule) :** champ **durée en minutes** (entier), utilisé quand l’annonce est une affiche sans vidéo (ou pour la partie affiche en écran de veille). **Valeur par défaut : 1** (une minute). Le client (super admin ou entreprise) peut définir une valeur différente **pour chaque annonce** (ex. 2 min, 3 min).
- **Ciblage (super admin uniquement) :** liste des `enterprise_id` (ou « toutes »).

---

## 5. Moments d’affichage des annonces

À valider ensemble :

| Moment | Description |
|--------|-------------|
| **Au démarrage / après connexion** | Au lancement de l’app ou après identification (tablette ou user), afficher une annonce (ou la première d’une file) une fois par session ou selon fréquence. |
| **Après inactivité (avant écran de veille)** | Option : après X minutes sans interaction, afficher une annonce avant de passer en écran de veille. |
| **Écran de veille (idle)** | Quand l’app est **en mode veille** (écran de veille affiché) : **enchaînement des annonces** (vidéos / affiches) en boucle ou en séquence. Dès qu’une annonce se termine (fin de vidéo ou timeout affiche), passer à la **suivante**. Ne pas afficher d’annonces **pendant** que l’utilisateur utilise l’app (pas d’interruption en plein usage). |
| **Autre** | Autres déclencheurs possibles (ex. retour sur l’accueil après une longue absence) à préciser. |

Règle importante : **pas d’annonces quand l’utilisateur est en train d’utiliser l’app** (sauf éventuellement une au démarrage). Les annonces en séquence concernent **uniquement** le mode veille (ou les moments définis comme « non actifs »).

---

## 6. Écran de veille et séquence d’annonces

- Quand l’application passe en **mode veille** (idle) :
  - L’écran de veille s’affiche (existant ou dédié).
  - On lance la lecture des **annonces éligibles** (super admin + entreprise, selon ciblage et ordre).
- **Boucle :** annonce 1 → à la fin → annonce 2 → … → à la fin de la **dernière** → **recommencer à la première** (boucle infinie tant que la tablette reste inactive).
- **Vidéo :** lecture jusqu’à la fin puis passage à l’annonce suivante.
- **Affiche seule :** affichage pendant la **durée configurée** pour l’annonce (en minutes ; par défaut 1 min), puis passage à la suivante.
- **Notification pendant la lecture :** **mettre en pause** la vidéo, **afficher le message** de la notification ; après traitement, l’utilisateur peut reprendre la vidéo ou fermer l’annonce.

---

## 7. Backend (à développer)

### 7.1 Modèles

- **Annonce (universel ou entreprise) :**
  - `id`, `enterprise_id` (nullable pour super admin : si null = annonce super admin ; si renseigné = annonce entreprise).
  - `poster_path` (affiche, nullable), `video_path` (nullable).
  - `title` (nullable), `display_order`, `is_active`, `starts_at`, `ends_at` (nullable).
  - **`display_duration_minutes`** (entier, nullable) : durée d’affichage en **minutes** pour les affiches (quand pas de vidéo ou pour la partie affiche). **Par défaut : 1.** Si null ou non renseigné, l’app utilise 1 minute.
  - **`view_count`** (entier, défaut 0) : **nombre de vues** (affichages) de l’annonce — incrémenté quand l’app signale qu’une annonce a été affichée (popup ou écran de veille).
  - `created_by` (user_id), `timestamps`.
- **Ciblage super admin :**
  - Table pivot ou JSON : pour chaque annonce « super admin », liste des `enterprise_id` où elle est diffusée (ou flag « toutes entreprises »).

### 7.2 Dashboard

- **Super admin :**
  - CRUD annonces (affiche + vidéo), choix des entreprises cibles (ou « toutes »), ordre, dates, actif, **durée d’affichage (minutes)** par annonce (par défaut 1 min). **Affichage des statistiques** : nombre de vues par annonce (ex. colonne « Vues » ou bloc récap dans la fiche).
- **Entreprise :**
  - CRUD annonces **de l’entreprise** uniquement (affiche + vidéo), ordre, dates, actif, **durée d’affichage (minutes)** par annonce (par défaut 1 min). **Affichage des statistiques** : nombre de vues par annonce pour les annonces de l’entreprise.
  - Pas d’accès aux annonces super admin ni aux autres entreprises.

### 7.3 API (app mobile)

- **Endpoint(s) :** ex. `GET /api/announcements` ou inclus dans une réponse existante (ex. config / session).
- **Réponse :** liste des annonces **éligibles** pour l’entreprise courante (session tablette ou user), **mélangées** (annonces super admin + annonces entreprise dans un même ordre, pas de priorité stricte) :
  - Annonces super admin ciblant cette entreprise (ou « toutes »).
  - Annonces de l’entreprise.
- Chaque annonce : URLs affiche et vidéo, ordre, type (affiche seule / vidéo seule / les deux), **`display_duration_minutes`** (pour savoir combien de minutes afficher l’affiche ; par défaut 1 si absent).
- **Sécurité :** isolation stricte par entreprise ; pas d’exposition des annonces d’autres entreprises.

### 7.3bis Enregistrement des vues (statistiques)

- **Endpoint :** ex. `POST /api/announcements/{id}/view` (ou `POST /api/announcements/view` avec `announcement_id` dans le body). Appelé par l’app lorsqu’une annonce est **affichée** (popup ou écran de veille).
- **Comportement :** incrémenter le compteur de vues (`view_count`) de l’annonce concernée. Vérifier que l’annonce est bien éligible pour l’entreprise de l’utilisateur/session qui envoie la requête (sinon ignorer ou 403).
- **Option (léger anti-spam) :** limiter à une vue par annonce par session/appareil sur une courte période (ex. 1 vue par annonce toutes les 5 min par device) pour éviter de gonfler les stats en boucle veille ; ou compter chaque affichage sans limite (selon choix).

### 7.4 Stockage des fichiers

- Affiches et vidéos : stockage (ex. `storage/app/public/announcements/...`) avec URLs publiques ou signées.
- **Taille max des fichiers : 20 Mo** pour les affiches (images) et pour les vidéos. À faire respecter en validation côté backend (ex. règle `max:20480` en Ko, soit 20 Mo).
- **Formats acceptés – maximum de compatibilité :**
  - **Vidéo :** accepter plusieurs formats (ex. **mp4, webm, mov, ogv**) pour compatibilité mobile et web.
  - **Image (affiches) :** accepter **JPEG, PNG, WebP, GIF** pour compatibilité large.

---

## 8. Application mobile (Flutter) – à développer

### 8.1 Récupération des annonces

- Au chargement (ou au passage en veille), appeler l’API pour récupérer la liste des annonces éligibles.
- Mettre en cache si besoin (durée de vie courte pour refléter les mises à jour).

### 8.1bis Statistiques (envoi des vues)

- Lorsqu’une annonce est **affichée** (popup ouverture, ou passage à cette annonce sur l’écran de veille), appeler l’API d’enregistrement de vue (ex. `POST /api/announcements/{id}/view`) pour incrémenter le compteur de vues côté backend.
- Ne pas bloquer l’affichage si l’appel échoue (envoi en arrière-plan ou fire-and-forget).

### 8.2 Popup annonce

- **Widget overlay** (dialog ou fullscreen selon choix) :
  - Affiche (image) si présente.
  - **Vidéo** : lecteur avec contrôle de fin de lecture ; **son désactivé par défaut (muted)** ; **bouton ou contrôle pour activer l’audio** si l’utilisateur le souhaite.
  - **Fermeture :** (1) Bouton **discret** en **coin haut droit**, **petite taille** (icône croix). (2) **Tap à l’extérieur du popup** → fermeture du popup (`barrierDismissible: true`).
- **Fin de vidéo :** listener sur la fin de lecture → fermeture automatique du popup.
- **Affiche seule :** affichage pendant la **durée configurée** pour l’annonce (champ `display_duration_minutes` ; par défaut 1 minute), puis fermeture automatique ; fermeture manuelle possible à tout moment (bouton discret).

### 8.3 Interaction avec les notifications

- **Validé** : lorsqu’une **notification** (push ou in-app) arrive pendant une annonce :
  - **Mettre en pause** la vidéo (si c’est une vidéo en lecture).
  - **Afficher le message** de la notification (popup ou banner comme aujourd’hui).
  - Après traitement de la notification par l’utilisateur : il peut **reprendre la vidéo** ou fermer l’annonce.

### 8.4 Box Annonces sur l’écran Hotel Infos & Sécurité

- Sur l’écran **Hotel Infos & Sécurité** (hub avec Hôtel Infos, Assistance & Urgence, Chatbot, Galerie), ajouter un **encadré / box « Annonces »**.
- Ce box affiche les **annonces éligibles** pour l’utilisateur connecté (session tablette ou compte) : annonces super admin ciblant son entreprise + annonces de son entreprise.
- **Contenu du box :** liste ou carrousel des annonces (affiche et/ou vidéo) que l’utilisateur est censé voir. Au tap sur une annonce, on peut ouvrir le détail (popup avec affiche/vidéo) ou lancer la lecture.
- **Emplacement :** dans la même page, par exemple sous les 4 cartes (Hôtel Infos, Assistance & Urgence, Chatbot, Galerie), ou en encart dédié visible sans quitter l’écran. L’objectif est que l’utilisateur voie clairement « ses » annonces à cet endroit.
- Si aucune annonce n’est disponible pour cet utilisateur, le box peut être masqué ou afficher un message discret (ex. « Aucune annonce pour le moment »).

### 8.5 Écran de veille (idle)

- Détecter l’entrée en mode veille (déjà existant dans l’app).
- Quand la **tablette reste inactive** : afficher **toutes les annonces en séquence** (une après l’autre).
  - Vidéo : lecture jusqu’à la fin (son **muet par défaut**, possibilité d’activer l’audio) → annonce suivante.
  - Affiche : affichage pendant la **durée en minutes** configurée pour l’annonce (par défaut 1 min) → annonce suivante.
- **Boucle infinie** : à la fin de la **dernière** annonce, **recommencer à la première** ; et ainsi de suite tant que la tablette reste inactive.
- **Ne pas** lancer d’annonces quand l’utilisateur utilise l’app (uniquement en veille).

### 8.6 Moments d’affichage (hors veille)

- À brancher selon les choix validés : au démarrage (une fois par session), après inactivité, etc.
- Une seule annonce à la fois ; pas d’empilement de popups.

---

## 9. Points à valider ensemble

1. **Fermeture discrète :** **validé** → bouton discret (position **coin haut droit**, **petite taille**). **Tap à l’extérieur du popup = fermeture**.
2. **Affiche seule :** **validé** → durée **dynamique par annonce** (en minutes), **par défaut 1 minute** ; le client peut la modifier pour chaque annonce dans le dashboard ; fermeture manuelle possible à tout moment.
3. **Notification pendant annonce :** **validé** → **mettre en pause la vidéo** et **afficher le message** de la notification ; après traitement, l’utilisateur peut reprendre la vidéo ou fermer l’annonce.
4. **Ordre de priorité :** **validé** → **mélange** entre annonces super admin et annonces entreprise (pas de priorité stricte ; une seule liste mélangée pour l’affichage).
5. **Moments d’affichage :** confirmer la liste (démarrage, veille, autre) et fréquence (une fois par session, à chaque retour en veille, etc.).
6. **Écran de veille :** durée d’affichage des affiches = **celle configurée par annonce** (par défaut 1 min). **Boucle : validé** → **boucle infinie** : quand toutes les annonces sont passées, recommencer depuis la première tant que la tablette reste inactive.
7. **Taille max des fichiers :** **validé** → **20 Mo** pour les affiches (images) et pour les vidéos.
8. **Formats :** **validé** → **maximum de compatibilité** : vidéo = **mp4, webm, mov, ogv** ; images = **JPEG, PNG, WebP, GIF**.
9. **Audio vidéo :** **validé** → **désactivé par défaut** (muet) ; **possibilité d’activer le son** pour l’utilisateur (bouton ou contrôle).

---

## 10. Résumé des livrables prévus

| Composant | Détail |
|-----------|--------|
| **Backend** | Modèles annonces + ciblage super admin, **champ `view_count`** (statistiques), CRUD dashboard (super admin + entreprise) **avec affichage du nombre de vues par annonce**, API liste annonces + **API enregistrement vue** (`POST .../view`), stockage affiches/vidéos. |
| **App Flutter** | **Box Annonces** sur l’écran Hotel Infos & Sécurité. Popup annonce (affiche + vidéo ; fermeture discrète + tap extérieur ; vidéo → fermeture auto en fin, **son muet par défaut**, possibilité d’activer l’audio ; affiche seule → durée configurable par annonce). **Notification** : pause vidéo + affichage du message ; reprise ou fermeture possible. **Écran de veille** : annonces en séquence, **boucle infinie** (à la fin, recommencer) tant que la tablette reste inactive. **Statistiques** : envoi d’une « vue » à l’API quand une annonce est affichée (popup ou veille). |

Une fois ce document validé (et les points § 9 tranchés), le développement pourra suivre cette spec.

---

## 11. À valider / à préciser (avant ou pendant le dev)

| Sujet | Statut | Décision à prendre |
|-------|--------|--------------------|
| **Fermeture discrète** | **Validé** | Bouton discret (coin haut droit, petite taille). Tap à l’extérieur du popup = fermeture. |
| **Notification pendant annonce** | **Validé** | Mettre en pause la vidéo et afficher le message de la notification ; après traitement, l’utilisateur peut reprendre la vidéo ou fermer l’annonce. |
| **Ordre super admin vs entreprise** | **Validé** | **Mélange** des deux (une seule liste mélangée, pas de priorité stricte). |
| **Tablette inactive** | **Validé** | Quand la tablette reste inactive, afficher les annonces (écran de veille) en boucle. |
| **Écran de veille – boucle** | **Validé** | **Boucle infinie** : passer toutes les annonces, à la fin recommencer depuis la première, tant que la tablette reste inactive. |
| **Audio vidéo** | **Validé** | **Désactivé par défaut** (muet) ; possibilité pour l’utilisateur d’activer le son (bouton/contrôle). |
| **Moments d’affichage** | À fixer | Au démarrage (oui/non), après inactivité (oui/non), uniquement en veille ? |
| **Formats** | **Validé** | Maximum compatibilité : vidéo = mp4, webm, mov, ogv ; images = JPEG, PNG, WebP, GIF. |

---

## 12. Améliorations possibles (optionnel)

Idées pour renforcer la fonctionnalité annonces après la première version :

| Amélioration | Bénéfice |
|--------------|----------|
| **Lien cliquable** | Ajouter une URL optionnelle sur une annonce (bouton « En savoir plus ») qui ouvre un site ou une page dans l’app. |
| **Texte court** | Champ description / sous-titre sous l’affiche ou la vidéo (une ligne ou deux) pour préciser le message. |
| **Preview dans le dashboard** | Aperçu de l’annonce (image + durée) avant publication. |
| **Transition entre annonces** | En écran de veille : fondu ou transition courte entre deux annonces pour un rendu plus propre. |
| **Son vidéo** | Règle optionnelle : son activé ou muet par défaut pour les vidéos (ou par annonce). |
| **Plage horaire** | Afficher une annonce seulement à certaines heures (ex. petit-déjeuner, soir) pour ciblage fin. |
| **Limite durée vidéo** | Pour éviter des vidéos trop longues en veille : durée max en secondes (ex. 2 min) ou simple recommandation. |
| **Formats image** | Spécifier formats acceptés (JPEG, PNG, WebP) et éventuellement ratio conseillé (ex. 16:9) pour un rendu homogène. |
