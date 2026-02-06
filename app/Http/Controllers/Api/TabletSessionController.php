<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TabletSessionController extends Controller
{
    /**
     * Connexion tablette par code client.
     * Vérifie que le client a une réservation de chambre valide (check_in <= now <= check_out).
     */
    public function session(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20',
            'enterprise_id' => 'required|exists:enterprises,id',
        ]);

        $code = Str::upper(Str::trim($request->input('code')));
        $enterpriseId = (int) $request->input('enterprise_id');

        $user = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'guest')
            ->where('tablet_code', $code)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide.',
            ], 422);
        }

        $reservation = Reservation::where('user_id', $user->id)
            ->where('enterprise_id', $enterpriseId)
            ->validAt(now())
            ->with('room')
            ->orderByDesc('check_in')
            ->first();

        if (!$reservation) {
            $futureOrPast = Reservation::where('user_id', $user->id)
                ->where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->first();

            if ($futureOrPast) {
                if ($futureOrPast->check_in > now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Votre réservation n\'est pas encore active. Date d\'arrivée : ' . $futureOrPast->check_in->format('d/m/Y H:i'),
                    ], 422);
                }
                if ($futureOrPast->check_out < now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Votre séjour est terminé. Merci de votre visite.',
                    ], 422);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucune réservation de chambre active pour ce code.',
            ], 422);
        }

        // Synchroniser la chambre du user avec la réservation active (pour les APIs qui utilisent room_number)
        $user->update(['room_number' => $reservation->room->room_number ?? $user->room_number]);

        $token = $user->createToken('tablet', ['tablet'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'guest' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'room_number' => $user->room_number ?? $reservation->room->room_number ?? null,
                    'room_id' => $reservation->room_id,
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'check_in' => $reservation->check_in->toIso8601String(),
                    'check_out' => $reservation->check_out->toIso8601String(),
                ],
            ],
        ], 200);
    }

    /**
     * Déconnexion tablette (révoquer le token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ], 200);
    }
}
