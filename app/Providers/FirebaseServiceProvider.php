<?php

namespace App\Providers;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApiClient as MessagingApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\RequestFactory as MessagingRequestFactory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase', function ($app) {
            $credentialsPath = $this->resolveCredentialsPath(env('FIREBASE_CREDENTIALS'));

            if (! $credentialsPath || ! is_readable($credentialsPath)) {
                $msg = 'Firebase credentials file not found or not readable. FIREBASE_CREDENTIALS=' . (env('FIREBASE_CREDENTIALS') ?? 'null') . ', base_path=' . base_path();
                Log::error($msg);
                throw new \Exception($msg);
            }

            // Toujours utiliser un chemin absolu pour que le SDK et les libs Google trouvent le fichier
            $absolutePath = realpath($credentialsPath) ?: $credentialsPath;
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $absolutePath);
            putenv('FIREBASE_CREDENTIALS=' . $absolutePath);

            $contents = file_get_contents($absolutePath);
            $decoded = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['private_key']) || empty($decoded['client_email'])) {
                Log::error('Firebase credentials file is invalid JSON or missing private_key/client_email.');
                throw new \Exception('Firebase credentials file is invalid. Check that it is the JSON from Firebase Console > Service accounts > Generate new key.');
            }

            Log::info('Firebase credentials loaded', ['path' => $absolutePath, 'project_id' => $decoded['project_id'] ?? 'n/a']);

            // Passer les credentials en tableau pour éviter les problèmes de lecture du fichier
            $factory = (new Factory)->withServiceAccount($decoded);

            $projectId = env('FIREBASE_PROJECT_ID') ?: ($decoded['project_id'] ?? null);
            if ($projectId) {
                $factory = $factory->withProjectId($projectId);
            }

            return $factory;
        });

        // Client Messaging dédié : on récupère un token OAuth2 au chargement et on l'ajoute
        // à chaque requête (avec rafraîchissement si expiré) pour éviter "Request is missing
        // required authentication credential" sur certains hébergements.
        $this->app->singleton('firebase.messaging', function ($app) {
            $factory = $app->make('firebase');
            $credentialsPath = $this->resolveCredentialsPath(env('FIREBASE_CREDENTIALS'));
            if (! $credentialsPath || ! is_readable($credentialsPath)) {
                return $factory->createMessaging();
            }
            $contents = file_get_contents($credentialsPath);
            $decoded = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['private_key']) || empty($decoded['client_email'])) {
                return $factory->createMessaging();
            }
            $projectId = env('FIREBASE_PROJECT_ID') ?: ($decoded['project_id'] ?? null) ?? ($decoded['project_id'] ?? null);
            if (! $projectId) {
                return $factory->createMessaging();
            }

            $credentials = new ServiceAccountCredentials(Factory::API_CLIENT_SCOPES, $decoded);
            $authTokenHandler = HttpHandlerFactory::build(new Client());

            try {
                $tokenData = $credentials->fetchAuthToken($authTokenHandler);
                $accessToken = $tokenData['access_token'] ?? null;
                if (! $accessToken) {
                    Log::warning('Firebase: fetchAuthToken did not return access_token, using default messaging client');
                    return $factory->createMessaging();
                }
                $expiresIn = (int) ($tokenData['expires_in'] ?? 3600);
                $tokenState = [
                    'token' => $accessToken,
                    'expires_at' => time() + $expiresIn - 300,
                ];
                Log::info('Firebase: OAuth2 access token obtained for FCM', ['expires_in' => $expiresIn]);
            } catch (\Throwable $e) {
                Log::warning('Firebase: could not obtain OAuth2 token for FCM: ' . $e->getMessage(), ['exception' => $e]);
                return $factory->createMessaging();
            }

            $httpFactory = new HttpFactory();
            $requestFactory = new MessagingRequestFactory($httpFactory, $httpFactory);

            // Headers par défaut sur le client : chaque requête (y compris via Pool) reçoit le token.
            $messagingHttpClient = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenState['token'],
                ],
            ]);
            $clock = \Beste\Clock\SystemClock::create();
            $errorHandler = new MessagingApiExceptionConverter($clock);
            $messagingApiClient = new MessagingApiClient($messagingHttpClient, $projectId, $requestFactory);
            $appInstanceApiClient = new AppInstanceApiClient(
                $factory->createApiClient([
                    'base_uri' => 'https://iid.googleapis.com',
                    'headers' => ['access_token_auth' => 'true'],
                ]),
                $errorHandler,
            );

            return new \Kreait\Firebase\Messaging($messagingApiClient, $appInstanceApiClient, $errorHandler);
        });
    }

    /**
     * Resolve the credentials file path (absolute, or relative to base_path / storage/app/firebase).
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
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
