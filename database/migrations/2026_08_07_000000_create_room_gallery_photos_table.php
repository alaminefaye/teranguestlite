<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();
            $table->enum('type', ['chambre', 'suite'])->default('chambre');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('path'); // chemin storage (toujours jpg/png après conversion)
            $table->string('original_extension', 20)->nullable(); // ex: 'tif', 'jpg'
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['enterprise_id', 'type', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_gallery_photos');
    }
};
