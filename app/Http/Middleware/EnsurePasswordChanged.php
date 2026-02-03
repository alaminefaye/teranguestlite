<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (auth()->check()) {
            $user = auth()->user();
            
            // Si l'utilisateur doit changer son mot de passe
            if ($user->must_change_password) {
                // Liste des routes exemptées (où on ne redirige pas)
                $exemptedRoutes = [
                    'auth/change-password',  // Page et soumission du formulaire
                    'logout',                 // Déconnexion
                ];
                
                // Vérifier si on n'est pas sur une route exemptée
                $isExempted = false;
                foreach ($exemptedRoutes as $route) {
                    if ($request->is($route)) {
                        $isExempted = true;
                        break;
                    }
                }
                
                // Rediriger vers la page de changement de mot de passe
                if (!$isExempted) {
                    return redirect()->route('auth.change-password.form')
                        ->with('warning', 'Vous devez changer votre mot de passe avant de continuer.');
                }
            }
        }
        
        return $next($request);
    }
}
