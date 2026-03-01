<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->ordered()->paginate(15);

        $stats = [
            'total' => MenuCategory::count(),
            'active' => MenuCategory::active()->count(),
            'room_service' => MenuCategory::byType('room_service')->count(),
            'restaurant' => MenuCategory::byType('restaurant')->count(),
        ];

        return view('pages.dashboard.menu-categories.index', [
            'title' => 'Catégories de menu',
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return view('pages.dashboard.menu-categories.create', [
            'title' => 'Créer une catégorie',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|in:room_service,restaurant,bar,spa',
            'status' => 'required|in:active,inactive',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;

        MenuCategory::create($validated);

        return redirect()->route('dashboard.menu-categories.index')
            ->with('success', 'Catégorie créée avec succès !');
    }

    public function show(MenuCategory $menuCategory)
    {
        $menuCategory->load(['menuItems' => function ($query) {
            $query->ordered();
        }]);

        $stats = [
            'total_items' => $menuCategory->menuItems()->count(),
            'available_items' => $menuCategory->menuItems()->available()->count(),
            'featured_items' => $menuCategory->menuItems()->featured()->count(),
        ];

        return view('pages.dashboard.menu-categories.show', [
            'title' => $menuCategory->name,
            'category' => $menuCategory,
            'stats' => $stats,
        ]);
    }

    public function edit(MenuCategory $menuCategory)
    {
        return view('pages.dashboard.menu-categories.edit', [
            'title' => 'Modifier ' . $menuCategory->name,
            'category' => $menuCategory,
        ]);
    }

    public function update(Request $request, MenuCategory $menuCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|in:room_service,restaurant,bar,spa',
            'status' => 'required|in:active,inactive',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $menuCategory->update($validated);

        return redirect()->route('dashboard.menu-categories.index')
            ->with('success', 'Catégorie mise à jour avec succès !');
    }

    public function destroy(MenuCategory $menuCategory)
    {
        if ($menuCategory->menuItems()->count() > 0) {
            return redirect()->route('dashboard.menu-categories.index')
                ->with('error', 'Impossible de supprimer cette catégorie : des articles y sont associés.');
        }

        $menuCategory->delete();

        return redirect()->route('dashboard.menu-categories.index')
            ->with('success', 'Catégorie supprimée avec succès !');
    }
}
