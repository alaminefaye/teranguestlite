<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    /**
     * Langues supportées par l'application.
     */
    protected const SUPPORTED_LOCALES = ['fr', 'en', 'es', 'ar'];

    /**
     * Langue par défaut si aucun header/paramètre valide n'est trouvé.
     */
    protected const DEFAULT_LOCALE = 'fr';

    /**
     * Lit le header Accept-Language (ou le paramètre ?lang=) et définit la locale de l'app.
     * Cela permet à Spatie\Translatable de retourner automatiquement la bonne traduction.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priorité : header Accept-Language > paramètre query ?lang=
        $lang = $request->header('Accept-Language')
            ?? $request->query('lang', self::DEFAULT_LOCALE);

        // Extraire la 1ère langue du header (ex: "en-US,en;q=0.9" → "en")
        if (str_contains($lang, ',')) {
            $lang = explode(',', $lang)[0];
        }

        // Normaliser (ex: "en-US" → "en")
        if (str_contains($lang, '-')) {
            $lang = strtolower(explode('-', $lang)[0]);
        }

        $lang = strtolower(trim($lang));

        $locale = in_array($lang, self::SUPPORTED_LOCALES, true)
            ? $lang
            : self::DEFAULT_LOCALE;

        app()->setLocale($locale);

        return $next($request);
    }
}
