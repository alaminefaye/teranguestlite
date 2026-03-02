# Déclaration – Accès aux photos et vidéos (Google Play Console)

Ce document sert à remplir la section **« Autorisations – Photos et vidéos »** dans la Google Play Console pour l’app **TeranGuest**.

---

## 1. Type d’accès

**Recommandation pour TeranGuest :** **Accès exceptionnel ou ponctuel.**

L’application ne lit les photos que lorsque l’utilisateur choisit d’**envoyer une image** au personnel de l’hôtel (chat, demande de service, etc.). L’utilisateur ouvre le sélecteur de photos (sélecteur système ou picker) uniquement à ce moment-là. Il n’y a pas d’accès en arrière-plan ni de lecture continue de la galerie.

Si la console vous propose une option du type **« Accès exceptionnel / ponctuel »** ou **« Via le sélecteur de photos Android »**, sélectionnez-la.

---

## 2. Description à fournir pour `READ_MEDIA_IMAGES`

**Question :** *« Décrivez l’utilisation de l’autorisation READ_MEDIA_IMAGES par votre appli »*

**Texte à copier-coller (français) :**

```
L’application TeranGuest permet aux clients d’un établissement hôtelier d’envoyer des photos au personnel (ex. : demande de room service, message au personnel, signalement). L’accès aux images est strictement limité au moment où l’utilisateur choisit de joindre une photo : il ouvre le sélecteur de photos du système, choisit une ou plusieurs images, qui sont alors envoyées dans le cadre de sa demande. Aucun accès en arrière-plan ni aucune lecture continue de la galerie n’est effectuée.
```

**Version courte (si le champ est limité en caractères) :**

```
Les utilisateurs peuvent joindre des photos lorsqu’ils envoient un message ou une demande au personnel de l’hôtel. Seules les images sélectionnées via le sélecteur système à ce moment-là sont utilisées ; pas d’accès continu à la galerie.
```

---

## 3. Si la console demande une justification « accès fréquent »

Si vous avez déclaré un **accès fréquent** aux photos/vidéos, il faut en justifier la nécessité. Pour TeranGuest, l’usage est **ponctuel** ; il est préférable de ne pas déclarer d’accès fréquent et d’utiliser la description ci-dessus (accès ponctuel via le sélecteur).

Si malgré tout vous devez fournir une justification pour un accès plus large, vous pouvez utiliser (à adapter selon le cas) :

```
L’application nécessite la permission READ_MEDIA_IMAGES pour permettre aux clients de joindre des photos à leurs messages et demandes envoyés au personnel de l’hôtel (room service, chat, signalements). L’accès est déclenché uniquement par l’action de l’utilisateur (bouton « Joindre une image ») et utilise le sélecteur de photos du système lorsque cela est possible, afin de limiter la portée des données accessibles.
```

---

## 4. Rappel technique (côté app)

- **Android :** les permissions déclarées dans `AndroidManifest.xml` incluent `READ_MEDIA_IMAGES` (et éventuellement `READ_EXTERNAL_STORAGE` pour les anciennes versions). L’utilisation réelle doit correspondre à la déclaration : sélection d’images par l’utilisateur au moment de l’envoi, sans accès permanent à la galerie.
- **Ressources Google :** [Centre d’aide – Autorisations liées aux photos et vidéos](https://support.google.com/googleplay/android-developer/answer/13392821) (à vérifier selon les mises à jour de la Play Console).

---

## 5. Checklist avant de valider

- [ ] Type d’accès coché : **ponctuel / exceptionnel** (ou équivalent).
- [ ] Description `READ_MEDIA_IMAGES` collée dans le champ prévu (version longue ou courte selon la place).
- [ ] Aucune affirmation d’accès « fréquent » ou « en arrière-plan » si vous utilisez uniquement le sélecteur au moment de l’envoi.
