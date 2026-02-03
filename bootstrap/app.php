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
        // Enregistrer le middleware pour le filtrage par entreprise
        $middleware->alias([
            'enterprise' => \App\Http\Middleware\EnsureUserBelongsToEnterprise::class,
        ]);
        
        // Ajouter le middleware pour vérifier le changement de mot de passe (global pour web)
        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
