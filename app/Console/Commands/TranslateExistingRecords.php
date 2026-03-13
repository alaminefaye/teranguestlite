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
    protected $signature = 'translate:backfill {--model= : Modèle (ex: SpaService)} {--dry-run : Afficher sans sauvegarder} {--force : Tout retraduire depuis le FR}';

    /**
     * The console command description.
     */
    protected $description = 'Traduit automatiquement EN/ES/AR les enregistrements existants (--force pour tout retraduire).';

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
        $this->info('Démarrage translate:backfill...');
        $this->output->write('', true);

        $targetLanguages = ['en', 'es', 'ar'];
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $onlyModel = $this->option('model');

        if ($dryRun) {
            $this->warn('Mode DRY-RUN activé : aucune donnée ne sera sauvegardée.');
        }
        if ($force) {
            $this->warn('Mode FORCE activé : toutes les traductions EN/ES/AR seront réécrites depuis le français.');
        }

        try {
            $this->runBackfill($targetLanguages, $dryRun, $force, $onlyModel);
        } catch (\Throwable $e) {
            $this->error('Erreur : ' . $e->getMessage());
            Log::error('translate:backfill exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }

        $this->info('✅ Rétro-traduction terminée !');
        return self::SUCCESS;
    }

    protected function runBackfill(array $targetLanguages, bool $dryRun, bool $force, ?string $onlyModel): void
    {
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

                    // Sans --force : si la traduction EN est déjà présente, on passe
                    if (! $force) {
                        $enValue = $record->getTranslation($field, 'en', false);
                        if (! empty($enValue)) {
                            continue;
                        }
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
    }
}
