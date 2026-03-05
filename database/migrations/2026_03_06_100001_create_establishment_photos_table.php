<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Galerie de photos d'un établissement (détails : extérieur, chambres, etc.).
     */
    public function up(): void
    {
        Schema::create('establishment_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            $table->string('path');
            $table->string('caption', 255)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['establishment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_photos');
    }
};
