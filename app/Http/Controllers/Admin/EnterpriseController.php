<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnterpriseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enterprises = Enterprise::withCount(['users'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.admin.enterprises.index', [
            'title' => 'Entreprises (Hôtels)',
            'enterprises' => $enterprises,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.enterprises.create', [
            'title' => 'Créer une entreprise',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:30720',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Créer l'entreprise
        $enterprise = Enterprise::create($validated);

        // Générer un slug pour l'email
        $slug = Str::slug($enterprise->name);
        
        // Générer les credentials du compte admin
        $adminEmail = "admin@{$slug}.com";
        $adminPassword = 'passer123'; // Mot de passe par défaut
        
        // Créer automatiquement un compte administrateur pour cette entreprise
        $admin = User::create([
            'name' => "Administrateur {$enterprise->name}",
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
            'enterprise_id' => $enterprise->id,
            'department' => 'Direction',
            'must_change_password' => true, // Forcer le changement à la première connexion
        ]);

        return redirect()->route('admin.enterprises.index')
            ->with('success', "Entreprise créée avec succès !")
            ->with('admin_credentials', [
                'email' => $adminEmail,
                'password' => $adminPassword,
                'name' => $admin->name,
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Enterprise $enterprise)
    {
        $enterprise->load(['users']);
        $usersCount = $enterprise->users()->count();
        $adminsCount = $enterprise->users()->where('role', 'admin')->count();
        $staffCount = $enterprise->users()->where('role', 'staff')->count();
        $guestsCount = $enterprise->users()->where('role', 'guest')->count();

        return view('pages.admin.enterprises.show', [
            'title' => $enterprise->name,
            'enterprise' => $enterprise,
            'usersCount' => $usersCount,
            'adminsCount' => $adminsCount,
            'staffCount' => $staffCount,
            'guestsCount' => $guestsCount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enterprise $enterprise)
    {
        return view('pages.admin.enterprises.edit', [
            'title' => 'Modifier ' . $enterprise->name,
            'enterprise' => $enterprise,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enterprise $enterprise)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:30720',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($enterprise->logo) {
                Storage::disk('public')->delete($enterprise->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $enterprise->update($validated);

        return redirect()->route('admin.enterprises.index')
            ->with('success', 'Entreprise mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enterprise $enterprise)
    {
        // Supprimer le logo
        if ($enterprise->logo) {
            Storage::disk('public')->delete($enterprise->logo);
        }

        $enterprise->delete();

        return redirect()->route('admin.enterprises.index')
            ->with('success', 'Entreprise supprimée avec succès !');
    }
}
