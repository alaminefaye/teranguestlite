<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables avec name + description à convertir en JSON.
     */
    private array $tables = [
        'palace_services',
        'laundry_services',
        'spa_services',
        'excursions',
        'restaurants',
        'menu_categories',
        'menu_items',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            // Migrer name (string → JSON)
            DB::statement("UPDATE `{$table}` SET name = JSON_OBJECT('fr', name) WHERE name IS NOT NULL AND JSON_VALID(name) = 0");

            // Migrer description (text → JSON) si la colonne existe
            if (Schema::hasColumn($table, 'description')) {
                DB::statement("UPDATE `{$table}` SET description = JSON_OBJECT('fr', description) WHERE description IS NOT NULL AND JSON_VALID(description) = 0");
            }

            Schema::table($table, function (Blueprint $table_blueprint) use ($table) {
                $table_blueprint->json('name')->nullable()->change();

                if (Schema::hasColumn($table, 'description')) {
                    $table_blueprint->json('description')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            // Restaurer name (JSON → string)
            DB::statement("UPDATE `{$table}` SET name = JSON_UNQUOTE(JSON_EXTRACT(name, '$.fr')) WHERE JSON_VALID(name) = 1");

            // Restaurer description (JSON → text) si la colonne existe
            if (Schema::hasColumn($table, 'description')) {
                DB::statement("UPDATE `{$table}` SET description = JSON_UNQUOTE(JSON_EXTRACT(description, '$.fr')) WHERE JSON_VALID(description) = 1");
            }

            Schema::table($table, function (Blueprint $table_blueprint) use ($table) {
                $table_blueprint->string('name')->change();

                if (Schema::hasColumn($table, 'description')) {
                    $table_blueprint->text('description')->nullable()->change();
                }
            });
        }
    }
};
