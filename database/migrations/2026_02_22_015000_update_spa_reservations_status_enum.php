<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('spa_reservations')) {
            return;
        }

        DB::statement("
            ALTER TABLE spa_reservations
            MODIFY COLUMN status ENUM('pending', 'pending_reschedule', 'confirmed', 'cancelled', 'completed')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('spa_reservations')) {
            return;
        }

        DB::table('spa_reservations')
            ->where('status', 'pending_reschedule')
            ->update(['status' => 'pending']);

        DB::statement("
            ALTER TABLE spa_reservations
            MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};

