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
                // Ne pas rediriger si on est déjà sur la page de changement de mot de passe
                if (!$request->is('auth/change-password') && !$request->is('logout')) {
                    return redirect()->route('auth.change-password.form')
                        ->with('warning', 'Vous devez changer votre mot de passe avant de continuer.');
                }
            }
        }
        
        return $next($request);
    }
}
