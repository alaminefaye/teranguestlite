<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->decimal('price_adult_eur', 10, 2)->nullable()->after('price_adult')->comment('Prix adulte en Euros (€)');
            $table->decimal('price_child_eur', 10, 2)->nullable()->after('price_child')->comment('Prix enfant en Euros (€)');
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn(['price_adult_eur', 'price_child_eur']);
        });
    }
};
