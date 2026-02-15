<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LeisureCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LeisureCategoryController extends Controller
{
    /** Liste des catégories principales uniquement (Sport, Loisirs). */
    public function index(Request $request): View
    {
        $query = LeisureCategory::withCount('children')->topLevel();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->ordered()->paginate(12);

        return view('pages.dashboard.leisure-categories.index', [
            'title' => 'Bien-être, Sport & Loisirs',
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.leisure-categories.create', [
            'title' => 'Ajouter une catégorie principale',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:sport,loisirs',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['parent_id'] = null;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        LeisureCategory::create($validated);

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(LeisureCategory $leisureCategory): View
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        return view('pages.dashboard.leisure-categories.edit', [
            'title' => 'Modifier la catégorie',
            'category' => $leisureCategory,
        ]);
    }

    public function update(Request $request, LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:sport,loisirs',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $leisureCategory->update($validated);

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $leisureCategory->delete();

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
