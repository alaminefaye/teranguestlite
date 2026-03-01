<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use App\Models\StockProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockProduct::query()->with('category');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', '%' . $q . '%')
                    ->orWhere('sku', 'like', '%' . $q . '%')
                    ->orWhere('barcode', 'like', '%' . $q . '%');
            });
        }
        if ($request->filled('category')) {
            $query->where('stock_category_id', $request->category);
        }
        if ($request->filled('alert')) {
            if ($request->alert === 'yes') {
                $query->inAlert();
            }
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->orderBy('name')->paginate(20);

        $stats = [
            'total' => StockProduct::count(),
            'active' => StockProduct::active()->count(),
            'in_alert' => StockProduct::inAlert()->count(),
            'total_value' => StockProduct::selectRaw('SUM(quantity_current * COALESCE(unit_cost, 0)) as total')->value('total') ?? 0,
        ];

        $categories = StockCategory::active()->ordered()->get();

        return view('pages.dashboard.stock-products.index', [
            'title' => 'Produits / Stock',
            'products' => $products,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): View
    {
        $categories = StockCategory::active()->ordered()->get();
        $preselectedCategoryId = $request->filled('category') ? (int) $request->category : null;
        return view('pages.dashboard.stock-products.create', [
            'title' => 'Nouveau produit',
            'categories' => $categories,
            'preselectedCategoryId' => $preselectedCategoryId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_category_id' => 'required|exists:stock_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:80',
            'barcode' => 'nullable|string|max:80',
            'unit' => 'required|string|in:' . implode(',', array_keys(StockProduct::UNITS)),
            'quantity_current' => 'nullable|numeric|min:0',
            'quantity_min' => 'nullable|numeric|min:0',
            'quantity_max' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['quantity_current'] = $validated['quantity_current'] ?? 0;
        $validated['quantity_min'] = $validated['quantity_min'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        StockProduct::create($validated);

        return redirect()->route('dashboard.stock-products.index')
            ->with('success', 'Produit créé.');
    }

    public function show(StockProduct $stockProduct): View
    {
        $stockProduct->load('category');
        $movements = $stockProduct->movements()->with('user')->orderByDesc('created_at')->limit(50)->get();
        return view('pages.dashboard.stock-products.show', [
            'title' => $stockProduct->name,
            'product' => $stockProduct,
            'movements' => $movements,
        ]);
    }

    public function edit(StockProduct $stockProduct): View
    {
        $categories = StockCategory::active()->ordered()->get();
        return view('pages.dashboard.stock-products.edit', [
            'title' => 'Modifier ' . $stockProduct->name,
            'product' => $stockProduct,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, StockProduct $stockProduct): RedirectResponse
    {
        $validated = $request->validate([
            'stock_category_id' => 'required|exists:stock_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:80',
            'barcode' => 'nullable|string|max:80',
            'unit' => 'required|string|in:' . implode(',', array_keys(StockProduct::UNITS)),
            'quantity_min' => 'nullable|numeric|min:0',
            'quantity_max' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $stockProduct->update($validated);

        return redirect()->route('dashboard.stock-products.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(StockProduct $stockProduct): RedirectResponse
    {
        if ($stockProduct->movements()->exists()) {
            return redirect()->route('dashboard.stock-products.index')
                ->with('error', 'Impossible de supprimer : des mouvements existent. Désactivez le produit si besoin.');
        }
        $stockProduct->delete();
        return redirect()->route('dashboard.stock-products.index')
            ->with('success', 'Produit supprimé.');
    }
}
