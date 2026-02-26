<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AmenityCategoryController extends Controller
{
    /**
     * Liste des catégories Amenities & Conciergerie avec leurs articles.
     * Utilisé par l'app mobile pour afficher les boxes et la liste de sélection dynamique.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = AmenityCategory::with(['items' => function ($q) {
            $q->active()->orderBy('display_order')->orderBy('name');
        }])
            ->active()
            ->ordered()
            ->get();

        $data = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'display_order' => $cat->display_order,
                'items' => $cat->items->filter(fn ($item) => $item->is_active)->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'display_order' => $item->display_order,
                ])->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
