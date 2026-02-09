<?php

namespace App\Services;

use App\Models\GuestFcmToken;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

/**
 * Notifications push FCM – uniquement vers le client concerné.
 *
 * Exigences (voir docs/NOTIFICATIONS-EXIGENCES-SYNC.md) :
 * - Notifier pour : nouvelle commande, commande validée, chaque changement de statut,
 *   réservations (spa, restaurant, excursions, blanchisserie, palace).
 * - Ciblage : sendToGuest(guest_id) ou sendToUser(user) selon la commande/réservation.
 * - Aucun envoi au client d'une autre chambre : tokens stockés par guest_id dans guest_fcm_tokens.
 */
class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * FCM exige que toutes les valeurs du payload "data" soient des chaînes.
     */
    private function ensureStringData(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }
        return $out;
    }

    /**
     * Envoyer une notification push au client (guest) : tous les appareils enregistrés pour ce guest
     * (tablette en chambre + app mobile si le client s'est connecté avec un compte lié à ce guest).
     */
    public function sendToGuest(int $guestId, string $title, string $body, array $data = []): bool
    {
        $tokens = GuestFcmToken::where('guest_id', $guestId)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::warning("No FCM tokens found for guest {$guestId}");
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($this->ensureStringData($data))
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ])
                )
                ->withApnsConfig(
                    ApnsConfig::fromArray([
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ])
                );

            $this->messaging->sendMulticast($message, $tokens);
            GuestFcmToken::where('guest_id', $guestId)->update(['last_used_at' => now()]);
            Log::info("Notification sent to guest {$guestId} (" . count($tokens) . " device(s)): {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to guest {$guestId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification push à un utilisateur
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [])
    {
        if (empty($user->fcm_token)) {
            Log::warning("User {$user->id} has no FCM token");
            return false;
        }

        $token = trim($user->fcm_token);
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($this->ensureStringData($data))
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ])
                )
                ->withApnsConfig(
                    ApnsConfig::fromArray([
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ])
                );

            $this->messaging->send($message);

            Log::info("Notification sent to user {$user->id} (order status): {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage(), [
                'exception_class' => get_class($e),
            ]);
            return false;
        }
    }

    /**
     * Envoyer une notification push à plusieurs utilisateurs
     */
    public function sendToMultipleUsers(array $users, string $title, string $body, array $data = [])
    {
        $tokens = collect($users)
            ->filter(fn($user) => !empty($user->fcm_token))
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::warning("No valid FCM tokens found");
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->sendMulticast($message, $tokens);
            
            Log::info("Notification sent to " . count($tokens) . " users: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send multicast notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification à tous les utilisateurs d'une entreprise
     */
    public function sendToEnterprise($enterpriseId, string $title, string $body, array $data = [])
    {
        $users = User::where('enterprise_id', $enterpriseId)
            ->whereNotNull('fcm_token')
            ->get();

        if ($users->isEmpty()) {
            Log::warning("No users with FCM tokens found for enterprise {$enterpriseId}");
            return false;
        }

        return $this->sendToMultipleUsers($users->toArray(), $title, $body, $data);
    }

    /**
     * Notification nouvelle commande : envoyée uniquement au client concerné (guest ou user).
     */
    public function sendNewOrderNotificationToClient($order): bool
    {
        $formattedTotal = $order->total ? number_format($order->total, 0, ',', ' ') . ' FCFA' : '—';
        $title = "Nouvelle commande #{$order->order_number}";
        $body = "Votre commande d'un montant de {$formattedTotal} a été reçue.";
        $data = [
            'type' => 'order',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'screen' => 'OrderDetails',
        ];

        if ($order->guest_id) {
            return $this->sendToGuest($order->guest_id, $title, $body, $data);
        }
        if ($order->user_id && $order->user && $order->user->fcm_token) {
            return $this->sendToUser($order->user, $title, $body, $data);
        }
        Log::warning("Order {$order->id}: no guest_id nor user FCM token for notification");
        return false;
    }

    /** @deprecated Utiliser sendNewOrderNotificationToClient pour cibler le client */
    public function sendNewOrderNotification(User $user, $order)
    {
        $formattedTotal = $order->total ? number_format($order->total, 0, ',', ' ') . ' FCFA' : '—';
        $title = "Nouvelle commande #{$order->order_number}";
        $body = "Votre commande d'un montant de {$formattedTotal} a été reçue.";
        return $this->sendToUser($user, $title, $body, [
            'type' => 'order',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'screen' => 'OrderDetails',
        ]);
    }

    /**
     * Notification changement de statut de commande : envoyée uniquement au client concerné.
     */
    public function sendOrderStatusNotificationToClient($order): bool
    {
        $statusMessages = [
            'confirmed' => 'Votre commande a été confirmée',
            'preparing' => 'Votre commande est en préparation',
            'ready' => 'Votre commande est prête',
            'delivering' => 'Votre commande est en cours de livraison',
            'delivered' => 'Votre commande a été livrée',
            'cancelled' => 'Votre commande a été annulée',
        ];
        $title = "Commande #{$order->order_number}";
        $body = $statusMessages[$order->status] ?? "Statut de commande mis à jour";
        $data = [
            'type' => 'order_status',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'screen' => 'OrderDetails',
        ];

        // Essayer d'abord par guest (tokens tablette + app enregistrés pour ce guest)
        if ($order->guest_id) {
            $sent = $this->sendToGuest($order->guest_id, $title, $body, $data);
            if ($sent) {
                return true;
            }
            Log::info("Order status: no tokens for guest {$order->guest_id}, trying user fallback");
        }

        // Fallback : envoyer à l'utilisateur (user.fcm_token) si la commande a un user_id
        if ($order->user_id && $order->user) {
            $user = $order->user;
            if (! empty(trim($user->fcm_token ?? ''))) {
                return $this->sendToUser($user, $title, $body, $data);
            }
            Log::warning("Order status: user {$user->id} has no FCM token (order #{$order->order_number})");
        } else {
            Log::warning("Order status: order #{$order->order_number} has no guest_id and no user");
        }

        return false;
    }

    /** @deprecated Utiliser sendOrderStatusNotificationToClient */
    public function sendOrderStatusNotification(User $user, $order)
    {
        $statusMessages = [
            'confirmed' => 'Votre commande a été confirmée',
            'preparing' => 'Votre commande est en préparation',
            'ready' => 'Votre commande est prête',
            'delivering' => 'Votre commande est en cours de livraison',
            'delivered' => 'Votre commande a été livrée',
            'cancelled' => 'Votre commande a été annulée',
        ];
        $title = "Commande #{$order->order_number}";
        $body = $statusMessages[$order->status] ?? "Statut de commande mis à jour";
        return $this->sendToUser($user, $title, $body, [
            'type' => 'order_status',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'screen' => 'OrderDetails',
        ]);
    }

    /**
     * Notification confirmation de réservation : envoyée au client (guest) concerné.
     */
    public function sendReservationConfirmationToGuest(int $guestId, $reservation, string $reservationNumber = null): bool
    {
        $number = $reservationNumber ?? (isset($reservation->reservation_number) ? $reservation->reservation_number : (string) ($reservation->id ?? $reservation));
        $title = "Réservation confirmée";
        $body = "Votre réservation #{$number} a été confirmée.";
        return $this->sendToGuest($guestId, $title, $body, [
            'type' => 'reservation',
            'reservation_id' => (string) ($reservation->id ?? $reservation),
            'reservation_number' => $number,
            'screen' => 'ReservationDetails',
        ]);
    }

    /** @deprecated Utiliser sendReservationConfirmationToGuest pour cibler le client */
    public function sendReservationConfirmation(User $user, $reservation)
    {
        $title = "Réservation confirmée";
        $body = "Votre réservation #{$reservation->reservation_number} a été confirmée.";
        return $this->sendToUser($user, $title, $body, [
            'type' => 'reservation',
            'reservation_id' => (string) $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'screen' => 'ReservationDetails',
        ]);
    }

    /**
     * Envoyer un message personnalisé au staff
     */
    public function sendToStaff($enterpriseId, string $title, string $body, array $data = [])
    {
        $staff = User::where('enterprise_id', $enterpriseId)
            ->whereIn('role', ['admin', 'staff'])
            ->whereNotNull('fcm_token')
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("No staff with FCM tokens found for enterprise {$enterpriseId}");
            return false;
        }

        return $this->sendToMultipleUsers($staff->toArray(), $title, $body, $data);
    }
}
