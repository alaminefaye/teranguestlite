<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        if ($request->filled('search')) {
            $query->whereHas('excursion', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        $bookings = $query->orderBy('booking_date', 'desc')->paginate(15);

        $stats = [
            'total' => ExcursionBooking::count(),
            'confirmed' => ExcursionBooking::where('status', 'confirmed')->count(),
            'today' => ExcursionBooking::whereDate('booking_date', today())->count(),
        ];

        return view('pages.dashboard.excursion-bookings.index', compact('bookings', 'stats'));
    }
}
