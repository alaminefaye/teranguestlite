<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LeisureCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LeisureSubcategoryController extends Controller
{
    /** Liste des sous-catégories (activités) d'une catégorie principale (Sport ou Loisirs). */
    public function index(LeisureCategory $leisureCategory): View
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $subcategories = $leisureCategory->children()->ordered()->get();

        return view('pages.dashboard.leisure-subcategories.index', [
            'title' => 'Activités : ' . $leisureCategory->name,
            'parent' => $leisureCategory,
            'subcategories' => $subcategories,
        ]);
    }

    public function create(LeisureCategory $leisureCategory): View
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        return view('pages.dashboard.leisure-subcategories.create', [
            'title' => 'Ajouter une activité',
            'parent' => $leisureCategory,
        ]);
    }

    public function store(Request $request, LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:golf,tennis,fitness,spa,other',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['parent_id'] = $leisureCategory->id;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        LeisureCategory::create($validated);

        return redirect()->route('dashboard.leisure-categories.subcategories.index', $leisureCategory)
            ->with('success', 'Activité ajoutée.');
    }

    public function edit(LeisureCategory $leisureCategory, LeisureCategory $subcategory): View
    {
        if ($leisureCategory->parent_id !== null || $subcategory->parent_id != $leisureCategory->id) {
            abort(404);
        }
        return view('pages.dashboard.leisure-subcategories.edit', [
            'title' => 'Modifier l\'activité',
            'parent' => $leisureCategory,
            'subcategory' => $subcategory,
        ]);
    }

    public function update(Request $request, LeisureCategory $leisureCategory, LeisureCategory $subcategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null || $subcategory->parent_id != $leisureCategory->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:golf,tennis,fitness,spa,other',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $subcategory->update($validated);

        return redirect()->route('dashboard.leisure-categories.subcategories.index', $leisureCategory)
            ->with('success', 'Activité mise à jour.');
    }

    public function destroy(LeisureCategory $leisureCategory, LeisureCategory $subcategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null || $subcategory->parent_id != $leisureCategory->id) {
            abort(404);
        }
        $subcategory->delete();

        return redirect()->route('dashboard.leisure-categories.subcategories.index', $leisureCategory)
            ->with('success', 'Activité supprimée.');
    }
}
