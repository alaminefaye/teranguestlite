<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables avec uniquement name à convertir.
     */
    private array $nameOnlyTables = [
        'vehicles',
        'amenity_categories',
        'amenity_items',
        'guide_categories',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tables avec uniquement name
        foreach ($this->nameOnlyTables as $table) {
            DB::statement("UPDATE `{$table}` SET name = JSON_OBJECT('fr', name) WHERE name IS NOT NULL AND JSON_VALID(name) = 0");

            Schema::table($table, function (Blueprint $table_blueprint) {
                $table_blueprint->json('name')->nullable()->change();
            });
        }

        // 2. guide_items : title + description
        DB::statement("UPDATE guide_items SET title = JSON_OBJECT('fr', title) WHERE title IS NOT NULL AND JSON_VALID(title) = 0");
        DB::statement("UPDATE guide_items SET description = JSON_OBJECT('fr', description) WHERE description IS NOT NULL AND JSON_VALID(description) = 0");

        Schema::table('guide_items', function (Blueprint $table) {
            $table->json('title')->nullable()->change();
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restaurer les tables name-only
        foreach ($this->nameOnlyTables as $table) {
            DB::statement("UPDATE `{$table}` SET name = JSON_UNQUOTE(JSON_EXTRACT(name, '$.fr')) WHERE JSON_VALID(name) = 1");

            Schema::table($table, function (Blueprint $table_blueprint) {
                $table_blueprint->string('name')->change();
            });
        }

        // 2. Restaurer guide_items
        DB::statement("UPDATE guide_items SET title = JSON_UNQUOTE(JSON_EXTRACT(title, '$.fr')) WHERE JSON_VALID(title) = 1");
        DB::statement("UPDATE guide_items SET description = JSON_UNQUOTE(JSON_EXTRACT(description, '$.fr')) WHERE JSON_VALID(description) = 1");

        Schema::table('guide_items', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('description')->nullable()->change();
        });
    }
};
