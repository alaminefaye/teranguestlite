<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nos établissements : autres sites du même groupe (entreprise) dans le pays.
     * Photo du box (cover), nom, lieu, description, galerie de photos.
     */
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('location', 255)->nullable()->comment('Ville, zone ou région');
            $table->string('cover_photo')->nullable()->comment('Photo pour la carte/box');
            $table->text('description')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['enterprise_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
