<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateExistingRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'translate:backfill
                            {--model= : Nom du modèle spécifique à traduire (ex: SpaService)}
                            {--dry-run : Afficher ce qui serait traduit sans sauvegarder}';

    /**
     * The console command description.
     */
    protected $description = 'Traduit automatiquement EN/ES/AR les enregistrements existants dont la traduction anglaise est vide.';

    /**
     * Modèles à traiter avec leurs champs translatables.
     */
    protected array $models = [
        \App\Models\PalaceService::class,
        \App\Models\LaundryService::class,
        \App\Models\SpaService::class,
        \App\Models\Excursion::class,
        \App\Models\Restaurant::class,
        \App\Models\MenuCategory::class,
        \App\Models\MenuItem::class,
        \App\Models\Vehicle::class,
        \App\Models\AmenityCategory::class,
        \App\Models\AmenityItem::class,
        \App\Models\GuideCategory::class,
        \App\Models\GuideItem::class,
        \App\Models\Announcement::class,
        \App\Models\LeisureCategory::class,
        \App\Models\Room::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetLanguages = ['en', 'es', 'ar'];
        $dryRun = $this->option('dry-run');
        $onlyModel = $this->option('model');

        if ($dryRun) {
            $this->warn('Mode DRY-RUN activé : aucune donnée ne sera sauvegardée.');
        }

        foreach ($this->models as $modelClass) {
            $shortName = class_basename($modelClass);

            // Filtrer si --model spécifié
            if ($onlyModel && strtolower($shortName) !== strtolower($onlyModel)) {
                continue;
            }

            $instance = new $modelClass;

            if (! property_exists($instance, 'translatable') || empty($instance->translatable)) {
                continue;
            }

            $this->info("Traitement de {$shortName}...");

            // Désactiver le scope enterprise pour traiter tous les enregistrements
            $records = $modelClass::withoutGlobalScopes()->get();
            $bar     = $this->output->createProgressBar($records->count());
            $bar->start();

            $translatedCount = 0;

            foreach ($records as $record) {
                $needsSave = false;

                foreach ($instance->translatable as $field) {
                    $frValue = $record->getTranslation($field, 'fr', false);

                    // Si pas de valeur FR, on passe
                    if (empty($frValue)) {
                        continue;
                    }

                    // Si la traduction EN est déjà présente, on passe
                    $enValue = $record->getTranslation($field, 'en', false);
                    if (! empty($enValue)) {
                        continue;
                    }

                    // Traduire vers toutes les langues cibles
                    foreach ($targetLanguages as $lang) {
                        try {
                            $translated = GoogleTranslate::trans($frValue, $lang, 'fr');
                            if (! empty($translated)) {
                                if (! $dryRun) {
                                    $record->setTranslation($field, $lang, $translated);
                                } else {
                                    $this->line("\n  [{$shortName} #{$record->id}] {$field}.{$lang}: \"{$translated}\"");
                                }
                                $needsSave = true;
                            }
                        } catch (\Exception $e) {
                            $this->warn("\n  Erreur traduction [{$shortName}#{$record->id}][{$field}→{$lang}]: " . $e->getMessage());
                            Log::warning("translate:backfill error: " . $e->getMessage());
                        }
                    }
                }

                if ($needsSave && ! $dryRun) {
                    // Sauvegarder sans déclencher le trait (éviter une double traduction)
                    $record->saveQuietly();
                    $translatedCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("  → {$translatedCount} enregistrement(s) traduit(s) sur {$records->count()}.");
        }

        $this->info('✅ Rétro-traduction terminée !');

        return self::SUCCESS;
    }
}
