<?php

namespace App\Providers;

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
            // par le SDK (chemin, permissions, open_basedir sur le serveur)
            $factory = (new Factory)->withServiceAccount($decoded);

            $projectId = env('FIREBASE_PROJECT_ID') ?: ($decoded['project_id'] ?? null);
            if ($projectId) {
                $factory = $factory->withProjectId($projectId);
            }

            return $factory;
        });

        $this->app->singleton('firebase.messaging', function ($app) {
            return $app->make('firebase')->createMessaging();
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
