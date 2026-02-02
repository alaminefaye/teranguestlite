<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('palace_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // concierge, transport, vip, butler
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->nullable()->comment('Prix de base, peut être sur demande');
            $table->boolean('price_on_request')->default(false);
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->boolean('is_premium')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palace_services');
    }
};
