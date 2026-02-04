# 🧪 GUIDE DE TEST COMPLET - TERANGUEST MOBILE

**Version :** 2.0.1  
**Date :** 3 Février 2026  
**Statut :** Tests Production-Ready

---

## 🎯 OBJECTIF

Valider le bon fonctionnement de **TOUS les modules** et **TOUS les parcours utilisateur**.

---

## ✅ CHECKLIST TESTS

### 1️⃣ AUTHENTIFICATION (3 écrans)

#### Splash Screen
```
✅ Affichage logo
✅ Animation de chargement
✅ Auto-login si token valide
✅ Redirection vers Dashboard si connecté
✅ Redirection vers Login si non connecté
```

#### Login Screen
```
✅ Champs email et password
✅ Validation email format
✅ Validation password non vide
✅ Bouton Login actif/inactif
✅ Message d'erreur si échec
✅ Redirection Dashboard si succès
✅ Token stocké correctement
```

#### Profile Screen
```
✅ Affichage infos utilisateur
✅ Avatar avec initiale
✅ Nom, email, chambre, hôtel
✅ Section "Mes Historiques" (6 liens)
✅ Section "Paramètres" (2 liens)
✅ Bouton déconnexion
✅ Confirmation avant logout
✅ Retour au login après logout
```

---

### 2️⃣ DASHBOARD (1 écran)

```
✅ Header avec météo temps réel
✅ Nom utilisateur affiché
✅ Badge panier (nombre d'articles)
✅ 8 services en grille 4 colonnes 3D
✅ Tap sur chaque service fonctionne
✅ Navigation vers chaque module
✅ Gradient & design cohérent
```

---

### 3️⃣ ROOM SERVICE (3 écrans)

#### Categories Screen
```
✅ Liste catégories en grille 4 col 3D
✅ Nom + nombre d'articles par catégorie
✅ Badge disponible/indisponible
✅ Tap catégorie → Articles
✅ Pull-to-refresh fonctionne
✅ Message si aucune catégorie
```

#### Menu Items Screen
```
✅ Liste articles en grille 4 col 3D
✅ Nom + prix + image (ou placeholder)
✅ Badge indisponible si non dispo
✅ Tap article → Détail
✅ Retour vers catégories
✅ Pull-to-refresh fonctionne
```

#### Item Detail Screen
```
✅ Image grande (ou placeholder)
✅ Nom + prix + description
✅ Sélecteur quantité (+ / -)
✅ Note spéciale (optionnel)
✅ Bouton "Ajouter au panier"
✅ Ajout panier → Badge mis à jour
✅ Message confirmation
✅ Retour vers articles
```

---

### 4️⃣ PANIER (1 écran)

#### Cart Screen
```
✅ Liste articles avec quantités
✅ Prix unitaire + sous-total
✅ Total général
✅ Instructions spéciales (optionnel)
✅ Sélecteur quantité (+ / -)
✅ Suppression article (quantité à 0)
✅ Bouton "Commander"
✅ Confirmation commande
✅ Panier vidé après commande
✅ Badge mis à jour
✅ Message si panier vide
```

---

### 5️⃣ COMMANDES (2 écrans)

#### Orders List Screen
```
✅ Liste commandes en grille 4 col 3D
✅ Numéro commande + date
✅ Badge statut coloré (pending, preparing, ready, delivered)
✅ Nombre d'articles + total
✅ Filtres par statut fonctionnels
✅ Tap commande → Détail
✅ Pull-to-refresh
✅ Scroll infini (pagination)
✅ Message si aucune commande
```

#### Order Detail Screen
```
✅ En-tête commande (numéro, statut, date)
✅ Timeline visuelle (4 étapes)
✅ Liste articles avec quantités et prix
✅ Total commande
✅ Bouton "Recommander" si delivered
✅ Recommander → Panier pré-rempli
✅ Message confirmation
✅ Retour vers liste
```

**Test Profil :**
```
✅ Profil → Mes Commandes → Liste complète
```

---

### 6️⃣ RESTAURANTS & BARS (4 écrans)

#### Restaurants List Screen
```
✅ Liste restaurants en grille 4 col 3D
✅ Nom + type + cuisine + capacité
✅ Badge Ouvert/Fermé
✅ Filtres par type (Tous, Restaurant, Bar)
✅ Tap restaurant → Détail
✅ Pull-to-refresh
✅ Message si aucun restaurant
```

#### Restaurant Detail Screen
```
✅ Image grande (ou placeholder)
✅ Nom + type + cuisine
✅ Description complète
✅ Capacité
✅ Horaires d'ouverture
✅ Commodités (liste)
✅ Bouton "Réserver une table"
✅ Navigation vers formulaire
```

#### Reserve Restaurant Screen
```
✅ Date picker fonctionnel
✅ Heure (chips 12h-23h)
✅ Sélecteur personnes (+ / -)
✅ Demandes spéciales (optionnel)
✅ Récapitulatif clair
✅ Bouton "Confirmer"
✅ Loader pendant requête
✅ Message confirmation
✅ Retour automatique
```

