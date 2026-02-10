<?php

namespace App\Providers;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Psr\Http\Message\RequestInterface;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase', function ($app) {
            $credentialsPath = $this->resolveCredentialsPath(config('services.firebase.credentials'));

            if (! is_file($credentialsPath) || ! is_readable($credentialsPath)) {
                throw new \Exception(
                    "Firebase credentials must be a readable JSON file. Got: {$credentialsPath}. " .
                    "Set FIREBASE_CREDENTIALS to the file path (e.g. storage/app/firebase/teranguest-74262-844fbd9b5264.json)."
                );
            }

            $absolutePath = realpath($credentialsPath);
            if ($absolutePath === false) {
                throw new \Exception("Firebase credentials path could not be resolved: {$credentialsPath}");
            }

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $absolutePath);
            $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $absolutePath;

            Log::info('Firebase initialized with credentials', [
                'path' => $absolutePath,
                'project_id' => config('services.firebase.project_id'),
            ]);

            // On ajoute nous-mêmes le token OAuth2 à chaque requête FCM (comme en CLI).
            // 1) Fichier en cache si valide, 2) sinon appel Google, puis on met en cache pour la suite.
            $credentialsPathForMiddleware = $absolutePath;
            $bearerMiddleware = function (callable $handler) use ($credentialsPathForMiddleware) {
                return function (RequestInterface $request, array $options) use ($handler, $credentialsPathForMiddleware) {
                    if ($request->hasHeader('Authorization')) {
                        return $handler($request, $options);
                    }
                    $token = null;
                    $file = storage_path('app/firebase/access_token.json');
                    if (is_file($file) && is_readable($file)) {
                        $data = @json_decode((string) file_get_contents($file), true);
                        if ($data && ! empty($data['access_token']) && isset($data['expires_at']) && (int) $data['expires_at'] > time() + 60) {
                            $token = $data['access_token'];
                        }
                    }
                    if (! $token) {
                        try {
                            $creds = new ServiceAccountCredentials(
                                ['https://www.googleapis.com/auth/firebase.messaging'],
                                $credentialsPathForMiddleware
                            );
                            $auth = $creds->fetchAuthToken();
                            if (! empty($auth['access_token'])) {
                                $token = $auth['access_token'];
                                $expiresIn = (int) ($auth['expires_in'] ?? 3600);
                                $dir = \dirname($file);
                                if (! is_dir($dir)) {
                                    @mkdir($dir, 0755, true);
                                }
                                @file_put_contents($file, json_encode([
                                    'access_token' => $token,
                                    'expires_at' => time() + $expiresIn - 60,
                                ], JSON_UNESCAPED_SLASHES));
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Firebase: could not get OAuth token', ['message' => $e->getMessage()]);
                        }
                    }
                    if ($token) {
                        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
                    }
                    return $handler($request, $options);
                };
            };

            $httpOptions = HttpClientOptions::default()->withGuzzleMiddleware($bearerMiddleware, 'firebase_bearer');

            return (new Factory)
                ->withServiceAccount($absolutePath)
                ->withHttpClientOptions($httpOptions);
        });

        $this->app->singleton('firebase.messaging', function ($app) {
            return $app->make('firebase')->createMessaging();
        });
    }

    /**
     * Resolve the path to the Firebase credentials JSON file.
     * If the env value points to a directory, looks for a .json file inside it.
     */
    private function resolveCredentialsPath(?string $envValue): string
    {
        $path = $envValue
            ? (str_starts_with($envValue, '/') ? $envValue : base_path($envValue))
            : base_path('firebase-credentials.json');

        if (is_file($path)) {
            return $path;
        }

        if (is_dir($path)) {
            $candidates = ['credentials.json', 'service-account.json'];
            foreach ($candidates as $name) {
                $file = rtrim($path, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $name;
                if (is_file($file) && is_readable($file)) {
                    return $file;
                }
            }
            $json = glob($path . \DIRECTORY_SEPARATOR . '*.json');
            $first = $json[0] ?? null;
            if ($first && is_readable($first)) {
                return $first;
            }
        }

        return $path;
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
