<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'staff', 'guest'])->default('guest')->after('email');
            $table->foreignId('enterprise_id')->nullable()->constrained('enterprises')->onDelete('cascade')->after('role');
            $table->string('department')->nullable()->after('enterprise_id'); // Pour staff: reception, housekeeping, room_service, spa, etc.
            $table->string('room_number')->nullable()->after('department'); // Pour guest: numéro de chambre
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['enterprise_id']);
            $table->dropColumn(['role', 'enterprise_id', 'department', 'room_number']);
        });
    }
};
