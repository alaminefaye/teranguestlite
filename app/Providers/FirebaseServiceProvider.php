<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

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

            $json = file_get_contents($credentialsPath);
            $credentials = json_decode($json, true);
            if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
                throw new \Exception("Firebase credentials file is invalid or incomplete: {$credentialsPath}");
            }

            Log::info('Firebase initialized with credentials', [
                'path' => $credentialsPath,
                'project_id' => $credentials['project_id'] ?? null,
            ]);

            return (new Factory)->withServiceAccount($credentials);
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
