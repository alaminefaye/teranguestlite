<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('settled_at')->nullable()->after('delivered_at');
        });

        Schema::create('reservation_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50); // wave, orange_money, cash, card
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('settled_at');
        });
        Schema::dropIfExists('reservation_settlements');
    }
};
