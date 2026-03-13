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
        // Migrer title (string → JSON {"fr": "valeur"})
        DB::statement("UPDATE announcements SET title = JSON_OBJECT('fr', title) WHERE title IS NOT NULL AND JSON_VALID(title) = 0");

        Schema::table('announcements', function (Blueprint $table) {
            $table->json('title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE announcements SET title = JSON_UNQUOTE(JSON_EXTRACT(title, '$.fr')) WHERE JSON_VALID(title) = 1");

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });
    }
};
