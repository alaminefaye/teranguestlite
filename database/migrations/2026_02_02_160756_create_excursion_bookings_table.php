<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursion_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('excursion_id')->constrained('excursions')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->date('booking_date');
            $table->integer('number_of_adults')->default(1);
            $table->integer('number_of_children')->default(0);
            $table->decimal('total_price', 10, 2);
            $table->text('special_requests')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
            $table->index(['excursion_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursion_bookings');
    }
};
