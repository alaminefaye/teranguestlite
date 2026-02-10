<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestFcmToken;
use App\Services\FirebaseNotificationService;
use App\Services\GuestReservationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * Enregistrer ou mettre à jour le FCM token de l'utilisateur.
     * Si l'utilisateur a un séjour actif (chambre), le token est aussi enregistré pour le guest
     * afin que les notifications (commandes, réservations) lui parviennent sur cet appareil.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $token = trim($request->fcm_token);
        $user->update([
            'fcm_token' => $token,
            'fcm_token_updated_at' => now(),
        ]);

        // Lier ce token au guest si l'utilisateur a un séjour actif (client en chambre)
        $stay = GuestReservationHelper::activeStayForUser($user);
        if ($stay !== null && $user->enterprise_id && isset($stay['guest_id'])) {
            try {
                GuestFcmToken::register($user->enterprise_id, $stay['guest_id'], $token, 'mobile');
                \Log::info("FCM: token registered for user {$user->id} and guest {$stay['guest_id']}");
            } catch (\Exception $e) {
                \Log::warning('FCM: could not register token for guest: ' . $e->getMessage());
            }
        } else {
            \Log::info("FCM: user {$user->id} has no active stay, token stored on user only (guest_id not linked)");
        }

        return response()->json([
            'success' => true,
            'message' => 'FCM token enregistré avec succès',
        ]);
    }

    /**
     * Supprimer le FCM token de l'utilisateur (lors de la déconnexion).
     * Retire aussi ce token de la table guest_fcm_tokens pour ne plus notifier cet appareil.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $previousToken = $user->fcm_token;
        $user->update([
            'fcm_token' => null,
            'fcm_token_updated_at' => now(),
        ]);

        if ($previousToken) {
            GuestFcmToken::where('fcm_token', $previousToken)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'FCM token supprimé avec succès',
        ]);
    }

    /**
     * Envoyer une notification de test depuis le serveur vers le token fourni.
     * Permet de vérifier si le téléphone reçoit bien les push (même token = même appareil).
     */
    public function test(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $token = trim($request->fcm_token);
        $suffix = strlen($token) >= 8 ? substr($token, -8) : '(short)';
        \Log::info("FCM test requested by user {$user->id}, token suffix ...{$suffix}");

        $sent = app(FirebaseNotificationService::class)->sendToToken(
            $token,
            'Test serveur',
            'Si vous voyez ceci, les notifications push fonctionnent depuis le serveur.',
            ['type' => 'test', 'screen' => 'Notifications']
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Notification de test envoyée' : 'Échec envoi (voir logs)',
            'token_suffix' => $suffix,
        ]);
    }
}
