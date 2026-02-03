<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('enterprise');

        // Filtre par entreprise
        if ($request->filled('enterprise_id')) {
            $query->where('enterprise_id', $request->enterprise_id);
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $enterprises = Enterprise::all();

        // Statistiques
        $stats = [
            'total' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'guests' => User::where('role', 'guest')->count(),
        ];

        return view('pages.admin.users.index', [
            'title' => 'Gestion des Utilisateurs',
            'users' => $users,
            'enterprises' => $enterprises,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $enterprises = Enterprise::all();
        return view('pages.admin.users.create', [
            'title' => 'Créer un utilisateur',
            'enterprises' => $enterprises,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,staff,guest',
            'enterprise_id' => 'nullable|exists:enterprises,id',
            'department' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Si c'est un super_admin, pas d'entreprise
        if ($validated['role'] === 'super_admin') {
            $validated['enterprise_id'] = null;
            $validated['department'] = null;
            $validated['room_number'] = null;
        }

        // Si c'est un guest, département = null
        if ($validated['role'] === 'guest') {
            $validated['department'] = null;
        }

        // Si ce n'est pas un guest, room_number = null
        if ($validated['role'] !== 'guest') {
            $validated['room_number'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    public function show(User $user): View
    {
        $user->load('enterprise');
        
        return view('pages.admin.users.show', [
            'title' => $user->name,
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        $enterprises = Enterprise::all();
        
        return view('pages.admin.users.edit', [
            'title' => 'Modifier ' . $user->name,
            'user' => $user,
            'enterprises' => $enterprises,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,staff,guest',
            'enterprise_id' => 'nullable|exists:enterprises,id',
            'department' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Si c'est un super_admin, pas d'entreprise
        if ($validated['role'] === 'super_admin') {
            $validated['enterprise_id'] = null;
            $validated['department'] = null;
            $validated['room_number'] = null;
        }

        // Si c'est un guest, département = null
        if ($validated['role'] === 'guest') {
            $validated['department'] = null;
        }

        // Si ce n'est pas un guest, room_number = null
        if ($validated['role'] !== 'guest') {
            $validated['room_number'] = null;
        }

        // Ne mettre à jour le mot de passe que s'il est fourni
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès !');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Ne pas supprimer son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte !');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès !');
    }
}
