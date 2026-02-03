<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password', [
            'title' => 'Changement de mot de passe obligatoire',
        ]);
    }

    /**
     * Traiter le changement de mot de passe
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = auth()->user();

        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        // Vérifier que le nouveau mot de passe est différent de l'ancien
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Rediriger vers le dashboard approprié selon le rôle
        $redirectRoute = match($user->role) {
            'super_admin' => 'admin.dashboard',
            'admin', 'staff' => 'dashboard',
            'guest' => 'guest.dashboard',
            default => 'dashboard',
        };

        return redirect()->route($redirectRoute)
            ->with('success', 'Votre mot de passe a été changé avec succès !');
    }
}
