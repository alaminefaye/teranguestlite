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
        $query = PalaceRequest::with(['palaceService', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('request_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('palaceService', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => PalaceRequest::count(),
            'pending' => PalaceRequest::where('status', 'pending')->count(),
            'today' => PalaceRequest::whereDate('created_at', today())->count(),
        ];

        return view('pages.dashboard.palace-requests.index', compact('requests', 'stats'));
    }
}
