<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guest::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('access_code', 'like', '%' . $request->search . '%');
            });
        }

        $guests = $query->orderBy('name')->paginate(15);
        $stats = [
            'total' => Guest::count(),
        ];

        return view('pages.dashboard.guests.index', [
            'title' => 'Clients (invités)',
            'guests' => $guests,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.guests.create', ['title' => 'Créer un client']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['access_code'] = Guest::generateAccessCode($validated['enterprise_id']);

        $guest = Guest::create($validated);

        return redirect()->route('dashboard.guests.show', $guest)
            ->with('success', 'Client créé avec succès ! Code tablette : ' . $guest->access_code);
    }

    public function show(Guest $guest): View
    {
        $guest->loadCount('reservations');
        return view('pages.dashboard.guests.show', [
            'title' => $guest->name,
            'guest' => $guest,
        ]);
    }

    public function edit(Guest $guest): View
    {
        return view('pages.dashboard.guests.edit', [
            'title' => 'Modifier ' . $guest->name,
            'guest' => $guest,
        ]);
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $guest->update($validated);

        return redirect()->route('dashboard.guests.show', $guest)
            ->with('success', 'Client mis à jour avec succès !');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        if ($guest->reservations()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce client : des réservations sont associées.');
        }
        $guest->delete();
        return redirect()->route('dashboard.guests.index')
            ->with('success', 'Client supprimé avec succès !');
    }

    /**
     * Régénérer le code (admin / gérant uniquement)
     */
    public function regenerateCode(Guest $guest): RedirectResponse
    {
        $newCode = $guest->regenerateAccessCode();
        return back()->with('success', 'Nouveau code tablette : ' . $newCode);
    }
}
