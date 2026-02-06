<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tablet_code', 20)->nullable()->after('room_number');
        });

        // Un client par code par établissement (les guests)
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['enterprise_id', 'tablet_code'], 'users_enterprise_tablet_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_enterprise_tablet_code_unique');
            $table->dropColumn('tablet_code');
        });
    }
};
