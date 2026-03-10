<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\GuestReservationHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pour les invités connectés via le Web (token Sanctum \"web-app\"),
 * s'assure que le token correspond bien à un séjour actif de la chambre.
 *
 * Objectif : éviter qu'un ancien client, toujours connecté sur le Web,
 * voie ou agisse sur le séjour d'un nouveau client dans la même chambre.
 */
class EnsureGuestWebTokenMatchesStay
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user || ! $user->isGuest()) {
            return $next($request);
        }

        $token = $user->currentAccessToken();
        if (! $token || $token->name !== 'web-app') {
            // On ne restreint que les tokens web-app des invités
            return $next($request);
        }

        $stay = GuestReservationHelper::activeStayForUser($user);

        // Aucun séjour actif : le token web ne doit plus permettre d'accéder aux données de chambre
        if (! $stay) {
            $token->delete();

            return response()->json([
                'success' => false,
                'message' => 'Votre séjour est terminé ou expiré. Scannez à nouveau le QR code de votre chambre pour vous reconnecter.',
            ], 401);
        }

        $reservation = $stay['reservation'];

        // Si le token a été créé avant le début du séjour actif courant,
        // cela signifie qu'il vient probablement d’un ancien client : on l’invalide.
        if ($token->created_at && $token->created_at->lt($reservation->check_in)) {
            $token->delete();

            return response()->json([
                'success' => false,
                'message' => 'Votre accès web a expiré. Scannez à nouveau le QR code ou entrez votre nouveau code client.',
            ], 401);
        }

        return $next($request);
    }
}

