<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE leisure_categories SET name = JSON_OBJECT("fr", name) WHERE JSON_VALID(name) IS FALSE AND name IS NOT NULL');
        DB::statement('UPDATE leisure_categories SET description = JSON_OBJECT("fr", description) WHERE JSON_VALID(description) IS FALSE AND description IS NOT NULL');

        Schema::table('leisure_categories', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leisure_categories', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });
    }
};
