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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // restaurant, bar, cafe, pool_bar
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('location')->nullable(); // Emplacement dans l'hôtel
            $table->integer('capacity')->nullable(); // Nombre de places
            $table->enum('status', ['open', 'closed', 'coming_soon'])->default('open');
            
            // Horaires d'ouverture (JSON)
            $table->json('opening_hours')->nullable(); // {monday: {open: "08:00", close: "22:00"}, ...}
            
            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Features
            $table->boolean('has_terrace')->default(false);
            $table->boolean('has_wifi')->default(true);
            $table->boolean('has_live_music')->default(false);
            $table->boolean('accepts_reservations')->default(true);
            
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
            $table->index(['enterprise_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
