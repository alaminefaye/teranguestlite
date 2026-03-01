<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('stock_product_id')->nullable()->after('display_order')->constrained('stock_products')->onDelete('set null');
            $table->decimal('stock_quantity_per_portion', 10, 3)->default(1)->after('stock_product_id')->comment('Quantité du produit stock consommée par portion vendue (ex: 2 pour une carafe = 2 bouteilles)');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['stock_product_id']);
            $table->dropColumn(['stock_product_id', 'stock_quantity_per_portion']);
        });
    }
};
