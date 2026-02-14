<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FirebaseNotificationService
{
    protected $messaging;
    protected $credentialsPath;
    protected $projectId;
    protected $useDirectHttp = false;

    public function __construct()
    {
        try {
            $this->messaging = app('firebase.messaging');
            $this->credentialsPath = $this->resolveCredentialsPath(env('FIREBASE_CREDENTIALS'));
            $this->projectId = env('FIREBASE_PROJECT_ID');
            
            // If we have credentials, try to load project_id from them
            if ($this->credentialsPath && file_exists($this->credentialsPath)) {
                $contents = file_get_contents($this->credentialsPath);
                $decoded = json_decode($contents, true);
                if ($decoded && isset($decoded['project_id'])) {
                    $this->projectId = $this->projectId ?: $decoded['project_id'];
                }
            }
        } catch (\Exception $e) {
            Log::error('FirebaseNotificationService constructor error: ' . $e->getMessage());
            $this->credentialsPath = $this->resolveCredentialsPath(env('FIREBASE_CREDENTIALS'));
            $this->projectId = env('FIREBASE_PROJECT_ID');
        }
    }

    /**
     * Resolve the credentials file path
     */
    private function resolveCredentialsPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);
        if (realpath($path) !== false) {
            return realpath($path);
        }

        $fromBase = base_path($path);
        if (is_readable($fromBase)) {
            return realpath($fromBase) ?: $fromBase;
        }

        $fromStorage = storage_path('app/firebase/' . basename($path));
        if (is_readable($fromStorage)) {
            return realpath($fromStorage) ?: $fromStorage;
        }

        return $fromBase;
    }

    /**
     * Get OAuth2 access token from service account credentials
     */
    protected function getAccessToken(): ?string
    {
        if (!$this->credentialsPath || !file_exists($this->credentialsPath)) {
            Log::error('Firebase credentials file not found for token generation');
            return null;
        }

        $cacheKey = 'firebase_oauth_token_' . md5($this->credentialsPath);
        
        // Check cache first (token valid for 1 hour, we cache for 50 minutes)
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            if (!$credentials || !isset($credentials['client_email']) || !isset($credentials['private_key'])) {
                Log::error('Invalid Firebase credentials format');
                return null;
            }

            $clientEmail = $credentials['client_email'];
            $privateKey = $credentials['private_key'];
            $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

            // Create JWT
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $now = time();
            $claimSet = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ]);

            $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64ClaimSet = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claimSet));
            $signatureInput = $base64Header . '.' . $base64ClaimSet;

            openssl_sign($signatureInput, $signature, $privateKey, 'SHA256');
            $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $signatureInput . '.' . $base64Signature;

            // Exchange JWT for access token
            $response = Http::asForm()->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;
                $expiresIn = $tokenData['expires_in'] ?? 3600;

                if ($accessToken) {
                    // Cache token for 50 minutes (3000 seconds)
                    Cache::put($cacheKey, $accessToken, intval($expiresIn) - 600);
                    Log::info('Firebase OAuth2 token obtained successfully');
                    return $accessToken;
                }
            } else {
                Log::error('Failed to obtain OAuth2 token: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exception while getting OAuth2 token: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Envoyer une notification push à un utilisateur via HTTP direct (contourne les problèmes de proxy)
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [])
    {
        if (empty($user->fcm_token)) {
            Log::warning("User {$user->id} has no FCM token");
            return false;
        }
    
        // Try direct HTTP API first (more reliable on shared hosting)
        $result = $this->sendViaHttpApi($user->fcm_token, $title, $body, $data);
        if ($result) {
            return true;
        }
    
        // Fallback to SDK method
        return $this->sendViaSdk($user, $title, $body, $data);
    }
    
    /**
     * Send notification via direct HTTP API call with explicit OAuth2 token
     */
    protected function sendViaHttpApi(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('Cannot send notification: failed to obtain OAuth2 access token');
                return false;
            }
        
            if (!$this->projectId) {
                Log::error('Cannot send notification: FIREBASE_PROJECT_ID not configured');
                return false;
            }
    
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    
            // Build message payload - data must be a map (object), not a list
            $messageData = $this->convertDataValuesToString($data);
            // Ensure data is never empty - add a default field if needed
            if (empty($messageData)) {
                $messageData = ['type' => 'notification'];
            }
        
            $messagePayload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $messageData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];
    
            // Try cURL first (bypasses Laravel HTTP client proxy issues)
            $result = $this->sendViaCurl($url, $messagePayload, $accessToken);
            if ($result) {
                return true;
            }
    
            // Fallback to Laravel HTTP client
            return $this->sendViaLaravelHttp($url, $messagePayload, $accessToken, $fcmToken);
        } catch (\Exception $e) {
            Log::error("Exception in sendViaHttpApi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification using raw cURL (bypasses proxy issues with Laravel HTTP client)
     */
    protected function sendViaCurl(string $url, array $payload, string $accessToken): bool
    {
        if (!function_exists('curl_init')) {
            Log::warning('cURL not available, skipping cURL method');
            return false;
        }
    
        $ch = curl_init();
            
        $jsonPayload = json_encode($payload);
            
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload),
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            // Disable proxy for this request
            CURLOPT_PROXY => null,
            CURLOPT_HTTPPROXYTUNNEL => false,
            // Follow redirects
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
    
        if ($curlError) {
            Log::error("cURL error: " . $curlError);
            return false;
        }
    
        if ($httpCode === 200) {
            Log::info("Notification sent successfully via cURL");
            return true;
        } else {
            $responseData = json_decode($response, true);
            Log::error("FCM cURL error (HTTP {$httpCode}): " . ($responseData ? json_encode($responseData) : $response));
                
            // If auth error, clear token cache
            if ($httpCode === 401) {
                Cache::forget('firebase_oauth_token_' . md5($this->credentialsPath));
            }
                
            return false;
        }
    }
    
    /**
     * Send notification using Laravel HTTP client (fallback)
     */
    protected function sendViaLaravelHttp(string $url, array $payload, string $accessToken, string $fcmToken): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);
        
            if ($response->successful()) {
                Log::info("Notification sent via HTTP API to token: " . substr($fcmToken, 0, 20) . "...");
                return true;
            } else {
                $errorBody = $response->json() ?? ['error' => $response->body()];
                Log::error("FCM HTTP API error: " . json_encode($errorBody));
                        
                // If it's an auth error, clear the cache to force token refresh
                if ($response->status() === 401) {
                    Cache::forget('firebase_oauth_token_' . md5($this->credentialsPath));
                }
                        
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in sendViaLaravelHttp: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert data array values to strings (FCM requirement)
     */
    protected function convertDataValuesToString(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = (string) $value;
        }
        return $result;
    }
    
    /**
     * Send notification via Firebase SDK (fallback method)
     */
    protected function sendViaSdk(User $user, string $title, string $body, array $data = []): bool
    {
        try {
            if (!$this->messaging) {
                Log::error('Firebase messaging not initialized');
                return false;
            }
    
            $message = CloudMessage::new()
                ->withToken($user->fcm_token)
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
    
            Log::info("Notification sent via SDK to user {$user->id}: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification via SDK to user {$user->id}: " . $e->getMessage());
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

        $successCount = 0;
        $failCount = 0;

        // Send to each token individually using HTTP API for better reliability
        foreach ($tokens as $token) {
            $result = $this->sendViaHttpApi($token, $title, $body, $data);
            if ($result) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        Log::info("Multicast notification: {$successCount} succeeded, {$failCount} failed out of " . count($tokens) . " total");
        
        return $successCount > 0;
    }

    /**
     * Send multicast notification via SDK (legacy method, kept for compatibility)
     */
    protected function sendMulticastViaSdk(array $tokens, string $title, string $body, array $data = []): bool
    {
        try {
            if (!$this->messaging) {
                Log::error('Firebase messaging not initialized');
                return false;
            }

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->sendMulticast($message, $tokens);
            
            Log::info("Notification sent via SDK multicast to " . count($tokens) . " users: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send multicast notification via SDK: " . $e->getMessage());
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
