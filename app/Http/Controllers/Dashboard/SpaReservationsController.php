<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SpaReservation;
use App\Models\SpaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaReservationsController extends Controller
{
    public function index(Request $request): View
    {
        $query = SpaReservation::with(['spaService', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('spa_service_id')) {
            $query->where('spa_service_id', $request->spa_service_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('reservation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reservation_date', '<=', $request->date_to);
        }
        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('spaService', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                    ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc')->paginate(15);

        $stats = [
            'total' => SpaReservation::count(),
            'confirmed' => SpaReservation::where('status', 'confirmed')->count(),
            'today' => SpaReservation::whereDate('reservation_date', today())->count(),
        ];

        $spaServices = SpaService::orderBy('name')->get(['id', 'name']);

        return view('pages.dashboard.spa-reservations.index', compact('reservations', 'stats', 'spaServices'));
    }
}
