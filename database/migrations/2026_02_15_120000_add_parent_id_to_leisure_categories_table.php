<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Structure type Amenities : 2 catégories principales (Sport, Loisirs) avec sous-catégories dynamiques.
     */
    public function up(): void
    {
        Schema::table('leisure_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('enterprise_id')->constrained('leisure_categories')->onDelete('cascade');
        });

        // Migrer les données existantes : créer Sport/Loisirs et attacher les anciennes catégories
        $existing = DB::table('leisure_categories')->whereNull('parent_id')->get();
        if ($existing->isEmpty()) {
            return;
        }
        $byEnterprise = $existing->groupBy('enterprise_id');
        foreach ($byEnterprise as $enterpriseId => $rows) {
            $sportId = null;
            $loisirsId = null;
            foreach ($rows as $row) {
                if ($row->type === 'sport' || $row->type === 'loisirs') {
                    continue;
                }
                if ($sportId === null) {
                    $sportId = DB::table('leisure_categories')->insertGetId([
                        'enterprise_id' => $enterpriseId,
                        'parent_id' => null,
                        'name' => 'Sport',
                        'description' => 'Réservation de parcours, courts, matériel et accès salle de sport.',
                        'type' => 'sport',
                        'display_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($loisirsId === null) {
                    $loisirsId = DB::table('leisure_categories')->insertGetId([
                        'enterprise_id' => $enterpriseId,
                        'parent_id' => null,
                        'name' => 'Loisirs',
                        'description' => 'Spa, bien-être et autres activités de loisirs.',
                        'type' => 'loisirs',
                        'display_order' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $parentId = in_array($row->type, ['golf_tennis', 'fitness'], true) ? $sportId : $loisirsId;
                DB::table('leisure_categories')->where('id', $row->id)->update(['parent_id' => $parentId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leisure_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
    }
};
