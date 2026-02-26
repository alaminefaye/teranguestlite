<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeisureCategory;
use Illuminate\Http\JsonResponse;

class LeisureCategoryController extends Controller
{
    /**
     * Arbre Sport / Loisirs : catégories principales avec leurs sous-catégories (comme Amenities).
     * L'app affiche d'abord 2 boxes (Sport, Loisirs), puis la liste dynamique des activités.
     */
    public function index(): JsonResponse
    {
        $mainCategories = LeisureCategory::with(['children' => function ($q) {
            $q->active()->ordered();
        }])
            ->topLevel()
            ->active()
            ->ordered()
            ->get();

        $data = $mainCategories->map(function ($main) {
            return [
                'id' => $main->id,
                'name' => $main->name,
                'description' => $main->description,
                'type' => $main->type,
                'display_order' => $main->display_order,
                'children' => $main->children->filter(fn ($c) => $c->is_active)->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'description' => $c->description,
                    'type' => $c->type,
                    'display_order' => $c->display_order,
                ])->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
