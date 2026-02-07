<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SpaReservation;
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
        if ($request->filled('search')) {
            $query->whereHas('spaService', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc')->paginate(15);

        $stats = [
            'total' => SpaReservation::count(),
            'confirmed' => SpaReservation::where('status', 'confirmed')->count(),
            'today' => SpaReservation::whereDate('reservation_date', today())->count(),
        ];

        return view('pages.dashboard.spa-reservations.index', compact('reservations', 'stats'));
    }
}
