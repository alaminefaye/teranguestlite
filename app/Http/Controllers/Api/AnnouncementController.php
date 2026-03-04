<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     *
     * Retourne la liste mélangée des annonces éligibles pour l'entreprise de l'utilisateur :
     *   - Annonces super admin ciblant cette entreprise (ou toutes)
     *   - Annonces propres de l'entreprise
     * Triées par display_order puis id.
     */
    public function index(Request $request): JsonResponse
    {
        $enterpriseId = $request->user()->enterprise_id;

        if (!$enterpriseId) {
            return response()->json(['data' => []]);
        }

        $announcements = Announcement::eligibleForEnterprise($enterpriseId)->get();

        return response()->json([
            'data' => $announcements->map(fn($a) => $a->toApiArray())->values(),
        ]);
    }

    /**
     * POST /api/announcements/{id}/view
     *
     * Incrémente le compteur de vues d'une annonce si elle est éligible
     * pour l'entreprise de l'utilisateur courant.
     * Appelé en fire-and-forget depuis l'app mobile.
     */
    public function recordView(Request $request, int $id): JsonResponse
    {
        $enterpriseId = $request->user()->enterprise_id;

        if (!$enterpriseId) {
            return response()->json(['ok' => false], 403);
        }

        // Vérifie que l'annonce est bien éligible pour cette entreprise
        $announcement = Announcement::eligibleForEnterprise($enterpriseId)
            ->where('id', $id)
            ->first();

        if (!$announcement) {
            return response()->json(['ok' => false], 403);
        }

        // Incrément atomique (évite les race conditions en boucle veille)
        $announcement->increment('view_count');

        return response()->json(['ok' => true]);
    }
}
