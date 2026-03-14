<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enregistrer les middlewares aliasés
        $middleware->alias([
            'enterprise' => \App\Http\Middleware\EnsureUserBelongsToEnterprise::class,
            'guest.web_stay' => \App\Http\Middleware\EnsureGuestWebTokenMatchesStay::class,
            'log.reservation.create' => \App\Http\Middleware\LogReservationCreateDebug::class,
        ]);

        // Ajouter le middleware pour vérifier le changement de mot de passe (global pour web)
        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);

        // Pour l'API : vérifier, pour les invités connectés via le Web,
        // que le token correspond bien à un séjour actif de la chambre.
        $middleware->api(prepend: [
            'guest.web_stay',
            \App\Http\Middleware\SetLocaleFromHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Toujours écrire les exceptions dans un fichier dédié (au cas où laravel.log est vide / permissions)
        $exceptions->report(function (\Throwable $e) {
            $logDir = storage_path('logs');
            if (!is_dir($logDir) || !is_writable($logDir)) {
                return;
            }
            $file = $logDir . '/debug_500.log';
            $line = date('c') . ' ' . $e->getMessage() . "\n  at " . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n---\n";
            @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        });
    })->create();
