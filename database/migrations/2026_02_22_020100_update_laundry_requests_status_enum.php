<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('laundry_requests')) {
            return;
        }

        if (Schema::hasColumn('laundry_requests', 'status')) {
            DB::table('laundry_requests')
                ->where('status', 'in_progress')
                ->update(['status' => 'processing']);

            DB::statement("
                ALTER TABLE laundry_requests
                MODIFY COLUMN status ENUM('pending', 'picked_up', 'processing', 'ready', 'delivered', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('laundry_requests')) {
            return;
        }

        if (Schema::hasColumn('laundry_requests', 'status')) {
            DB::table('laundry_requests')
                ->where('status', 'processing')
                ->update(['status' => 'in_progress']);

            DB::statement("
                ALTER TABLE laundry_requests
                MODIFY COLUMN status ENUM('pending', 'picked_up', 'in_progress', 'ready', 'delivered')
                NOT NULL DEFAULT 'pending'
            ");
        }
    }
};

