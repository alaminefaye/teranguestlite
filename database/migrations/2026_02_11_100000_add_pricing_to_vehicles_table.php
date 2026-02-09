<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('price_per_day', 12, 2)->nullable()->after('is_available')->comment('Prix pour une journée (FCFA), null = sur demande');
            $table->decimal('price_half_day', 12, 2)->nullable()->after('price_per_day')->comment('Prix demi-journée (FCFA), null = sur demande');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['price_per_day', 'price_half_day']);
        });
    }
};
