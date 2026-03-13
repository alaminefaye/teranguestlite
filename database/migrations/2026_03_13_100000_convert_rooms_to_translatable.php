<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ajouter la colonne type_name (JSON) avant de modifier description
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('type_name')->nullable()->after('type');
        });

        // 2. Migrer la colonne description existante : text → JSON {"fr": "valeur"}
        DB::statement('UPDATE rooms SET description = JSON_OBJECT("fr", description) WHERE description IS NOT NULL AND JSON_VALID(description) = 0');

        // 3. Changer description de text → json
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer description en text (extraire la valeur FR)
        DB::statement('UPDATE rooms SET description = JSON_UNQUOTE(JSON_EXTRACT(description, "$.fr")) WHERE JSON_VALID(description) = 1');

        Schema::table('rooms', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->dropColumn('type_name');
        });
    }
};
