<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('stock_product_id')->constrained('stock_products')->onDelete('cascade');
            $table->string('type', 20); // in, out, adjustment
            $table->decimal('quantity', 15, 3); // positif = entrée, négatif = sortie (pour adjustment)
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_type', 80)->nullable()->comment('order, manual, transfer, etc.');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['enterprise_id', 'stock_product_id']);
            $table->index(['enterprise_id', 'type']);
            $table->index(['enterprise_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
