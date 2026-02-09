# Synchronisation notifications – Exigences ↔ Implémentation

## Exigences demandées

1. **Toutes les commandes** passées dans l’application → le **client concerné** reçoit une notification.
2. **Toutes les réservations** (spa, restaurant, excursions, blanchisserie, palace, etc.) → le **client concerné** reçoit une notification (prise en compte).
3. **Quand une commande est validée** (par le staff) → le client concerné reçoit une notification.
4. **Quand une commande change de statut** (en préparation, prête, en livraison, livrée, annulée) → le client concerné reçoit une notification.
5. **Uniquement le client concerné** : pas d’envoi au mauvais client (ex. chambre A ne doit pas recevoir les notifs de la chambre B).

---

## Implémentation (synchronisée)

### Backend (Laravel)

| Exigence | Implémentation |
|----------|----------------|
| Cibler uniquement le client concerné | Table `guest_fcm_tokens` : chaque token est lié à un `guest_id`. Les notifications sont envoyées via `sendToGuest($guestId)` ou, si commande sans guest (user uniquement), via `sendToUser($user)`. |
| Nouvelle commande | `sendNewOrderNotificationToClient($order)` : si `order->guest_id` → `sendToGuest` ; sinon `order->user_id` + `user->fcm_token` → `sendToUser`. Appelée dans : `TabletSessionController` (checkout tablette), `RoomServiceController` (checkout app), `OrderController` (reorder). |
| Commande validée / statut modifié | `sendOrderStatusNotificationToClient($order)` : même ciblage (guest ou user). Appelée dans `Dashboard/OrderController` à chaque changement de statut : confirm, prepare, markReady, deliver, complete, cancel. |
| Réservation prise en compte (spa, restaurant, etc.) | `sendReservationConfirmationToGuest($stay['guest_id'], ...)` ou `sendToGuest($stay['guest_id'], ...)`. Appelée dans : `SpaServiceController`, `RestaurantController`, `ExcursionController`, `LaundryServiceController`, `PalaceServiceController`. |

### App mobile (Flutter)

| Exigence | Implémentation |
|----------|----------------|
| Recevoir les notifs sur l’appareil du client connecté | Au **login** : `NotificationService().registerWithBackendForUser()` enregistre le token FCM. Le backend le stocke sur `users.fcm_token` et, si l’utilisateur a un séjour actif, dans `guest_fcm_tokens` pour ce guest. |
| Recevoir les notifs sur la tablette (code client) | Après **validation du code client** (tablette) : `NotificationService().registerWithBackendForTabletSession(session)` enregistre le token pour ce `guest_id` dans `guest_fcm_tokens`. Seuls les appareils liés à ce guest reçoivent les notifs. |
| Ne pas recevoir les notifs d’un autre client | Le backend n’envoie qu’aux tokens du `guest_id` (ou du `user_id`) concerné. À la déconnexion : `unregisterFromBackend()` supprime le token côté backend et dans `guest_fcm_tokens`. |

### Récapitulatif des événements qui déclenchent une notification

- **Nouvelle commande** (room service, tablette ou app) → client concerné.
- **Commande confirmée** (dashboard) → client concerné.
- **Commande en préparation / prête / en livraison / livrée** (dashboard) → client concerné.
- **Commande annulée** (dashboard) → client concerné.
- **Réservation spa / restaurant / excursion / blanchisserie / palace confirmée** → client (guest) concerné.

Tout est relatif au **client connecté** (ou au guest de la session tablette) : aucun envoi au client d’une autre chambre ou d’un autre compte.
