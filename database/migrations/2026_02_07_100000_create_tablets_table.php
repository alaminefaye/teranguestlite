<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tablets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable()->comment('Libellé optionnel, ex. Chambre 101');
            $table->string('device_identifier')->nullable()->comment('ID appareil pour notifications');
            $table->timestamps();

            $table->unique(['enterprise_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablets');
    }
};
