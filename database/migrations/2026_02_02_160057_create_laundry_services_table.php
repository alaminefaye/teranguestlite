<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // washing, ironing, dry_cleaning, express
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('turnaround_hours')->default(24)->comment('Délai en heures');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_services');
    }
};
