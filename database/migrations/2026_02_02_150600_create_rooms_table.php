<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('room_number')->unique();
            $table->integer('floor')->nullable();
            $table->enum('type', ['single', 'double', 'suite', 'deluxe', 'presidential'])->default('single');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->integer('capacity')->default(2); // Nombre de personnes
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // Équipements : wifi, tv, minibar, etc.
            $table->string('image')->nullable();
            $table->timestamps();

            // Index pour recherche rapide
            $table->index(['enterprise_id', 'status']);
            $table->index(['enterprise_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
