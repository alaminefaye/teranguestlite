<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Table pivot pour le ciblage sélectif des annonces super admin.
     * Une ligne = cette annonce (super admin) est diffusée vers cette entreprise.
     * Utilisée uniquement quand target_all_enterprises = false.
     */
    public function up(): void
    {
        Schema::create('announcement_enterprises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enterprise_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['announcement_id', 'enterprise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_enterprises');
    }
};
