# Notifications push sur iPhone (appareil réel)

Si les notifications **ne s’affichent pas** sur un **iPhone réel** (alors que le serveur les envoie), vérifier les points suivants.

## 1. Firebase Console – APNs

Sans configuration APNs, FCM ne peut pas livrer les notifications sur iOS.

1. Ouvrir [Firebase Console](https://console.firebase.google.com) → ton projet.
2. **Paramètres du projet** (icône engrenage) → **Cloud Messaging**.
3. Section **Configuration des applications Apple** :
   - Soit **Clé d’authentification APNs** (.p8) : recommandé.
     - Dans [Apple Developer](https://developer.apple.com/account/resources/authkeys/list) : Keys → créer une clé avec **Apple Push Notifications service (APNs)**.
     - Télécharger le fichier .p8 (une seule fois), noter l’**Key ID** et le **Team ID** / **Bundle ID**.
     - Dans Firebase : upload de la clé .p8 + Key ID + Team ID + Bundle ID de l’app.
   - Soit **Certificat APNs** (certificat push .p12) pour l’app.

4. Vérifier que le **Bundle ID** dans Firebase correspond exactement à celui du projet Xcode (`ios/Runner.xcodeproj` ou **Runner** → **General** → **Bundle Identifier**).

## 2. Xcode – Capabilities

1. Ouvrir `ios/Runner.xcworkspace` dans Xcode.
2. Sélectionner le target **Runner** → onglet **Signing & Capabilities**.
3. Vérifier que **Push Notifications** est ajouté (bouton **+ Capability** si absent).

## 3. Entitlements

Le fichier `ios/Runner/Runner.entitlements` doit contenir :

- `aps-environment` = `development` pour les builds de développement (depuis Xcode).
- Pour les builds **TestFlight / App Store** : passer à `production` (ou utiliser un fichier d’entitlements de release avec `production`).

## 4. Test

1. Lancer l’app sur un **iPhone physique** (câble ou wireless).
2. Accepter les **autorisations de notification** quand l’app les demande.
3. Se connecter (pour enregistrer le token FCM côté serveur).
4. Mettre l’app en **arrière-plan** (ou fermer l’écran).
5. Déclencher une notification depuis le dashboard (ex. changement de statut de commande) ou utiliser **« Test depuis le serveur »** dans l’app (page Notifications).

Si tout est correct, la notification doit apparaître sur l’écran de verrouillage / centre de notifications.

## 5. Liste des notifications dans l’app

L’historique des notifications (page **Notifications**) est maintenant **persisté** : les notifications reçues en arrière-plan sont enregistrées et s’affichent au prochain lancement de l’app, même si tu n’as pas ouvert la notification.
