# Spécification : Avis (satisfaction client) et Excursions

> **Date :** 5 mars 2026  
> **Statut :** Implémenté

---

## 1. Rubrique « Avis » (satisfaction client)

### Objectif
Recueillir les retours et évaluations des utilisateurs après un service rendu.

### Déclencheurs (éligibilité pour un avis)
Un avis peut être soumis **après** :
- **Commande livrée** (order `status = delivered`)
- **Check-out** (reservation `status = checked_out`)
- **Demande bien traitée** : blanchisserie livrée (`status = delivered`) ou service palace terminé (`status = completed`)
- **Réservation excursion terminée** (excursion_booking `status = completed`)

### Implémentation
- **Backend :** table `guest_reviews` (reviewable_type / reviewable_id polymorphiques), modèle `GuestReview`, API :
  - `GET /api/reviews/pending` — liste des éléments éligibles non encore notés
  - `POST /api/reviews` — soumission d’un avis (rating 1–5, commentaire optionnel)
  - `GET /api/reviews` — liste des avis déjà donnés par l’utilisateur (rubrique « Mes avis »)
- **App Flutter :** écran « Avis » (depuis Profil) avec onglets « À noter » / « Mes avis », formulaire note + commentaire pour chaque élément en attente.

L’utilisateur ouvre la rubrique **Avis** depuis **Mon profil** ; les éléments éligibles (commandes livrées, check-out, demandes traitées, excursions terminées) s’affichent dans « À noter ». Après soumission, l’avis apparaît dans « Mes avis ».

---

## 2. Section Excursions : horaires et description

### Modifications
- **Horaires :** champ **Horaires (détail)** / `schedule_description` (texte) pour décrire le déroulé et les horaires de l’activité (ex. « Départ 09h00, pause déjeuner 12h30–14h, retour 18h »). L’heure de départ reste dans `departure_time`.
- **Description :** le champ existant **Description** est utilisé comme **description détaillée** de l’activité (libellé « Description détaillée » dans le dashboard).
- **Tranche d’âge enfants :** nouveau champ **Tranche d’âge enfants** / `children_age_range` (ex. « 3–12 ans »), affiché clairement dans la fiche excursion (app et API).

### Fichiers impactés
- Migration : `schedule_description`, `children_age_range` sur `excursions`
- Modèle `Excursion`, dashboard (create/edit), API (index/show), app Flutter (détail excursion) : affichage horaires, description détaillée, tranche d’âge enfants.

---

## 3. Résumé des livrables

| Composant | Détail |
|-----------|--------|
| **Avis** | Table `guest_reviews`, API pending/store/index, écran Avis (À noter / Mes avis) accessible depuis Profil |
| **Excursions** | Champs `schedule_description` et `children_age_range`, formulaire dashboard, API et écran détail Flutter (horaires + âge enfants) |
