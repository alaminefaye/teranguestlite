<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->json('name');
            $table->json('description')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->json('equipments')->nullable();
            $table->string('image')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['enterprise_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_rooms');
    }
};

