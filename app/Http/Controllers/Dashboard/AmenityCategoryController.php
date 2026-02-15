<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AmenityCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = AmenityCategory::withCount('items');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->ordered()->paginate(12);

        return view('pages.dashboard.amenity-categories.index', [
            'title' => 'Amenities & Conciergerie',
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.amenity-categories.create', [
            'title' => 'Ajouter une catégorie',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        AmenityCategory::create($validated);

        return redirect()->route('dashboard.amenity-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(AmenityCategory $amenityCategory): View
    {
        return view('pages.dashboard.amenity-categories.edit', [
            'title' => 'Modifier la catégorie',
            'category' => $amenityCategory,
        ]);
    }

    public function update(Request $request, AmenityCategory $amenityCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $amenityCategory->update($validated);

        return redirect()->route('dashboard.amenity-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(AmenityCategory $amenityCategory): RedirectResponse
    {
        $amenityCategory->delete();

        return redirect()->route('dashboard.amenity-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
