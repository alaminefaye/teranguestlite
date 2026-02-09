<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'palace_requests',
            'spa_reservations',
            'restaurant_reservations',
            'excursion_bookings',
            'laundry_requests',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'guest_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('guest_id')->nullable();
            });
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'palace_requests',
            'spa_reservations',
            'restaurant_reservations',
            'excursion_bookings',
            'laundry_requests',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'guest_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['guest_id']);
            });
        }
    }
};
