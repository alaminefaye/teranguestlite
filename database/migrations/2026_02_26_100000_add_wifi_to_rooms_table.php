<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wi-Fi peut être différent par chambre. Si renseigné, il remplace le Wi-Fi global de l'hôtel sur la tablette.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('wifi_network')->nullable()->after('image');
            $table->string('wifi_password')->nullable()->after('wifi_network');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['wifi_network', 'wifi_password']);
        });
    }
};
