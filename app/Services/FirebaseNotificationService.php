<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
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

        try {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
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
            
            Log::info("Notification sent to user {$user->id}: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
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
     * Récupère le User (compte tablette / chambre, role=guest) qui doit recevoir les notifications pour cette chambre.
     * Priorité : User avec room_id = chambre (lié formellement), sinon room_number.
     */
    public function getUserForRoom(int $roomId): ?User
    {
        $room = Room::withoutGlobalScope('enterprise')->find($roomId);
        if (! $room) {
            Log::warning("getUserForRoom: room_id {$roomId} not found");
            return null;
        }

        $user = User::where('enterprise_id', $room->enterprise_id)
            ->where('role', 'guest')
            ->where('room_id', $room->id)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->first();

        if (! $user) {
            $user = User::where('enterprise_id', $room->enterprise_id)
                ->where('role', 'guest')
                ->where('room_number', $room->room_number)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->first();
        }

        if (! $user) {
            Log::warning("getUserForRoom: no guest user with FCM token for room_id={$roomId}, room_number={$room->room_number}, enterprise_id={$room->enterprise_id}. The tablet must be logged in with the room account at least once to register the token.");
        }

        return $user;
    }

    /**
     * Envoyer une notification au client de la chambre (User tablette lié à cette chambre).
     */
    public function sendToClientOfRoom(int $roomId, string $title, string $body, array $data = []): bool
    {
        $user = $this->getUserForRoom($roomId);
        if (! $user) {
            Log::warning("sendToClientOfRoom: no recipient for room_id={$roomId}. Connect the tablet with the room account (Client Chambre XXX) and ensure notifications are allowed.");
            return false;
        }
        $sent = $this->sendToUser($user, $title, $body, $data);
        if ($sent) {
            Log::info("sendToClientOfRoom: notification sent to user_id={$user->id} (room_id={$roomId}): {$title}");
        }
        return $sent;
    }

    /**
     * Envoyer une notification de nouvelle commande au client de la chambre concernée.
     */
    public function sendNewOrderNotificationToRoom($order): bool
    {
        if (empty($order->room_id)) {
            Log::warning("Order {$order->id} has no room_id, cannot send notification to client");
            return false;
        }
        $formattedTotal = $order->formatted_total ?? (number_format($order->total, 0, ',', ' ') . ' FCFA');
        $title = "Nouvelle commande #{$order->order_number}";
        $body = "Votre commande d'un montant de {$formattedTotal} a été reçue.";
        return $this->sendToClientOfRoom($order->room_id, $title, $body, [
            'type' => 'order',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'screen' => 'OrderDetails',
        ]);
    }

    /**
     * Envoyer une notification de changement de statut de commande au client de la chambre.
     */
    public function sendOrderStatusNotificationToRoom($order): bool
    {
        if (empty($order->room_id)) {
            return false;
        }
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
        return $this->sendToClientOfRoom($order->room_id, $title, $body, [
            'type' => 'order_status',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'screen' => 'OrderDetails',
        ]);
    }

    /**
     * Envoyer une notification de confirmation de réservation au client de la chambre.
     */
    public function sendReservationConfirmationToRoom(int $roomId, string $reservationNumber, string $serviceName = 'Réservation'): bool
    {
        $title = "Réservation confirmée";
        $body = "Votre réservation {$serviceName} #{$reservationNumber} a été confirmée.";
        return $this->sendToClientOfRoom($roomId, $title, $body, [
            'type' => 'reservation',
            'reservation_number' => $reservationNumber,
            'screen' => 'Reservations',
        ]);
    }

    /**
     * Envoyer une notification de nouvelle commande (legacy: à un User donné)
     */
    public function sendNewOrderNotification(User $user, $order)
    {
        $title = "Nouvelle commande #{$order->order_number}";
        $body = "Votre commande d'un montant de {$order->formatted_total} a été reçue.";
        
        return $this->sendToUser($user, $title, $body, [
            'type' => 'order',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'screen' => 'OrderDetails',
        ]);
    }

    /**
     * Envoyer une notification de changement de statut de commande
     */
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
     * Envoyer une notification de confirmation de réservation
     */
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
