<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TestFirebaseCredentials extends Command
{
    protected $signature = 'fcm:test
                            {--user= : User ID with fcm_token to send a test notification}
                            {--token : Tester uniquement la génération du token OAuth2}
                            {--curl : Affiche une commande curl pour tester FCM depuis ce serveur (avec --user=ID pour le device token)}
                            {--curl-oneline : Même que --curl mais sur une seule ligne (copier-coller direct)}';

    protected $description = 'Vérifie le chargement des credentials Firebase et optionnellement envoie une notification test';

    public function handle(): int
    {
        $this->info('Vérification Firebase...');

        // Test OAuth2 token generation first
        if ($this->option('token')) {
            return $this->testOAuthTokenGeneration();
        }

        // Check credentials file (config comme gestion-compagny)
        $credentialsPath = config('services.firebase.credentials');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            $this->error('Fichier de credentials Firebase non trouvé.');
            $this->line('Chemin configuré: ' . ($credentialsPath ?? 'null'));
            $this->line('Vérifiez FIREBASE_CREDENTIALS_PATH dans .env (ex. app/firebase/teranguest-74262-xxx.json).');
            return 1;
        }
        $this->info('Credentials trouvés: ' . $credentialsPath);

        // Verify JSON content
        $contents = file_get_contents($credentialsPath);
        $decoded = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Le fichier credentials n\'est pas un JSON valide.');
            return 1;
        }
        if (empty($decoded['project_id']) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            $this->error('Le fichier credentials est incomplet (manque project_id, client_email ou private_key).');
            return 1;
        }
        $this->info('Project ID: ' . $decoded['project_id']);
        $this->info('Client Email: ' . $decoded['client_email']);

        // Test OAuth2 token generation
        $this->info('Test de génération du token OAuth2...');
        $service = app(FirebaseNotificationService::class);
        
        // Use reflection to call protected method
        $reflection = new \ReflectionMethod($service, 'getAccessToken');
        $reflection->setAccessible(true);
        $accessToken = $reflection->invoke($service);
        
        if (!$accessToken) {
            $this->error('Échec de la génération du token OAuth2.');
            $this->line('Vérifiez les logs pour plus de détails: tail -f storage/logs/laravel.log');
            return 1;
        }
        $this->info('Token OAuth2 généré avec succès!');

        if ($this->option('curl') || $this->option('curl-oneline')) {
            return $this->outputCurlCommand($decoded['project_id'], $accessToken, $this->option('curl-oneline'));
        }

        $userId = $this->option('user');
        if ($userId) {
            $user = User::find($userId);
            if (! $user || empty($user->fcm_token)) {
                $this->warn("User {$userId} non trouvé ou sans fcm_token. Utilisez un ID d'utilisateur qui a un token (ex. compte tablette connecté).");
                return 1;
            }
            $this->info("Envoi d'une notification test à l'utilisateur {$user->id} ({$user->name})...");
            $this->info("FCM Token: " . substr($user->fcm_token, 0, 30) . "...");
            
            try {
                $sent = $service->sendToUser(
                    $user,
                    'Test TerangaGuest',
                    'Si vous voyez ce message, les notifications push sont opérationnelles.'
                );
                if ($sent) {
                    $this->info('Notification envoyée avec succès!');
                } else {
                    $this->warn('Envoi a échoué (voir storage/logs/laravel.log pour les détails).');
                }
            } catch (\Throwable $e) {
                $this->error('Erreur envoi: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->line('');
            $this->line('Commandes disponibles:');
            $this->line('  php artisan fcm:test --token        # Tester uniquement la génération du token OAuth2');
            $this->line('  php artisan fcm:test --user=6       # Envoyer une notification test à l\'utilisateur 6');
            $this->line('  php artisan fcm:test --curl --user=6 # Afficher une commande curl de test');
        }

        return 0;
    }

    /**
     * Test OAuth2 token generation
     */
    private function testOAuthTokenGeneration(): int
    {
        $credentialsPath = config('services.firebase.credentials');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            $this->error('Fichier de credentials non trouvé. Vérifiez FIREBASE_CREDENTIALS_PATH dans .env.');
            return 1;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        $clientEmail = $credentials['client_email'];
        $privateKey = $credentials['private_key'];
        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        $this->info('Génération du JWT...');

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

        if (!openssl_sign($signatureInput, $signature, $privateKey, 'SHA256')) {
            $this->error('Échec de la signature du JWT. Vérifiez que la clé privée est valide.');
            return 1;
        }

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $signatureInput . '.' . $base64Signature;

        $this->info('JWT généré avec succès.');
        $this->info('Échange du JWT contre un access token...');

        // Exchange JWT for access token
        $response = Http::asForm()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->successful()) {
            $tokenData = $response->json();
            $this->info('Token OAuth2 obtenu avec succès!');
            $this->info('Token (premiers 50 caractères): ' . substr($tokenData['access_token'], 0, 50) . '...');
            $this->info('Expires in: ' . $tokenData['expires_in'] . ' secondes');
            $this->info('Token type: ' . $tokenData['token_type']);
            return 0;
        } else {
            $this->error('Échec de l\'obtention du token OAuth2.');
            $this->error('Status: ' . $response->status());
            $this->error('Response: ' . $response->body());
            return 1;
        }
    }

    /**
     * Affiche une commande curl prête à l'emploi pour tester FCM depuis ce serveur.
     * Si curl renvoie 200 → le proxy ne touche pas à Authorization vers Google ; sinon (401) → proxy ou règle ciblant googleapis.com.
     */
    private function outputCurlCommand(string $projectId, string $accessToken, bool $oneline = false): int
    {
        $userId = $this->option('user');
        $deviceToken = null;
        if ($userId) {
            $user = User::find($userId);
            if ($user && ! empty($user->fcm_token)) {
                $deviceToken = $user->fcm_token;
            }
        }
        if (! $deviceToken) {
            $deviceToken = '<COLLER_ICI_LE_FCM_TOKEN_DEVICE>';
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $body = json_encode([
            'message' => [
                'token' => $deviceToken,
                'notification' => ['title' => 'Test curl', 'body' => 'Si vous voyez ceci, FCM fonctionne depuis ce serveur.'],
            ],
        ]);

        $curlOne = 'curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "' . $url . '" -H "Authorization: Bearer ' . $accessToken . '" -H "Content-Type: application/json" -d \'' . $body . '\'';

        if ($oneline) {
            $this->line('');
            $this->line($curlOne);
            $this->line('');
            return 0;
        }

        $this->line('');
        $this->info('Commande curl à exécuter sur CE serveur (test direct vers FCM) :');
        $this->line('');
        $this->line('curl -s -w "\\nHTTP_CODE:%{http_code}" -X POST "' . $url . '" \\');
        $this->line('  -H "Authorization: Bearer ' . $accessToken . '" \\');
        $this->line('  -H "Content-Type: application/json" \\');
        $this->line("  -d '" . $body . "'");
        $this->line('');
        $this->comment('Si HTTP_CODE:200 → FCM reçoit bien le header ; le souci vient peut-être de PHP/Guzzle.');
        $this->comment('Si HTTP_CODE:401 → Authorization est supprimé ou refusé vers fcm.googleapis.com (proxy/règle).');
        $this->line('');

        return 0;
    }
}
