<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trace les requêtes vers la page création réservation pour déboguer les 500 sans log.
 */
class LogReservationCreateDebug
{
    public function handle(Request $request, Closure $next): Response
    {
        $log = storage_path('logs/reservation-create-debug.log');
        @file_put_contents($log, date('c') . " [MIDDLEWARE] ENTER reservations/create\n", FILE_APPEND);
        return $next($request);
    }
}