#### My Restaurant Reservations Screen
```
✅ Liste réservations en grille 4 col 3D
✅ Nom restaurant + date/heure
✅ Nombre personnes
✅ Badge statut coloré
✅ Pull-to-refresh
✅ Message si aucune réservation
```

**Test Profil :**
```
✅ Profil → Mes Réservations Restaurant → Liste complète
```

---

### 7️⃣ SPA & BIEN-ÊTRE (4 écrans)

#### Spa Services List Screen
```
✅ Liste services en grille 4 col 3D
✅ Nom + prix + durée
✅ Badge indisponible si non dispo
✅ Filtres par catégorie
✅ Tap service → Détail
✅ Pull-to-refresh
✅ Message si aucun service
```

#### Spa Service Detail Screen
```
✅ Image grande (ou placeholder)
✅ Nom + prix + durée
✅ Description complète
✅ Bouton "Réserver"
✅ Navigation vers formulaire
```

#### Reserve Spa Screen
```
✅ Date picker fonctionnel
✅ Time picker fonctionnel
✅ Demandes spéciales (optionnel)
✅ Récapitulatif avec prix
✅ Bouton "Confirmer"
✅ Loader pendant requête
✅ Message confirmation
✅ Retour automatique
```

#### My Spa Reservations Screen
```
✅ Liste réservations en grille 4 col 3D
✅ Nom service + date/heure
✅ Prix + durée
✅ Badge statut coloré
✅ Pull-to-refresh
✅ Message si aucune réservation
```

**Test Profil :**
```
✅ Profil → Mes Réservations Spa → Liste complète
```

---

### 8️⃣ EXCURSIONS (4 écrans)

#### Excursions List Screen
```
✅ Liste excursions en grille 4 col 3D
✅ Nom + prix adulte/enfant + durée
✅ Badge indisponible si non dispo
✅ Badge durée visible
✅ Tap excursion → Détail
✅ Pull-to-refresh
✅ Message si aucune excursion
```

#### Excursion Detail Screen
```
✅ Image grande (ou placeholder)
✅ Nom + durée
✅ Prix adulte + prix enfant
✅ Description complète
✅ Liste inclusions (checkmarks)
✅ Bouton "Réserver"
✅ Navigation vers formulaire
```

#### Book Excursion Screen
```
✅ Date picker fonctionnel
✅ Sélecteur adultes (1-20, + / -)
✅ Sélecteur enfants (0-20, + / -)
✅ Demandes spéciales (optionnel)
✅ Calcul prix dynamique
✅ Récapitulatif détaillé
✅ Bouton "Confirmer"
✅ Loader pendant requête
✅ Message confirmation
✅ Retour automatique
```

#### My Excursion Bookings Screen
```
✅ Liste bookings en grille 4 col 3D
✅ Nom excursion + date
✅ Participants (adultes + enfants)
✅ Prix total
✅ Badge statut coloré
✅ Pull-to-refresh
✅ Message si aucun booking
```

**Test Profil :**
```
✅ Profil → Mes Excursions → Liste complète
```

---

### 9️⃣ BLANCHISSERIE (3 écrans)

#### Laundry List Screen
```
✅ Liste services en grille 4 col 3D
✅ Nom + prix par pièce
✅ Sélecteur quantité par service (+ / -)
✅ Footer récapitulatif (articles + total)
✅ Total mis à jour en temps réel
✅ Bouton "Confirmer" actif si sélection
✅ Navigation vers formulaire
✅ Pull-to-refresh
```

#### Create Laundry Request Screen
```
✅ Récapitulatif articles sélectionnés
✅ Quantité × prix par article
✅ Total général
✅ Instructions spéciales (optionnel)
✅ Bouton "Confirmer"
✅ Loader pendant requête
✅ Message confirmation
✅ Sélection vidée après
✅ Retour automatique
```

#### My Laundry Requests Screen
```
✅ Liste demandes en grille 4 col 3D
✅ Numéro demande + date
✅ Nombre articles + total
✅ Badge statut coloré (6 statuts)
✅ Pull-to-refresh
✅ Message si aucune demande
```

**Test Profil :**
```
✅ Profil → Mes Demandes Blanchisserie → Liste complète
```

---

### 🔟 SERVICES PALACE (3 écrans)

#### Palace List Screen
```
✅ Liste services en grille 4 col 3D
✅ Nom + catégorie
✅ Icône étoile dorée
✅ Tap service → Formulaire
✅ Pull-to-refresh
✅ Message si aucun service
```

#### Create Palace Request Screen
```
✅ Nom service affiché
✅ Champ détails (multi-lignes)
✅ Date picker optionnel
✅ Time picker optionnel
✅ Date + heure combinées
✅ Bouton "Envoyer"
✅ Loader pendant requête
✅ Message confirmation
✅ Retour automatique
```

