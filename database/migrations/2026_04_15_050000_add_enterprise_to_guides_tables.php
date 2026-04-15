<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guide_categories', function (Blueprint $table) {
            $table->foreignId('enterprise_id')
                ->nullable()
                ->after('id')
                ->constrained('enterprises')
                ->nullOnDelete();
            $table->string('category_type')->nullable()->after('name');
            $table->index(['enterprise_id', 'is_active']);
        });

        Schema::table('guide_items', function (Blueprint $table) {
            $table->foreignId('enterprise_id')
                ->nullable()
                ->after('id')
                ->constrained('enterprises')
                ->nullOnDelete();
            $table->index(['enterprise_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('guide_items', function (Blueprint $table) {
            $table->dropIndex(['enterprise_id', 'is_active']);
            $table->dropConstrainedForeignId('enterprise_id');
        });

        Schema::table('guide_categories', function (Blueprint $table) {
            $table->dropIndex(['enterprise_id', 'is_active']);
            $table->dropColumn('category_type');
            $table->dropConstrainedForeignId('enterprise_id');
        });
    }
};

