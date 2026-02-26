<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AmenityCategory;
use App\Models\AmenityItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AmenityItemController extends Controller
{
    public function index(AmenityCategory $amenityCategory): View
    {
        $items = $amenityCategory->items()->orderBy('display_order')->orderBy('name')->get();

        return view('pages.dashboard.amenity-items.index', [
            'title' => 'Articles : ' . $amenityCategory->name,
            'category' => $amenityCategory,
            'items' => $items,
        ]);
    }

    public function create(AmenityCategory $amenityCategory): View
    {
        return view('pages.dashboard.amenity-items.create', [
            'title' => 'Ajouter un article',
            'category' => $amenityCategory,
        ]);
    }

    public function store(Request $request, AmenityCategory $amenityCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['amenity_category_id'] = $amenityCategory->id;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        AmenityItem::create($validated);

        return redirect()->route('dashboard.amenity-categories.items.index', $amenityCategory)
            ->with('success', 'Article ajouté.');
    }

    public function edit(AmenityCategory $amenityCategory, AmenityItem $item): View
    {
        if ($item->amenity_category_id != $amenityCategory->id) {
            abort(404);
        }
        return view('pages.dashboard.amenity-items.edit', [
            'title' => 'Modifier l\'article',
            'category' => $amenityCategory,
            'item' => $item,
        ]);
    }

    public function update(Request $request, AmenityCategory $amenityCategory, AmenityItem $item): RedirectResponse
    {
        if ($item->amenity_category_id != $amenityCategory->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('dashboard.amenity-categories.items.index', $amenityCategory)
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(AmenityCategory $amenityCategory, AmenityItem $item): RedirectResponse
    {
        if ($item->amenity_category_id != $amenityCategory->id) {
            abort(404);
        }
        $item->delete();

        return redirect()->route('dashboard.amenity-categories.items.index', $amenityCategory)
            ->with('success', 'Article supprimé.');
    }

    /** Masquer / Afficher l'article (sans supprimer). */
    public function toggleActive(AmenityCategory $amenityCategory, AmenityItem $item): RedirectResponse
    {
        if ($item->amenity_category_id != $amenityCategory->id) {
            abort(404);
        }
        $item->update(['is_active' => !$item->is_active]);
        $label = $item->is_active ? 'affiché' : 'masqué';
        return redirect()->route('dashboard.amenity-categories.items.index', $amenityCategory)
            ->with('success', "Article {$label}.");
    }
}
