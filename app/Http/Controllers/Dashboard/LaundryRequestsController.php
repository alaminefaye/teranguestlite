<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LaundryRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaundryRequestsController extends Controller
{
    public function index(Request $request): View
    {
        $query = LaundryRequest::with(['user', 'room', 'guest']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('room', fn ($q2) => $q2->where('room_number', 'like', '%' . $search . '%'));
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => LaundryRequest::count(),
            'pending' => LaundryRequest::where('status', 'pending')->count(),
            'today' => LaundryRequest::whereDate('created_at', today())->count(),
        ];

        return view('pages.dashboard.laundry-requests.index', compact('requests', 'stats'));
    }

    public function show(LaundryRequest $laundryRequest): View
    {
        $laundryRequest->load(['user', 'room', 'guest']);
        return view('pages.dashboard.laundry-requests.show', ['request' => $laundryRequest]);
    }
}
