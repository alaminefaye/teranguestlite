<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Éviter les 500 "memory exhausted" sur les pages qui chargent beaucoup de données
if (function_exists('ini_set')) {
    @ini_set('memory_limit', '512M');
}

// Capturer les erreurs fatales (memory, parse, etc.) même si les logs Laravel sont vides
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        $logDir = __DIR__ . '/../storage/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            $line = date('c') . ' [FATAL] ' . ($err['message'] ?? '') . ' in ' . ($err['file'] ?? '') . ' line ' . ($err['line'] ?? '') . "\n";
            @file_put_contents($logDir . '/fatal.log', $line, FILE_APPEND | LOCK_EX);
        }
    }
});

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
