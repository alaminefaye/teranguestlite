<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Excursion;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ExcursionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Excursion::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $excursions = $query->ordered()->paginate(12);

        $stats = [
            'total' => Excursion::count(),
            'available' => Excursion::available()->count(),
            'featured' => Excursion::featured()->count(),
            'cultural' => Excursion::where('type', 'cultural')->count(),
        ];

        return view('pages.dashboard.excursions.index', [
            'title' => 'Excursions',
            'excursions' => $excursions,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.excursions.create', ['title' => 'Créer une excursion']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cultural,adventure,relaxation,city_tour',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price_adult' => 'required|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'departure_time' => 'nullable|string|max:50',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:available,unavailable,seasonal',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_featured'] = $request->has('is_featured');
        $validated['included'] = $this->parseLines($request->input('included'));
        $validated['not_included'] = $this->parseLines($request->input('not_included'));

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('excursions', 'public');
        }

        Excursion::create($validated);

        return redirect()->route('dashboard.excursions.index')
            ->with('success', 'Excursion créée avec succès !');
    }

    public function show(Excursion $excursion): View
    {
        return view('pages.dashboard.excursions.show', [
            'title' => $excursion->name,
            'excursion' => $excursion,
        ]);
    }

    public function edit(Excursion $excursion): View
    {
        return view('pages.dashboard.excursions.edit', [
            'title' => 'Modifier ' . $excursion->name,
            'excursion' => $excursion,
        ]);
    }

    public function update(Request $request, Excursion $excursion): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cultural,adventure,relaxation,city_tour',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price_adult' => 'required|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'departure_time' => 'nullable|string|max:50',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:available,unavailable,seasonal',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['included'] = $this->parseLines($request->input('included'));
        $validated['not_included'] = $this->parseLines($request->input('not_included'));

        if ($request->hasFile('image')) {
            if ($excursion->image) {
                Storage::disk('public')->delete($excursion->image);
            }
            $validated['image'] = $request->file('image')->store('excursions', 'public');
        }

        $excursion->update($validated);

        return redirect()->route('dashboard.excursions.index')
            ->with('success', 'Excursion mise à jour avec succès !');
    }

    public function destroy(Excursion $excursion): RedirectResponse
    {
        if ($excursion->image) {
            Storage::disk('public')->delete($excursion->image);
        }

        $excursion->delete();

        return redirect()->route('dashboard.excursions.index')
            ->with('success', 'Excursion supprimée avec succès !');
    }

    /** Masquer / Afficher (sans supprimer). */
    public function toggleActive(Excursion $excursion): RedirectResponse
    {
        $excursion->update(['is_active' => !$excursion->is_active]);
        $label = $excursion->is_active ? 'affichée' : 'masquée';
        return redirect()->route('dashboard.excursions.index')
            ->with('success', "Excursion {$label}.");
    }

    private function parseLines(?string $value): array
    {
        if (empty($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode("\n", $value))));
    }
}
