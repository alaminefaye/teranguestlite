<?php

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
        $this->error('Corrigez le chemin dans .env (ex: FIREBASE_CREDENTIALS=storage/app/teranguest-74262-844fbd9b5264.json) puis: php artisan config:clear');
    }
})->purpose('Vérifier la configuration Firebase et le fichier credentials');
