<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use App\Models\StockProduct;
use App\Models\StockMovement;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Tableau de bord stocks : vue d'ensemble + alertes seuils.
     */
    public function index(): View
    {
        $stats = [
            'categories_count' => StockCategory::count(),
            'products_count' => StockProduct::count(),
            'products_in_alert' => StockProduct::inAlert()->count(),
            'total_value' => StockProduct::selectRaw('SUM(quantity_current * COALESCE(unit_cost, 0)) as total')->value('total') ?? 0,
            'movements_today' => StockMovement::whereDate('created_at', today())->count(),
        ];

        $alerts = StockProduct::inAlert()
            ->with('category')
            ->orderByRaw('quantity_current ASC')
            ->limit(30)
            ->get();

        $recentMovements = StockMovement::with(['product.category', 'user'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('pages.dashboard.stock.index', [
            'title' => 'Gestion des stocks',
            'stats' => $stats,
            'alerts' => $alerts,
            'recentMovements' => $recentMovements,
        ]);
    }
}
