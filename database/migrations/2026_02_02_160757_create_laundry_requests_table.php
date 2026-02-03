<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->string('request_number')->unique();
            $table->json('items'); // [{laundry_service_id, quantity, price}]
            $table->decimal('total_price', 10, 2);
            $table->dateTime('pickup_time')->nullable();
            $table->dateTime('delivery_time')->nullable();
            $table->text('special_instructions')->nullable();
            $table->enum('status', ['pending', 'picked_up', 'in_progress', 'ready', 'delivered'])->default('pending');
            $table->timestamps();
            
            $table->index(['enterprise_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_requests');
    }
};
