<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PalaceRequest;
use App\Models\PalaceService;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PalaceRequestsController extends Controller
{
    public function index(Request $request): View
    {
        $query = PalaceRequest::with(['palaceService', 'user', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('palace_service_id')) {
            $query->where('palace_service_id', $request->palace_service_id);
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
                    ->orWhereHas('palaceService', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                    ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => PalaceRequest::count(),
            'pending' => PalaceRequest::where('status', 'pending')->count(),
            'today' => PalaceRequest::whereDate('created_at', today())->count(),
        ];

        $palaceServices = PalaceService::orderBy('name')->get(['id', 'name']);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return view('pages.dashboard.palace-requests.index', compact('requests', 'stats', 'palaceServices', 'rooms'));
    }

    public function show(PalaceRequest $palaceRequest): View
    {
        $palaceRequest->load(['palaceService', 'user', 'room']);
        return view('pages.dashboard.palace-requests.show', [
            'request' => $palaceRequest,
        ]);
    }
}
