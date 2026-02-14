<?php

namespace App\Providers;

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
                throw new \Exception("Firebase credentials file not found or not readable: " . (env('FIREBASE_CREDENTIALS') ?? 'FIREBASE_CREDENTIALS not set'));
            }

            // So that Google Auth libraries use the same credentials
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $factory = (new Factory)->withServiceAccount($credentialsPath);

            $projectId = env('FIREBASE_PROJECT_ID');
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

        if (realpath($path) !== false) {
            return realpath($path);
        }

        $fromBase = base_path($path);
        if (is_readable($fromBase)) {
            return realpath($fromBase);
        }

        $fromStorage = storage_path('app/firebase/' . basename($path));
        if (is_readable($fromStorage)) {
            return realpath($fromStorage);
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
