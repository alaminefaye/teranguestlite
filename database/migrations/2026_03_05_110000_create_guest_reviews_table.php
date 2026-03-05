<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Avis / satisfaction client : recueillir les retours après commande livrée,
     * check-out, demande (blanchisserie/palace) traitée, réservation excursion terminée.
     */
    public function up(): void
    {
        Schema::create('guest_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();

            $table->string('reviewable_type', 100); // Order, Reservation, ExcursionBooking, LaundryRequest, PalaceRequest
            $table->unsignedBigInteger('reviewable_id');

            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['reviewable_type', 'reviewable_id'], 'guest_reviews_reviewable_unique');
            $table->index(['enterprise_id', 'user_id']);
            $table->index(['enterprise_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_reviews');
    }
};
