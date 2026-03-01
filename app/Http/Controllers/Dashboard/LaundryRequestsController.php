<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LaundryRequest;
use App\Models\Room;
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
}
