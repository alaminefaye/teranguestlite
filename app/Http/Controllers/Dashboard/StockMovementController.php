<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockMovement::query()->with(['product.category', 'user']);

        if ($request->filled('product_id')) {
            $query->where('stock_product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderByDesc('created_at')->paginate(25);

        $products = StockProduct::active()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('pages.dashboard.stock-movements.index', [
            'title' => 'Mouvements de stock',
            'movements' => $movements,
            'products' => $products,
        ]);
    }

    public function create(Request $request): View
    {
        $products = StockProduct::active()->with('category')->orderBy('name')->get();
        $productId = $request->get('product_id');
        return view('pages.dashboard.stock-movements.create', [
            'title' => 'Nouveau mouvement',
            'products' => $products,
            'preselectedProductId' => $productId ? (int) $productId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_product_id' => 'required|exists:stock_products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = StockProduct::findOrFail($validated['stock_product_id']);
        $qty = (float) $validated['quantity'];
        $type = $validated['type'];

        if ($type === 'out') {
            if ($product->quantity_current < $qty) {
                return back()->withInput()->with('error', 'Stock insuffisant. Disponible : ' . $product->quantity_current . ' ' . $product->unit_label);
            }
        }
        if ($type === 'adjustment') {
            $sign = $request->input('adjustment_direction', 'add') === 'subtract' ? -1 : 1;
            $qty = $qty * $sign;
            $newQty = $product->quantity_current + $qty;
            if ($newQty < 0) {
                return back()->withInput()->with('error', 'Ajustement impossible : le stock ne peut pas être négatif.');
            }
        }

        $delta = $type === 'out' ? -$qty : ($type === 'adjustment' ? $qty : $qty);
        DB::transaction(function () use ($validated, $product, $delta, $type, $qty) {
            $movement = new StockMovement([
                'enterprise_id' => auth()->user()->enterprise_id,
                'stock_product_id' => $product->id,
                'type' => $type,
                'quantity' => $type === 'adjustment' ? $delta : $qty,
                'unit_cost' => $validated['unit_cost'] ?? null,
                'reference_type' => 'manual',
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);
            $movement->save();

            if ($type === 'in') {
                $product->increment('quantity_current', $qty);
            } elseif ($type === 'out') {
                $product->decrement('quantity_current', $qty);
            } else {
                $product->increment('quantity_current', $delta);
            }
        });

        $message = $type === 'in' ? 'Entrée enregistrée.' : ($type === 'out' ? 'Sortie enregistrée.' : 'Ajustement enregistré.');
        return redirect()->route('dashboard.stock-movements.index')
            ->with('success', $message);
    }
}
