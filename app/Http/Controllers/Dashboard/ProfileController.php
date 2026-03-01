<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher la page profil (utilisateur + données de l'établissement si admin/staff).
     */
    public function index(): View
    {
        $user = auth()->user();
        $user->load('enterprise');
        $enterprise = $user->enterprise;

        return view('pages.profile', [
            'title' => 'Profil',
            'user' => $user,
            'enterprise' => $enterprise,
        ]);
    }

    /**
     * Mettre à jour les données de l'établissement de l'utilisateur connecté.
     * Chaque entreprise modifie ainsi ses propres données depuis le profil.
     */
    public function updateEnterprise(Request $request): RedirectResponse
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            return redirect()->route('profile')->with('error', 'Aucun établissement associé à votre compte.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:30720',
            'cover_photo' => 'nullable|image|max:30720',
        ]);

        if ($request->hasFile('logo')) {
            if ($enterprise->logo) {
                Storage::disk('public')->delete($enterprise->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('cover_photo')) {
            if ($enterprise->cover_photo) {
                Storage::disk('public')->delete($enterprise->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')->store('covers', 'public');
        }

        $enterprise->update($validated);

        return redirect()->route('profile')->with('success', 'Données de l\'établissement mises à jour.');
    }
}
