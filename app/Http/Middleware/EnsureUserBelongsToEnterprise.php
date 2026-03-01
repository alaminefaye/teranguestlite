<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware SaaS : s'assure que l'utilisateur est bien rattaché à une entreprise
 * avant d'accéder au dashboard. Évite tout accès sans contexte entreprise (données non mélangées).
 */
class EnsureUserBelongsToEnterprise
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Super admin a accès à tout (gestion multi-entreprises)
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Sans enterprise_id, pas d'accès : isolation stricte des données par entreprise
        if ($user && !$user->enterprise_id) {
            abort(403, 'Accès non autorisé : vous n\'êtes associé à aucune entreprise.');
        }

        return $next($request);
    }
}
