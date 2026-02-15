<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                    'enterprise' => $user->enterprise ? [
                        'id' => $user->enterprise->id,
                        'name' => $user->enterprise->name,
                        'logo' => $user->enterprise->logo,
                        'cover_photo' => $user->enterprise->cover_photo,
                        'gym_hours' => $user->enterprise->gym_hours,
                    ] : null,
                    'department' => $user->department,
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

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'enterprise_id' => $user->enterprise_id,
                'enterprise' => $user->enterprise ? [
                    'id' => $user->enterprise->id,
                    'name' => $user->enterprise->name,
                    'logo' => $user->enterprise->logo,
                    'cover_photo' => $user->enterprise->cover_photo,
                    'gym_hours' => $user->enterprise->gym_hours,
                    'address' => $user->enterprise->address,
                    'phone' => $user->enterprise->phone,
                    'email' => $user->enterprise->email,
                ] : null,
                'department' => $user->department,
                'room_number' => $user->room_number,
                'must_change_password' => $user->must_change_password ?? false,
                'can_reserve' => GuestReservationHelper::activeStayForUser($user) !== null,
                'created_at' => $user->created_at->toISOString(),
            ],
        ], 200);
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
