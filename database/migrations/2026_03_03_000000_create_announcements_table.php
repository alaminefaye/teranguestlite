<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            // Si enterprise_id est null → annonce super admin ; sinon → annonce de l'entreprise
            $table->foreignId('enterprise_id')->nullable()->constrained()->nullOnDelete();

            // Contenu (au moins l'un des deux doit être renseigné)
            $table->string('poster_path')->nullable();
            $table->string('video_path')->nullable();

            // Métadonnées
            $table->string('title')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Planification
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Durée d'affichage pour affiche seule (en minutes, défaut 1)
            $table->unsignedInteger('display_duration_minutes')->default(1);

            // Statistiques
            $table->unsignedBigInteger('view_count')->default(0);

            // Super admin : cibler toutes les entreprises sans exception
            $table->boolean('target_all_enterprises')->default(false);

            // Qui a créé l'annonce
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['enterprise_id', 'is_active']);
            $table->index(['target_all_enterprises', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
