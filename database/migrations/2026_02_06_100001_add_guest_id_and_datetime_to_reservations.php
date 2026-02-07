<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('guest_id')->nullable()->after('user_id')->constrained('guests')->onDelete('set null');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('check_in')->change();
            $table->dateTime('check_out')->change();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('check_in')->change();
            $table->date('check_out')->change();
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
