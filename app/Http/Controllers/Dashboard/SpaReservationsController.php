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
        $query = SpaReservation::with(['spaService', 'user', 'guest', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('spaService', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('room', fn ($q2) => $q2->where('room_number', 'like', '%' . $search . '%'));
            });
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

    public function show(SpaReservation $spaReservation): View
    {
        $spaReservation->load(['spaService', 'user', 'guest', 'room']);
        return view('pages.dashboard.spa-reservations.show', ['reservation' => $spaReservation]);
    }
}
