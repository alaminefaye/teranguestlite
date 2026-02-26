<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Masquer / désactiver sans supprimer : is_active = false n'affiche pas dans l'app.
     */
    public function up(): void
    {
        $tables = [
            'leisure_categories',
            'amenity_categories',
            'amenity_items',
            'spa_services',
            'laundry_services',
            'palace_services',
            'excursions',
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_active')->default(true);
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'leisure_categories',
            'amenity_categories',
            'amenity_items',
            'spa_services',
            'laundry_services',
            'palace_services',
            'excursions',
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('is_active'));
            }
        }
    }
};