#### My Palace Requests Screen
```
✅ Liste demandes en grille 4 col 3D
✅ Nom service + date création
✅ Heure planifiée si définie
✅ Badge statut coloré (4 statuts)
✅ Pull-to-refresh
✅ Message si aucune demande
```

**Test Profil :**
```
✅ Profil → Mes Demandes Palace → Liste complète
```

---

## 🧪 TESTS TRANSVERSAUX

### Navigation
```
✅ Retour arrière fonctionne partout
✅ Dashboard accessible depuis tous les écrans
✅ Profil accessible depuis Dashboard
✅ Tous les historiques accessibles depuis Profil
✅ Deep links fonctionnent
```

### Design 3D
```
✅ Toutes les cartes ont effet 3D
✅ Transform Matrix4 appliqué
✅ Ombres multiples visibles
✅ Grille 4 colonnes uniforme
✅ Bordures dorées présentes
```

### UX
```
✅ Pull-to-refresh fonctionne partout
✅ Loading states élégants
✅ Error states clairs
✅ Empty states informatifs
✅ Messages en français
✅ Animations fluides
```

### Performance
```
✅ Temps de chargement < 2s
✅ Scroll fluide
✅ Pas de lag
✅ Mémoire stable
✅ Badge panier instantané
```

---

## 📋 TESTS PAR PARCOURS

### Parcours A : Nouvelle Commande
```
1. Login
2. Dashboard → Room Service
3. Sélectionner catégorie
4. Sélectionner article
5. Ajouter au panier (quantité + note)
6. Badge panier mis à jour ✅
7. Aller au panier
8. Vérifier contenu
9. Commander
10. Confirmation ✅
11. Panier vidé ✅
12. Profil → Mes Commandes
13. Voir commande créée ✅
```

### Parcours B : Réserver Restaurant
```
1. Dashboard → Restaurants
2. Filtrer par type
3. Sélectionner restaurant
4. Voir détails
5. Cliquer "Réserver"
6. Choisir date + heure + personnes
7. Confirmer
8. Confirmation ✅
9. Profil → Mes Réservations Restaurant
10. Voir réservation créée ✅
```

### Parcours C : Réserver Spa
```
1. Dashboard → Spa
2. Filtrer par catégorie
3. Sélectionner service
4. Voir détails
5. Cliquer "Réserver"
6. Choisir date + heure
7. Confirmer
8. Confirmation ✅
9. Profil → Mes Réservations Spa
10. Voir réservation créée ✅
```

### Parcours D : Réserver Excursion
```
1. Dashboard → Excursions
2. Sélectionner excursion
3. Voir détails + inclusions
4. Cliquer "Réserver"
5. Choisir date + adultes + enfants
6. Vérifier calcul prix ✅
7. Confirmer
8. Confirmation ✅
9. Profil → Mes Excursions
10. Voir booking créé ✅
```

### Parcours E : Demande Blanchisserie
```
1. Dashboard → Blanchisserie
2. Sélectionner quantités (+ / -)
3. Vérifier footer total ✅
4. Cliquer "Confirmer"
5. Voir récapitulatif
6. Confirmer demande
7. Confirmation ✅
8. Profil → Mes Demandes Blanchisserie
9. Voir demande créée ✅
```

### Parcours F : Demande Palace
```
1. Dashboard → Services Palace
2. Sélectionner service
3. Remplir détails
4. Choisir date + heure (optionnel)
5. Envoyer
6. Confirmation ✅
7. Profil → Mes Demandes Palace
8. Voir demande créée ✅
```

### Parcours G : Consulter Historiques
```
1. Dashboard → Profil
2. Section "Mes Historiques" visible ✅
3. Tap "Mes Commandes" → Liste ✅
4. Retour Profil
5. Tap "Mes Réservations Restaurant" → Liste ✅
6. Retour Profil
7. Tap "Mes Réservations Spa" → Liste ✅
8. Retour Profil
9. Tap "Mes Excursions" → Liste ✅
10. Retour Profil
11. Tap "Mes Demandes Blanchisserie" → Liste ✅
12. Retour Profil
13. Tap "Mes Demandes Palace" → Liste ✅
```

---

## 🎯 CRITÈRES DE SUCCÈS

```
✅ 0 crash
✅ 0 erreur bloquante
✅ Tous les parcours fonctionnels
✅ Design cohérent 100%
✅ Navigation fluide
✅ Temps de réponse < 2s
✅ Messages clairs
✅ Feedback utilisateur immédiat
```

---

## 📊 RAPPORT FINAL

**À remplir après tests :**

```
Tests réussis : ____ / 300
Bugs trouvés : ____
Bugs critiques : ____
Suggestions : ____

Statut : ✅ VALIDÉ / ⚠️ À CORRIGER
```

---

**🎊 GUIDE DE TEST COMPLET - TERANGUEST MOBILE 2.0.1 🎊**

**Tous les modules testables ! ✅**
