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
        Schema::create('amenity_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->index('enterprise_id');
        });

        Schema::create('amenity_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_category_id')->constrained('amenity_categories')->onDelete('cascade');
            $table->string('name');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->index('amenity_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_items');
        Schema::dropIfExists('amenity_categories');
    }
};
