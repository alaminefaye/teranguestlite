<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Lie formellement chaque accès tablette (User role=guest) à une chambre.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('room_number')
                ->constrained('rooms')->nullOnDelete();
        });

        // Remplir room_id pour les utilisateurs guest existants (via room_number)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('
                UPDATE users u
                INNER JOIN rooms r ON r.room_number = u.room_number AND r.enterprise_id = u.enterprise_id
                SET u.room_id = r.id
                WHERE u.role = ? AND u.room_number IS NOT NULL AND u.room_id IS NULL
            ', ['guest']);
        } else {
            foreach (DB::table('users')->where('role', 'guest')->whereNotNull('room_number')->whereNull('room_id')->get() as $user) {
                $roomId = DB::table('rooms')
                    ->where('enterprise_id', $user->enterprise_id)
                    ->where('room_number', $user->room_number)
                    ->value('id');
                if ($roomId) {
                    DB::table('users')->where('id', $user->id)->update(['room_id' => $roomId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
        });
    }
};
