<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spa_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('spa_service_id')->constrained('spa_services')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->text('special_requests')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['pending', 'pending_reschedule', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
            $table->index(['spa_service_id', 'reservation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_reservations');
    }
};
