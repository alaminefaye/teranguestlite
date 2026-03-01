<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LaundryRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaundryRequestsController extends Controller
{
    public function index(Request $request): View
    {
        $query = LaundryRequest::with(['user', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => LaundryRequest::count(),
            'pending' => LaundryRequest::where('status', 'pending')->count(),
            'today' => LaundryRequest::whereDate('created_at', today())->count(),
        ];

        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return view('pages.dashboard.laundry-requests.index', compact('requests', 'stats', 'rooms'));
    }

    public function show(LaundryRequest $laundryRequest): View
    {
        $laundryRequest->load(['user', 'room']);
        return view('pages.dashboard.laundry-requests.show', ['request' => $laundryRequest]);
    }

    public function edit(LaundryRequest $laundryRequest): View
    {
        $laundryRequest->load(['user', 'room']);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);
        return view('pages.dashboard.laundry-requests.edit', ['request' => $laundryRequest, 'rooms' => $rooms]);
    }

    public function update(Request $request, LaundryRequest $laundryRequest): RedirectResponse
    {
        if ($laundryRequest->status === 'cancelled') {
            return redirect()->route('dashboard.laundry-requests.show', $laundryRequest)->with('error', 'Une demande annulée ne peut pas être modifiée.');
        }
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'pickup_time' => 'nullable|date',
            'delivery_time' => 'nullable|date',
            'special_instructions' => 'nullable|string|max:500',
        ]);
        $laundryRequest->update([
            'status' => $validated['status'],
            'pickup_time' => $validated['pickup_time'] ?? $laundryRequest->pickup_time,
            'delivery_time' => $validated['delivery_time'] ?? $laundryRequest->delivery_time,
            'special_instructions' => $validated['special_instructions'],
        ]);
        return redirect()->route('dashboard.laundry-requests.show', $laundryRequest)->with('success', 'Demande mise à jour.');
    }

    public function cancel(Request $request, LaundryRequest $laundryRequest): RedirectResponse
    {
        if ($laundryRequest->status === 'cancelled') {
            return redirect()->back()->with('info', 'Cette demande est déjà annulée.');
        }
        $laundryRequest->update(['status' => 'cancelled']);
        return redirect()->route('dashboard.laundry-requests.index')->with('success', 'Demande annulée.');
    }
}
