<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Excursion;
use App\Models\ExcursionBooking;
use Illuminate\Http\RedirectResponse;
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

    public function show(ExcursionBooking $excursionBooking): View
    {
        $excursionBooking->load(['excursion', 'user', 'room']);
        return view('pages.dashboard.excursion-bookings.show', ['booking' => $excursionBooking]);
    }

    public function edit(ExcursionBooking $excursionBooking): View
    {
        $excursionBooking->load(['excursion', 'user', 'room']);
        $excursions = Excursion::orderBy('name')->get(['id', 'name']);
        return view('pages.dashboard.excursion-bookings.edit', ['booking' => $excursionBooking, 'excursions' => $excursions]);
    }

    public function update(Request $request, ExcursionBooking $excursionBooking): RedirectResponse
    {
        if ($excursionBooking->status === 'cancelled') {
            return redirect()->route('dashboard.excursion-bookings.show', $excursionBooking)->with('error', 'Une réservation annulée ne peut pas être modifiée.');
        }
        $validated = $request->validate([
            'booking_date' => 'required|date',
            'number_of_adults' => 'required|integer|min:0',
            'number_of_children' => 'required|integer|min:0',
            'special_requests' => 'nullable|string|max:500',
            'status' => 'required|in:pending,confirmed',
        ]);
        $excursionBooking->update([
            'booking_date' => $validated['booking_date'],
            'number_of_adults' => $validated['number_of_adults'],
            'number_of_children' => $validated['number_of_children'],
            'special_requests' => $validated['special_requests'],
            'status' => $validated['status'],
        ]);
        return redirect()->route('dashboard.excursion-bookings.show', $excursionBooking)->with('success', 'Réservation mise à jour.');
    }

    public function cancel(Request $request, ExcursionBooking $excursionBooking): RedirectResponse
    {
        if ($excursionBooking->status === 'cancelled') {
            return redirect()->back()->with('info', 'Cette réservation est déjà annulée.');
        }
        $excursionBooking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('cancellation_reason'),
        ]);
        return redirect()->route('dashboard.excursion-bookings.index')->with('success', 'Réservation annulée.');
    }
}
