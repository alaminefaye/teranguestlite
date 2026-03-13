<?php

namespace App\Models\Traits;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

trait TranslatesAutomatically
{
    /**
     * Langues cibles pour la traduction automatique.
     * La langue source est toujours 'fr'.
     */
    protected array $targetLanguages = ['en', 'es', 'ar'];

    /**
     * Boot le trait : s'attache à l'événement 'saving' d'Eloquent.
     */
    public static function bootTranslatesAutomatically(): void
    {
        static::saving(function (self $model) {
            $model->autoTranslate();
        });
    }

    /**
     * Pour chaque champ translatable, traduit automatiquement
     * si la valeur française a changé ou si la clé 'en' est vide.
     */
    protected function autoTranslate(): void
    {
        if (empty($this->translatable)) {
            return;
        }

        foreach ($this->translatable as $field) {
            $frValue = $this->getTranslation($field, 'fr', false);

            // Pas de valeur française → rien à traduire
            if (empty($frValue)) {
                continue;
            }

            // Traduction nécessaire si :
            // - L'entité n'existe pas encore en base (nouvel enregistrement)
            // - La valeur FR a changé (le champ est dirty)
            // - La traduction EN est vide (données migrées sans traduction)
            $enValue = $this->getTranslation($field, 'en', false);
            $isDirty  = $this->isDirty($field);
            $isNew    = ! $this->exists;
            $noEn     = empty($enValue);

            if (! $isNew && ! $isDirty && ! $noEn) {
                continue;
            }

            foreach ($this->targetLanguages as $lang) {
                try {
                    $translated = GoogleTranslate::trans($frValue, $lang, 'fr');

                    if (! empty($translated)) {
                        $this->setTranslation($field, $lang, $translated);
                    }
                } catch (\Exception $e) {
                    // En cas d'échec de traduction : on log l'erreur mais on ne bloque pas la sauvegarde.
                    Log::warning("TranslatesAutomatically: impossible de traduire le champ [{$field}] vers [{$lang}] pour " . static::class . " — " . $e->getMessage());
                }
            }
        }
    }
}
