<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * Enregistrer ou mettre à jour le FCM token de l'utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $user->update([
            'fcm_token' => $request->fcm_token,
            'fcm_token_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token enregistré avec succès',
        ]);
    }

    /**
     * Supprimer le FCM token de l'utilisateur (lors de la déconnexion)
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $user->update([
            'fcm_token' => null,
            'fcm_token_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token supprimé avec succès',
        ]);
    }
}
