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
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'guest_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('guest_id')->nullable()->after('user_id')->constrained('guests')->onDelete('set null');
                });
            }
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
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'guest_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['guest_id']);
                });
            }
        }
    }
};
