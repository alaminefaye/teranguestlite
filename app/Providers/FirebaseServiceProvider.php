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
            $credentialsPath = config('services.firebase.credentials');

            if (! $credentialsPath || ! is_readable($credentialsPath)) {
                $msg = 'Firebase credentials file not found or not readable. FIREBASE_CREDENTIALS_PATH=' . (env('FIREBASE_CREDENTIALS_PATH') ?? 'null') . ', resolved=' . ($credentialsPath ?? 'null');
                Log::error($msg);
                throw new \Exception($msg);
            }

            $absolutePath = realpath($credentialsPath) ?: $credentialsPath;
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $absolutePath);

            $contents = file_get_contents($absolutePath);
            $decoded = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['private_key']) || empty($decoded['client_email'])) {
                Log::error('Firebase credentials file is invalid JSON or missing private_key/client_email.');
                throw new \Exception('Firebase credentials file is invalid. Check that it is the JSON from Firebase Console > Service accounts > Generate new key.');
            }

            Log::info('Firebase credentials loaded', ['path' => $absolutePath, 'project_id' => $decoded['project_id'] ?? 'n/a']);

            $factory = (new Factory)->withServiceAccount($absolutePath);

            $projectId = config('services.firebase.project_id') ?: ($decoded['project_id'] ?? null);
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
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
