<?php

namespace App\Providers;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
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

        // Client Messaging dédié : credentials sans cache + auth explicite pour éviter
        // "Request is missing required authentication credential" (Pool Guzzle / middleware).
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
            $handler = HandlerStack::create();
            $handler->push(new AuthTokenMiddleware($credentials, $authTokenHandler));
            $messagingHttpClient = new Client([
                'handler' => $handler,
                'auth' => 'google_auth',
            ]);

            $httpFactory = new HttpFactory();
            $requestFactory = new MessagingRequestFactory($httpFactory, $httpFactory);
            $clock = new \Beste\Clock\SystemClock();
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
