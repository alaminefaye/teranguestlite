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

        // Add token if it doesn't already exist for this user
        $user->fcmTokens()->firstOrCreate([
            'token' => $request->fcm_token,
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

        $request->validate([
            'fcm_token' => 'sometimes|string',
        ]);

        if ($request->has('fcm_token') && !empty($request->fcm_token)) {
            // Delete specific device
            $user->fcmTokens()->where('token', $request->fcm_token)->delete();
        } else {
            // If none provided, clear all tokens for security (optional legacy behavior)
            // or just respond with error
            $user->fcmTokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'FCM token supprimé avec succès',
        ]);
    }
}
