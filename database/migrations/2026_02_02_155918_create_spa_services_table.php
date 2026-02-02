<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spa_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // massage, facial, body_treatment, wellness
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration')->comment('Durée en minutes');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->boolean('is_featured')->default(false);
            $table->text('benefits')->nullable(); // JSON - liste des bienfaits
            $table->text('contraindications')->nullable(); // JSON - contre-indications
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
            $table->index(['enterprise_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_services');
    }
};
