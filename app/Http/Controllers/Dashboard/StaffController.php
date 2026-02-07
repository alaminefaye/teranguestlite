<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Liste du personnel (staff) de l'hôtel.
     */
    public function index(Request $request): View
    {
        $enterpriseId = auth()->user()->enterprise_id;
        if (!$enterpriseId) {
            abort(403, 'Accès réservé à un administrateur d\'hôtel.');
        }

        $query = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'staff')
            ->orderBy('department')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $staff = $query->paginate(15);

        $departments = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'staff')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();

        $total = User::where('enterprise_id', $enterpriseId)->where('role', 'staff')->count();

        return view('pages.dashboard.staff.index', [
            'title' => 'Staff',
            'staff' => $staff,
            'departments' => $departments,
            'total' => $total,
        ]);
    }
}
