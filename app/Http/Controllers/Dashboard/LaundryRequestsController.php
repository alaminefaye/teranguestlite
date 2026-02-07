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
        $query = LaundryRequest::with(['user', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('request_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => LaundryRequest::count(),
            'pending' => LaundryRequest::where('status', 'pending')->count(),
            'today' => LaundryRequest::whereDate('created_at', today())->count(),
        ];

        return view('pages.dashboard.laundry-requests.index', compact('requests', 'stats'));
    }
}
