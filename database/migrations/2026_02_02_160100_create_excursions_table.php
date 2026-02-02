<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // cultural, adventure, relaxation, city_tour
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_child', 10, 2)->nullable();
            $table->integer('duration_hours')->comment('Durée en heures');
            $table->string('departure_time')->nullable(); // Heure de départ
            $table->text('included')->nullable(); // Ce qui est inclus (JSON)
            $table->text('not_included')->nullable(); // Ce qui n'est pas inclus (JSON)
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->nullable();
            $table->enum('status', ['available', 'unavailable', 'seasonal'])->default('available');
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
            $table->index(['enterprise_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursions');
    }
};
