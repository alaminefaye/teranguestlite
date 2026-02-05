<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->ordered()->paginate(15);

        $stats = [
            'total' => MenuItem::count(),
            'available' => MenuItem::available()->count(),
            'featured' => MenuItem::featured()->count(),
        ];

        $categories = MenuCategory::active()->ordered()->get();

        return view('pages.dashboard.menu-items.index', [
            'title' => 'Articles de menu',
            'items' => $items,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $categories = MenuCategory::active()->ordered()->get();

        return view('pages.dashboard.menu-items.create', [
            'title' => 'Créer un article',
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:30720',
            'ingredients' => 'nullable|array',
            'allergens' => 'nullable|array',
            'preparation_time' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        MenuItem::create($validated);

        return redirect()->route('dashboard.menu-items.index')
            ->with('success', 'Article créé avec succès !');
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');

        return view('pages.dashboard.menu-items.show', [
            'title' => $menuItem->name,
            'item' => $menuItem,
        ]);
    }

    public function edit(MenuItem $menuItem)
    {
        $categories = MenuCategory::active()->ordered()->get();

        return view('pages.dashboard.menu-items.edit', [
            'title' => 'Modifier ' . $menuItem->name,
            'item' => $menuItem,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:30720',
            'ingredients' => 'nullable|array',
            'allergens' => 'nullable|array',
            'preparation_time' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem->update($validated);

        return redirect()->route('dashboard.menu-items.index')
            ->with('success', 'Article mis à jour avec succès !');
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }

        $menuItem->delete();

        return redirect()->route('dashboard.menu-items.index')
            ->with('success', 'Article supprimé avec succès !');
    }
}
