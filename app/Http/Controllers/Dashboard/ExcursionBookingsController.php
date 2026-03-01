<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Excursion;
use App\Models\ExcursionBooking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExcursionBookingsController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExcursionBooking::with(['excursion', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('excursion_id')) {
            $query->where('excursion_id', $request->excursion_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('excursion', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                    ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $bookings = $query->orderBy('booking_date', 'desc')->paginate(15);

        $stats = [
            'total' => ExcursionBooking::count(),
            'confirmed' => ExcursionBooking::where('status', 'confirmed')->count(),
            'today' => ExcursionBooking::whereDate('booking_date', today())->count(),
        ];

        $excursions = Excursion::orderBy('name')->get(['id', 'name']);

        return view('pages.dashboard.excursion-bookings.index', compact('bookings', 'stats', 'excursions'));
    }
}
