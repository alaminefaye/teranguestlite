<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name'); // ex: Berline Premium, SUV 7 places
            $table->string('vehicle_type')->default('other'); // berline, suv, minibus, van, other
            $table->unsignedTinyInteger('number_of_seats');
            $table->string('image')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['enterprise_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
