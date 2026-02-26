<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sections que le staff peut gérer (tuiles dashboard + notifications).
     * Si vide/null pour un admin: accès à tout. Pour un staff: n'accède qu'aux sections listées.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('managed_sections')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('managed_sections');
        });
    }
};
