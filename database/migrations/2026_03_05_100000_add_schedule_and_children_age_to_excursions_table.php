<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->text('schedule_description')->nullable()->after('departure_time')->comment('Horaires et planning détaillé de l\'activité');
            $table->string('children_age_range', 100)->nullable()->after('price_child')->comment('Tranche d\'âge applicable aux enfants (ex: 3-12 ans)');
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn(['schedule_description', 'children_age_range']);
        });
    }
};
