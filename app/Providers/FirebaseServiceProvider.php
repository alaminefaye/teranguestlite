<?php

namespace App\Providers;

use Google\Auth\Cache\FileSystemCacheItemPool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

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

            // Utiliser le chemin absolu pour éviter tout souci de répertoire de travail (hébergeur, PHP-FPM)
            $absolutePath = realpath($credentialsPath);
            if ($absolutePath === false) {
                throw new \Exception("Firebase credentials path could not be resolved: {$credentialsPath}");
            }

            // Certains composants Google (auth, token) lisent cette variable d'environnement
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $absolutePath);
            $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $absolutePath;

            Log::info('Firebase initialized with credentials', [
                'path' => $absolutePath,
                'project_id' => config('services.firebase.project_id'),
            ]);

            // Cache fichier partagé CLI / PHP-FPM : le cron (firebase:warm-token) remplit le cache,
            // le processus web lit le token sans appeler oauth2.googleapis.com (utile si sortie HTTPS bloquée).
            $oauthCachePath = storage_path('app/firebase/oauth_cache');
            if (! is_dir($oauthCachePath)) {
                @mkdir($oauthCachePath, 0755, true);
            }
            $authTokenCache = new FileSystemCacheItemPool($oauthCachePath);

            return (new Factory)
                ->withServiceAccount($absolutePath)
                ->withAuthTokenCache($authTokenCache);
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
