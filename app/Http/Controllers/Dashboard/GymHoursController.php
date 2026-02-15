<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GymHoursController extends Controller
{
    /**
     * Affiche le formulaire des horaires de la salle de sport (établissement courant).
     */
    public function index(): View
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }

        return view('pages.dashboard.gym-hours.edit', [
            'title' => 'Horaires salle de sport',
            'enterprise' => $enterprise,
        ]);
    }

    /**
     * Met à jour les horaires de la salle de sport (gym_hours) de l'établissement.
     */
    public function update(Request $request): RedirectResponse
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }

        $validated = $request->validate([
            'gym_hours' => 'nullable|string|max:2000',
        ]);

        $enterprise->update(['gym_hours' => $validated['gym_hours'] ?? null]);

        return redirect()->route('dashboard.gym-hours.index')
            ->with('success', 'Horaires de la salle de sport enregistrés.');
    }
}
