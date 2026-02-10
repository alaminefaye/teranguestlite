<?php

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('firebase:check', function () {
    $envValue = config('services.firebase.credentials');
    $this->info('=== Configuration Firebase (ce que Laravel utilise) ===');
    $this->line('FIREBASE_CREDENTIALS (config): ' . ($envValue ?: '(vide)'));
    $path = $envValue
        ? (str_starts_with($envValue, '/') ? $envValue : base_path($envValue))
        : base_path('firebase-credentials.json');
    $this->line('Chemin résolu (absolu): ' . $path);
    $this->line('Fichier existe: ' . (is_file($path) ? 'Oui' : 'Non'));
    $this->line('Fichier lisible: ' . (is_readable($path) ? 'Oui' : 'Non'));
    if (is_file($path) && is_readable($path)) {
        $json = @json_decode(file_get_contents($path), true);
        if ($json && isset($json['project_id'])) {
            $this->line('Project ID (dans le fichier): ' . $json['project_id']);
            $this->line('Client email: ' . ($json['client_email'] ?? '(absent)'));
            $this->info('OK — Le fichier est valide. Si les notifications échouent encore, exécutez: php artisan config:clear puis php artisan config:cache');
        } else {
            $this->error('Le fichier JSON ne contient pas project_id ou est invalide.');
        }
    } else {
        $this->error('Corrigez le chemin dans .env (ex: FIREBASE_CREDENTIALS=storage/app/firebase/teranguest-74262-844fbd9b5264.json) puis: php artisan config:clear');
    }
})->purpose('Vérifier la configuration Firebase et le fichier credentials');

Artisan::command('firebase:test-token', function () {
    $envValue = config('services.firebase.credentials');
    $path = $envValue
        ? (str_starts_with($envValue, '/') ? $envValue : base_path($envValue))
        : base_path('firebase-credentials.json');
    $absolutePath = is_file($path) ? realpath($path) : $path;

    $this->info('Test d\'obtention d\'un token OAuth2 (comme pour FCM)...');
    $this->line('Fichier: ' . $absolutePath);

    try {
        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $absolutePath
        );
        $token = $credentials->fetchAuthToken();
        if (! empty($token['access_token'])) {
            $prefix = substr($token['access_token'], 0, 20) . '...';
            $this->info('OK — Token OAuth2 obtenu. Préfixe: ' . $prefix);
            $this->line('Si les notifications échouent encore, le problème vient d\'ailleurs (ex: requête FCM qui n\'attache pas le token).');
        } else {
            $this->warn('Token vide ou sans access_token: ' . json_encode(array_keys($token ?? [])));
        }
    } catch (\Throwable $e) {
        $this->error('ÉCHEC — Impossible d\'obtenir un token OAuth2.');
        $this->line('');
        $this->line('Message: ' . $e->getMessage());
        $this->line('Fichier: ' . $e->getFile() . ':' . $e->getLine());
        $this->line('');
        $this->line('Causes fréquentes sur hébergement partagé:');
        $this->line('  - Connexion sortante vers oauth2.googleapis.com bloquée (firewall)');
        $this->line('  - PHP sans accès réseau ou SSL désactivé');
        $this->line('  - open_basedir / restrictions empêchant la lecture du fichier');
        $this->line('');
        $this->line('Contacter l\'hébergeur pour autoriser les appels HTTPS vers:');
        $this->line('  https://oauth2.googleapis.com  et  https://fcm.googleapis.com');
    }
})->purpose('Tester si le serveur peut obtenir un token OAuth2 pour Firebase');

Artisan::command('firebase:warm-token', function () {
    $this->info('Préchauffage du cache OAuth2 (partagé avec le processus web)...');

    try {
        $messaging = app('firebase.messaging');
        // Déclenche une requête authentifiée vers FCM → le token est récupéré et mis en cache fichier.
        $messaging->validateRegistrationTokens('warm');
    } catch (\Throwable $e) {
        // On attend une erreur "invalid token" ; l’important est que le token ait été mis en cache.
        if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid')) {
            $this->info('OK — Cache OAuth2 rempli (erreur de validation attendue pour le token de test).');
            $this->line('Planifiez cette commande toutes les 50 min (cron) pour que le web utilise toujours un token valide.');
        } else {
            $this->warn('Cache peut-être rempli ; erreur: ' . $e->getMessage());
        }
    }

    // Secours pour le web : fichier token que PHP-FPM lit pour Authorization
    $envValue = config('services.firebase.credentials');
    $path = $envValue ? (str_starts_with($envValue, '/') ? $envValue : base_path($envValue)) : base_path('firebase-credentials.json');
    $absolutePath = is_file($path) ? realpath($path) : $path;
    try {
        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $absolutePath
        );
        $token = $credentials->fetchAuthToken();
        if (! empty($token['access_token'])) {
            $expiresIn = (int) ($token['expires_in'] ?? 3600);
            $expiresAt = time() + $expiresIn - 60;
            $dir = storage_path('app/firebase');
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file = $dir . '/access_token.json';
            file_put_contents($file, json_encode([
                'access_token' => $token['access_token'],
                'expires_at' => $expiresAt,
            ], JSON_UNESCAPED_SLASHES));
            $this->info('OK — Token écrit dans ' . $file . ' (lu par le web pour FCM).');
        }
    } catch (\Throwable $e) {
        $this->warn('Impossible d\'écrire le fichier token : ' . $e->getMessage());
    }
    $this->line('Planifiez cette commande toutes les 50 min (cron) pour que le web utilise toujours un token valide.');
})->purpose('Remplir le cache OAuth2 (CLI) pour que le processus web n’ait pas à appeler oauth2.googleapis.com');
