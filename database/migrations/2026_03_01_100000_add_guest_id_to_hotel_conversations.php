<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('guest_id')->nullable()->after('user_id');
            $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hotel_conversations', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
        });
    }
};
