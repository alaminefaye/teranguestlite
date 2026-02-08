<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pour les demandes "Location Voiture" : taxi (lieu + destination + distance)
     * ou location (places, type véhicule, jours, durée).
     */
    public function up(): void
    {
        Schema::table('palace_requests', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('palace_requests', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
