<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->foreignId('stock_category_id')->constrained('stock_categories')->onDelete('restrict');
            $table->string('name');
            $table->string('sku', 80)->nullable();
            $table->string('barcode', 80)->nullable();
            $table->string('unit', 30)->default('piece'); // piece, liter, kg, box, unit, etc.
            $table->decimal('quantity_current', 15, 3)->default(0);
            $table->decimal('quantity_min', 15, 3)->default(0)->comment('Seuil alerte : si quantity_current <= quantity_min');
            $table->decimal('quantity_max', 15, 3)->nullable()->comment('Seuil max optionnel');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('location', 100)->nullable()->comment('Emplacement / entrepôt');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['enterprise_id', 'stock_category_id']);
            $table->index(['enterprise_id', 'is_active']);
            $table->unique(['enterprise_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_products');
    }
};
