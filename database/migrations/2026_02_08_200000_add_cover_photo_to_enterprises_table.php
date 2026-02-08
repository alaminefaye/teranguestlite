<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Image de couverture pour l'accueil app (grande photo en fond), distincte du logo.
     */
    public function up(): void
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->string('cover_photo')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->dropColumn('cover_photo');
        });
    }
};
