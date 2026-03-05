<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Nos établissements (autres sites du groupe) — pour l'app mobile, section Hotel Infos & Sécurité.
 */
class EstablishmentController extends Controller
{
    /**
     * Liste des établissements de l'entreprise (scope par user connecté).
     * GET /api/establishments
     */
    public function index(Request $request): JsonResponse
    {
        $establishments = Establishment::active()
            ->ordered()
            ->get()
            ->map(fn(Establishment $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'location' => $e->location,
                'cover_photo' => $e->cover_photo ? asset('storage/' . $e->cover_photo) : null,
            ]);

        return response()->json([
            'success' => true,
            'data' => $establishments,
        ], 200);
    }

    /**
     * Détail d'un établissement avec galerie photos.
     * GET /api/establishments/{id}
     */
    public function show(int $id): JsonResponse
    {
        $establishment = Establishment::active()->find($id);
        if (!$establishment) {
            return response()->json(['success' => false, 'message' => 'Établissement introuvable.'], 404);
        }

        $establishment->load('photos');
        $photos = $establishment->photos->map(fn($p) => [
            'id' => $p->id,
            'url' => $p->path ? asset('storage/' . $p->path) : null,
            'caption' => $p->caption,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $establishment->id,
                'name' => $establishment->name,
                'location' => $establishment->location,
                'cover_photo' => $establishment->cover_photo ? asset('storage/' . $establishment->cover_photo) : null,
                'description' => $establishment->description,
                'address' => $establishment->address,
                'phone' => $establishment->phone,
                'website' => $establishment->website,
                'photos' => $photos,
            ],
        ], 200);
    }
}
