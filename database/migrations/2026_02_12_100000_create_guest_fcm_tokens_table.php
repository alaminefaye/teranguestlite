<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tokens FCM liés au client (guest) : tablette en chambre ou app mobile du client.
     * Permet d'envoyer les notifications (commandes, réservations, statuts) uniquement au client concerné.
     */
    public function up(): void
    {
        Schema::create('guest_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('guest_id');
            $table->string('fcm_token', 500);
            $table->string('source', 20)->default('mobile'); // 'mobile' | 'tablet'
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['guest_id', 'fcm_token']);
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_fcm_tokens');
    }
};
