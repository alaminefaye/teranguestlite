<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockCategory::query()->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->ordered()->paginate(15);

        $stats = [
            'total' => StockCategory::count(),
            'active' => StockCategory::active()->count(),
        ];

        return view('pages.dashboard.stock-categories.index', [
            'title' => 'Catégories de stock',
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.stock-categories.create', [
            'title' => 'Créer une catégorie de stock',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_active'] = $request->boolean('is_active', true);

        StockCategory::create($validated);

        return redirect()->route('dashboard.stock-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(StockCategory $stockCategory): View
    {
        $stockCategory->load(['products' => fn ($q) => $q->orderBy('name')]);
        $stats = [
            'total_products' => $stockCategory->products()->count(),
            'in_alert' => $stockCategory->products()->inAlert()->count(),
        ];
        return view('pages.dashboard.stock-categories.show', [
            'title' => $stockCategory->name,
            'category' => $stockCategory,
            'stats' => $stats,
        ]);
    }

    public function edit(StockCategory $stockCategory): View
    {
        return view('pages.dashboard.stock-categories.edit', [
            'title' => 'Modifier ' . $stockCategory->name,
            'category' => $stockCategory,
        ]);
    }

    public function update(Request $request, StockCategory $stockCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $stockCategory->update($validated);

        return redirect()->route('dashboard.stock-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(StockCategory $stockCategory): RedirectResponse
    {
        if ($stockCategory->products()->count() > 0) {
            return redirect()->route('dashboard.stock-categories.index')
                ->with('error', 'Impossible de supprimer : des produits sont associés à cette catégorie.');
        }
        $stockCategory->delete();
        return redirect()->route('dashboard.stock-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
