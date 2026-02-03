<?php

namespace App\Services;

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
     * Envoyer une notification de nouvelle commande
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
