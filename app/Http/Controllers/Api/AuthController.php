<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Room;
use App\Models\User;
use App\Services\GuestReservationHelper;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Connexion (Login)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect',
            ], 401);
        }

        $user = Auth::user();

        // Créer un token d'accès
        $token = $user->createToken('mobile-app')->plainTextToken;

        $enterpriseData = $user->enterprise ? [
            'id' => $user->enterprise->id,
            'name' => $user->enterprise->name,
            'logo' => $user->enterprise->logo,
            'cover_photo' => $user->enterprise->cover_photo,
            'gym_hours' => $user->enterprise->gym_hours,
            'address' => $user->enterprise->address,
            'phone' => $user->enterprise->phone,
            'email' => $user->enterprise->email,
            'hotel_infos' => $this->hotelInfosForUser($user),
            'emergency' => $user->enterprise->emergency,
            'chatbot_url' => $user->enterprise->chatbot_url,
        ] : null;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'enterprise_id' => $user->enterprise_id,
                    'enterprise' => $enterpriseData,
                    'department' => $user->department,
                    'managed_sections' => $user->managed_sections ?? [],
                    'room_number' => $user->room_number,
                    'must_change_password' => $user->must_change_password ?? false,
                    'can_reserve' => GuestReservationHelper::activeStayForUser($user) !== null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Connexion Web via le Code Client (QR Code)
     */
    public function webLogin(Request $request)
    {
        $request->validate([
            'client_code' => 'required|string',
        ]);

        $user = User::where('client_code', $request->client_code)
            ->where('role', 'guest')
            ->first();

        // 2) Fallback : vérifier si c'est le "Code tablette" (access_code) d'un Guest physique
        // et récupérer la tablette de la chambre correspondant à sa réservation active.
        if (!$user) {
            $guest = \App\Models\Guest::where('access_code', $request->client_code)->first();
            if ($guest) {
                $reservation = \App\Models\Reservation::withoutGlobalScope('enterprise')
                    ->where('guest_id', $guest->id)
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->where('check_in', '<=', now())
                    ->where('check_out', '>=', now())
                    ->first();

                if ($reservation && $reservation->room_id) {
                    // Trouver l'utilisateur (tablette) lié à cette chambre
                    $user = User::where('role', 'guest')
                        ->where('enterprise_id', $guest->enterprise_id)
                        ->where(function ($q) use ($reservation) {
                            $q->where('room_id', $reservation->room_id)
                                ->orWhere('room_number', $reservation->room->room_number ?? null);
                        })
                        ->first();
                }
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Code client invalide',
            ], 401);
        }

        // Créer un token d'accès spécifique au Web
        $token = $user->createToken('web-app')->plainTextToken;

        $enterpriseData = $user->enterprise ? [
            'id' => $user->enterprise->id,
            'name' => $user->enterprise->name,
            'logo' => $user->enterprise->logo,
            'cover_photo' => $user->enterprise->cover_photo,
            'gym_hours' => $user->enterprise->gym_hours,
            'address' => $user->enterprise->address,
            'phone' => $user->enterprise->phone,
            'email' => $user->enterprise->email,
            'hotel_infos' => $this->hotelInfosForUser($user),
            'emergency' => $user->enterprise->emergency,
            'chatbot_url' => $user->enterprise->chatbot_url,
        ] : null;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'enterprise_id' => $user->enterprise_id,
                    'enterprise' => $enterpriseData,
                    'department' => $user->department,
                    'managed_sections' => $user->managed_sections ?? [],
                    'room_number' => $user->room_number,
                    'must_change_password' => false,
                    'can_reserve' => GuestReservationHelper::activeStayForUser($user) !== null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Déconnexion (Logout)
     */
    public function logout(Request $request)
    {
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    /**
     * Obtenir les informations du profil utilisateur
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('enterprise');

        $enterpriseData = $user->enterprise ? [
            'id' => $user->enterprise->id,
            'name' => $user->enterprise->name,
            'logo' => $user->enterprise->logo,
            'cover_photo' => $user->enterprise->cover_photo,
            'gym_hours' => $user->enterprise->gym_hours,
            'address' => $user->enterprise->address,
            'phone' => $user->enterprise->phone,
            'email' => $user->enterprise->email,
            'hotel_infos' => $this->hotelInfosForUser($user),
            'emergency' => $user->enterprise->emergency,
            'chatbot_url' => $user->enterprise->chatbot_url,
        ] : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'enterprise_id' => $user->enterprise_id,
                'enterprise' => $enterpriseData,
                'department' => $user->department,
                'managed_sections' => $user->managed_sections ?? [],
                'room_number' => $user->room_number,
                'must_change_password' => $user->must_change_password ?? false,
                'can_reserve' => GuestReservationHelper::activeStayForUser($user) !== null,
                'created_at' => $user->created_at->toISOString(),
            ],
        ], 200);
    }

    /**
     * Retourne les hotel_infos (livret d'accueil) pour l'utilisateur.
     * Si l'utilisateur a un room_id, les identifiants Wi‑Fi de la chambre remplacent ceux de l'hôtel.
     */
    private function hotelInfosForUser(User $user): array
    {
        if (!$user->enterprise) {
            return [
                'wifi_network' => '',
                'wifi_password' => '',
                'house_rules' => '',
                'map_url' => null,
                'practical_info' => '',
            ];
        }
        if ($user->room_id) {
            $room = Room::withoutGlobalScope('enterprise')
                ->where('enterprise_id', $user->enterprise_id)
                ->where('id', $user->room_id)
                ->first();
            if ($room) {
                return $user->enterprise->getHotelInfosForRoom($room);
            }
        }
        return $user->enterprise->hotel_infos;
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect',
            ], 400);
        }

        // Vérifier que le nouveau est différent
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien',
            ], 400);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe changé avec succès',
        ], 200);
    }
}
