<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FirebaseNotificationService
{
    protected $messaging;
    protected $credentialsPath;
    protected $projectId;
    protected $useDirectHttp = false;

    public function __construct()
    {
        $this->credentialsPath = config('services.firebase.credentials');
        $this->projectId = config('services.firebase.project_id');

        if ($this->credentialsPath && file_exists($this->credentialsPath)) {
            $contents = @file_get_contents($this->credentialsPath);
            $decoded = $contents ? json_decode($contents, true) : null;
            if ($decoded && isset($decoded['project_id'])) {
                $this->projectId = $this->projectId ?: $decoded['project_id'];
            }
        }

        try {
            $this->messaging = app('firebase.messaging');
        } catch (\Exception $e) {
            Log::error('FirebaseNotificationService constructor error: ' . $e->getMessage());
            $this->messaging = null;
        }
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

            if (!function_exists('curl_init')) {
                Log::error('cURL is not available for OAuth2 token request');
                return null;
            }

            $postFields = http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            $ch = curl_init($tokenUri);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $responseBody = curl_exec($ch);
            if ($responseBody === false) {
                $error = curl_error($ch) ?: 'Unknown cURL error';
                unset($ch);
                Log::error('Failed to obtain OAuth2 token via cURL: ' . $error);
                return null;
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            unset($ch);

            if ($statusCode >= 200 && $statusCode < 300) {
                $tokenData = json_decode($responseBody, true);
                if (!is_array($tokenData)) {
                    Log::error('Invalid OAuth2 token response JSON');
                    return null;
                }

                $accessToken = $tokenData['access_token'] ?? null;
                $expiresIn = isset($tokenData['expires_in']) ? (int) $tokenData['expires_in'] : 3600;

                if ($accessToken) {
                    Cache::put($cacheKey, $accessToken, $expiresIn - 600);
                    Log::info('Firebase OAuth2 token obtained successfully');
                    return $accessToken;
                }

                Log::error('OAuth2 token response missing access_token');
                return null;
            }

            Log::error('Failed to obtain OAuth2 token: HTTP ' . $statusCode . ' body: ' . $responseBody);
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
        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::warning("User {$user->id} has no FCM tokens");
            // If FCM fails due to hosting restrictions, store for in-app polling
            Log::warning("FCM failed, storing notification for in-app polling for user {$user->id}");
            $this->storeForInAppPolling($user, $title, $body, $data);
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            // Try direct HTTP API first (more reliable on shared hosting)
            $result = $this->sendViaHttpApi($token, $title, $body, $data);
            if ($result) {
                $success = true;
            }
        }

        if (!$success) {
            // If FCM fails entirely, store for in-app polling
            Log::warning("FCM failed for all tokens, storing notification for in-app polling for user {$user->id}");
            $this->storeForInAppPolling($user, $title, $body, $data);
        }

        // Fallback to SDK method (legacy)
        $this->sendViaSdk($user, $title, $body, $data);

        return $success;
    }

    /**
     * Store notification in database for in-app polling (fallback when FCM is blocked)
     */
    protected function storeForInAppPolling(User $user, string $title, string $body, array $data = []): void
    {
        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'type' => $data['type'] ?? 'general',
                'is_read' => false,
            ]);
            Log::info("Notification stored for in-app polling for user {$user->id}");
        } catch (\Exception $e) {
            Log::error("Failed to store notification for polling: " . $e->getMessage());
        }
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
                            'sound' => 'notification',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'channel_id' => 'high_importance_channel',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'notification.mp3',
                                'badge' => 1,
                                'content-available' => 1,
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
     * Send notification using raw cURL (bypasses proxy issues with Laravel HTTP client).
     * Uses options to avoid proxy stripping Authorization and to resend auth on redirects.
     */
    protected function sendViaCurl(string $url, array $payload, string $accessToken): bool
    {
        if (!function_exists('curl_init')) {
            Log::warning('cURL not available, skipping cURL method');
            return false;
        }

        $ch = curl_init();
        $jsonPayload = json_encode($payload);
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload),
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            // Force no proxy (shared hosting often uses HTTP_PROXY which strips Authorization)
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_HTTPPROXYTUNNEL => false,
            // Resend Authorization when following redirects (otherwise 401 after redirect)
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_UNRESTRICTED_AUTH => true,
        ]);

        // Prefer HTTP/2 if available (some proxies only alter HTTP/1.1)
        if (defined('CURL_VERSION_HTTP2') && (curl_version()['features'] & CURL_VERSION_HTTP2)) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        unset($ch);

        if ($curlError) {
            Log::error("cURL error: " . $curlError);
            return false;
        }

        if ($httpCode === 200) {
            Log::info("Notification sent successfully via cURL");
            return true;
        }

        $responseData = json_decode($response, true);
        Log::error("FCM cURL error (HTTP {$httpCode}): " . ($responseData ? json_encode($responseData) : $response));

        // Handle errors (cleanup stale tokens)
        $this->handleFcmError($httpCode, $responseData, $payload['message']['token'] ?? null);

        if ($httpCode === 401) {
            Cache::forget('firebase_oauth_token_' . md5($this->credentialsPath));
            Log::warning(
                'FCM 401: the server obtained an OAuth2 token but FCM rejected it. '
                . 'Often the host proxy/firewall strips the Authorization header to fcm.googleapis.com. '
                . 'Contact your host (e.g. O2Switch) or use a VPS where the header is not modified.'
            );
        }

        return false;
    }

    /**
     * Send notification using Laravel HTTP client (fallback)
     */
    protected function sendViaLaravelHttp(string $url, array $payload, string $accessToken, string $fcmToken): bool
    {
        try {
            if (!function_exists('curl_init')) {
                Log::warning('cURL not available, cannot use HTTP client fallback');
                return false;
            }

            $ch = curl_init();
            $jsonPayload = json_encode($payload);
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload),
            ];

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonPayload,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $body = curl_exec($ch);
            if ($body === false) {
                $error = curl_error($ch) ?: 'Unknown cURL error';
                unset($ch);
                Log::error("FCM HTTP API error (fallback): " . $error);
                return false;
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            unset($ch);

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info("Notification sent via HTTP API to token: " . substr($fcmToken, 0, 20) . "...");
                return true;
            }

            $errorBody = json_decode($body, true);
            if (!is_array($errorBody)) {
                $errorBody = ['raw' => $body];
            }
            Log::error("FCM HTTP API error (fallback): " . json_encode($errorBody));

            // Handle errors (cleanup stale tokens)
            $this->handleFcmError($statusCode, $errorBody, $fcmToken);

            if ($statusCode === 401) {
                Cache::forget('firebase_oauth_token_' . md5($this->credentialsPath));
            }

            return false;
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
            if ($value !== null) {
                $result[$key] = (string) $value;
            }
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

            $tokens = $user->fcmTokens()->pluck('token')->toArray();
            if (empty($tokens)) {
                return false;
            }

            $safeData = $this->convertDataValuesToString($data);

            $success = false;
            foreach ($tokens as $token) {
                $message = CloudMessage::new()
                    ->withToken($token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($safeData)
                    ->withAndroidConfig(
                        AndroidConfig::fromArray([
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'notification',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ])
                    )
                    ->withApnsConfig(
                        ApnsConfig::fromArray([
                            'payload' => [
                                'aps' => [
                                    'sound' => 'notification.mp3',
                                    'badge' => 1,
                                    'content-available' => 1,
                                ],
                            ],
                        ])
                    );

                try {
                    $this->messaging->send($message);
                    Log::info("Notification sent successfully via SDK to token: " . substr($token, 0, 20) . "...");
                    $success = true;
                } catch (\Exception $e) {
                    Log::error("Failed to send notification via SDK to token: " . substr($token, 0, 20) . "...: " . $e->getMessage());
                }
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to send notification via SDK to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification push à plusieurs utilisateurs
     */
    public function sendToMultipleUsers($users, string $title, string $body, array $data = [])
    {
        $tokens = collect($users)
            ->flatMap(function ($user) {
                if (is_array($user) && isset($user['id'])) {
                    // It's an array representation of a model, we need to load relations or refetch
                    // The easiest approach here is just to extract tokens if attached, but usually they aren't.
                    // Instead, look up the user by ID
                    $model = User::with('fcmTokens')->find($user['id']);
                    return $model ? $model->fcmTokens->pluck('token') : [];
                } elseif (is_object($user)) {
                    return $user->fcmTokens ? $user->fcmTokens->pluck('token') : [];
                }
                return [];
            })
            ->filter()
            ->unique()
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

        // Si des échecs ont eu lieu, on s'assure de stocker pour polling pour TOUS les utilisateurs cibles
        // car on ne sait pas forcément quel token appartient à quel user ici sans recherche supplémentaire.
        // Plus simple: le sendToUser gère déjà le fallback. Ici c'est pour sendToMultipleUsers (Staff/Enterprise).
        if ($successCount < count($tokens)) {
            foreach ($users as $user) {
                $userModel = null;
                if (is_array($user) && isset($user['id'])) {
                    $userModel = User::find($user['id']);
                } elseif ($user instanceof User) {
                    $userModel = $user;
                }
                
                if ($userModel) {
                    $this->storeForInAppPolling($userModel, $title, $body, $data);
                }
            }
        }

        Log::info("Multicast notification: {$successCount} succeeded, {$failCount} failed out of " . count($tokens) . " total");

        return $successCount > 0;
    }

    /**
     * Gère les erreurs FCM (suppression des tokens invalides)
     */
    protected function handleFcmError(int $httpCode, $response, ?string $token): void
    {
        if (!$token) return;

        $isUnregistered = false;

        // Erreur 404 peut signifier UNREGISTERED dans l'API v1
        if ($httpCode === 404) {
            $isUnregistered = true;
        }

        // Vérifier le corps de la réponse pour plus de certitude
        if ($response && is_array($response)) {
            $error = $response['error'] ?? null;
            if ($error && isset($error['status']) && $error['status'] === 'NOT_FOUND') {
                $isUnregistered = true;
            }
            
            $details = $error['details'] ?? [];
            foreach ($details as $detail) {
                if (isset($detail['errorCode']) && $detail['errorCode'] === 'UNREGISTERED') {
                    $isUnregistered = true;
                    break;
                }
            }
        }

        if ($isUnregistered) {
            try {
                \App\Models\UserFcmToken::where('token', $token)->delete();
                Log::info("Deleted unregistered FCM token: " . substr($token, 0, 20) . "...");
            } catch (\Exception $e) {
                Log::error("Failed to delete unregistered token: " . $e->getMessage());
            }
        }
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
            ->has('fcmTokens')
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
        if (!$room) {
            Log::warning("getUserForRoom: room_id {$roomId} not found");
            return null;
        }

        $user = User::where('enterprise_id', $room->enterprise_id)
            ->where('role', 'guest')
            ->where('room_id', $room->id)
            ->has('fcmTokens')
            ->first();

        if (!$user) {
            $user = User::where('enterprise_id', $room->enterprise_id)
                ->where('role', 'guest')
                ->where('room_number', $room->room_number)
                ->has('fcmTokens')
                ->first();
        }

        if (!$user) {
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
        if (!$user) {
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
    public function sendOrderStatusNotificationToRoom($order, ?string $reason = null): bool
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

        $normalizedReason = $reason !== null ? trim($reason) : '';
        if ($order->status === 'cancelled' && $normalizedReason !== '') {
            $body .= ' Motif : ' . $normalizedReason;
        }

        $data = [
            'type' => 'order_status',
            'order_id' => (string) $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'screen' => 'OrderDetails',
        ];

        if ($normalizedReason !== '') {
            $data['reason'] = $normalizedReason;
        }

        return $this->sendToClientOfRoom($order->room_id, $title, $body, $data);
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
     * Envoyer une notification au staff de préparation (admins + staff room_service_orders)
     * en EXCLUANT le département "Service en chambre" (qui ne s'occupe pas de la préparation).
     * Utilisé pour : nouvelle commande, commande prête en cuisine.
     */
    public function sendToKitchenStaff($enterpriseId, string $title, string $body, array $data = [])
    {
        $staff = User::where('enterprise_id', $enterpriseId)
            ->where(function ($q) {
                $q->where('role', 'admin')
                    ->orWhere(function ($q2) {
                        $q2->where('role', 'staff')
                            ->where(function ($q3) {
                                $q3->whereNull('managed_sections')
                                    ->orWhereJsonContains('managed_sections', \App\Helpers\StaffSection::ROOM_SERVICE_ORDERS);
                            })
                            ->where(function ($q4) {
                                // Exclure le département "Service en chambre"
                                $q4->whereNull('department')
                                    ->orWhere('department', '!=', 'Service en chambre');
                            });
                    });
            })
            ->has('fcmTokens')
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("sendToKitchenStaff: aucun staff cuisine/admin avec FCM token pour enterprise {$enterpriseId}");
            return false;
        }

        return $this->sendToMultipleUsers($staff->toArray(), $title, $body, $data);
    }

    /**
     * Envoyer une notification uniquement aux staff du département "Service en chambre".
     * Les admins reçoivent aussi la notification (ils supervisent tout).
     */
    public function sendToRoomServiceDepartmentStaff($enterpriseId, string $title, string $body, array $data = [])
    {
        $staff = User::where('enterprise_id', $enterpriseId)
            ->where(function ($q) {
                $q->where('role', 'admin')
                    ->orWhere(function ($q2) {
                        $q2->where('role', 'staff')
                            ->where('department', 'Service en chambre');
                    });
            })
            ->has('fcmTokens')
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("sendToRoomServiceDepartmentStaff: aucun staff 'Service en chambre' avec FCM token pour enterprise {$enterpriseId}");
            // Fallback : stocker en base pour polling in-app
            $allStaff = User::where('enterprise_id', $enterpriseId)
                ->whereIn('role', ['admin', 'staff'])
                ->where('department', 'Service en chambre')
                ->get();
            foreach ($allStaff as $u) {
                $this->storeForInAppPolling($u, $title, $body, $data);
            }
            return false;
        }

        return $this->sendToMultipleUsers($staff->toArray(), $title, $body, $data);
    }

    /**
     * Envoyer un message personnalisé au staff
     */
    public function sendToStaff($enterpriseId, string $title, string $body, array $data = [])
    {
        $staff = User::where('enterprise_id', $enterpriseId)
            ->whereIn('role', ['admin', 'staff'])
            ->has('fcmTokens')
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("No staff with FCM tokens found for enterprise {$enterpriseId}");
            return false;
        }

        return $this->sendToMultipleUsers($staff->toArray(), $title, $body, $data);
    }

    /**
     * Envoyer une notification au staff qui gère la section donnée (admin reçoit tout, staff selon managed_sections).
     */
    public function sendToStaffForSection($enterpriseId, string $sectionKey, string $title, string $body, array $data = [])
    {
        $staff = User::where('enterprise_id', $enterpriseId)
            ->whereIn('role', ['admin', 'staff'])
            ->has('fcmTokens')
            ->where(function ($q) use ($sectionKey) {
                $q->where('role', 'admin')
                    ->orWhere(function ($q2) use ($sectionKey) {
                        $q2->where('role', 'staff')
                            ->where(function ($q3) use ($sectionKey) {
                                $q3->whereNull('managed_sections')
                                    ->orWhereJsonContains('managed_sections', $sectionKey);
                            });
                    });
            })
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("No staff with FCM tokens found for enterprise {$enterpriseId} and section {$sectionKey}");
            return false;
        }

        return $this->sendToMultipleUsers($staff->toArray(), $title, $body, $data);
    }
}
