<?php

namespace App\Http\Helpers;

/**
 * Pour l'API mobile/tablette : renvoyer les champs traduisibles sous forme
 * de tableau { fr: "...", en: "...", es: "...", ar: "..." } pour que l'app
 * puisse afficher la langue choisie ou traduire côté client si une traduction manque.
 */
final class TranslatableApiHelper
{
    public const LOCALES = ['fr', 'en', 'es', 'ar'];

    /**
     * Retourne les traductions d'un attribut (Spatie).
     * Si le modèle n'a pas getTranslations, retourne [ 'fr' => $value ] pour compatibilité.
     */
    public static function translationsFor(object $model, string $attribute): array
    {
        if (! method_exists($model, 'getTranslations')) {
            $value = $model->{$attribute} ?? '';
            return array_fill_keys(self::LOCALES, is_string($value) ? $value : '');
        }

        $raw = $model->getTranslations($attribute);
        $out = [];
        foreach (self::LOCALES as $locale) {
            $out[$locale] = isset($raw[$locale]) && $raw[$locale] !== '' && $raw[$locale] !== null
                ? (string) $raw[$locale]
                : null;
        }
        return $out;
    }
}
