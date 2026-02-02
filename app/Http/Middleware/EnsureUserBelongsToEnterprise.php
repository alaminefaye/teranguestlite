<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToEnterprise
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Super admin a accès à tout
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier que l'utilisateur a bien un enterprise_id
        if ($user && !$user->enterprise_id) {
            abort(403, 'Accès non autorisé : vous n\'êtes associé à aucune entreprise.');
        }

        return $next($request);
    }
}
