<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PalaceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PalaceRequestsController extends Controller
{
    public function index(Request $request): View
    {
        $query = PalaceRequest::with(['palaceService', 'user', 'guest', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', '%' . $search . '%')
                    ->orWhereHas('palaceService', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('guest', fn ($q2) => $q2->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%'))
                    ->orWhereHas('room', fn ($q2) => $q2->where('room_number', 'like', '%' . $search . '%'));
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => PalaceRequest::count(),
            'pending' => PalaceRequest::where('status', 'pending')->count(),
            'today' => PalaceRequest::whereDate('created_at', today())->count(),
        ];

        return view('pages.dashboard.palace-requests.index', compact('requests', 'stats'));
    }

    public function show(PalaceRequest $palaceRequest): View
    {
        $palaceRequest->load(['palaceService', 'user', 'guest', 'room']);
        return view('pages.dashboard.palace-requests.show', [
            'request' => $palaceRequest,
        ]);
    }
}
