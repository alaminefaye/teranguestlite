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
        $query = ExcursionBooking::with(['excursion', 'user', 'guest', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('excursion', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('room', fn ($q2) => $q2->where('room_number', 'like', '%' . $search . '%'));
            });
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

    public function show(ExcursionBooking $excursionBooking): View
    {
        $excursionBooking->load(['excursion', 'user', 'guest', 'room']);
        return view('pages.dashboard.excursion-bookings.show', ['booking' => $excursionBooking]);
    }
}
