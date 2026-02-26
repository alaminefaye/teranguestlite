<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $enterprises = DB::table('enterprises')->select('id')->get();

        foreach ($enterprises as $enterprise) {
            $hasGuidedTours = DB::table('palace_services')
                ->where('enterprise_id', $enterprise->id)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%visites guidées%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%visite guidée%']);
                })
                ->exists();

            if (!$hasGuidedTours) {
                $maxOrder = DB::table('palace_services')
                    ->where('enterprise_id', $enterprise->id)
                    ->max('display_order');

                DB::table('palace_services')->insert([
                    'enterprise_id' => $enterprise->id,
                    'name' => 'Visites guidées personnalisées',
                    'category' => 'concierge',
                    'description' => 'Réservation de guides certifiés pour circuits culturels, gastronomiques ou historiques.',
                    'price' => null,
                    'price_on_request' => true,
                    'status' => 'available',
                    'is_premium' => false,
                    'display_order' => ($maxOrder ?? 0) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('palace_services')
            ->where('name', 'Visites guidées personnalisées')
            ->delete();
    }
};
