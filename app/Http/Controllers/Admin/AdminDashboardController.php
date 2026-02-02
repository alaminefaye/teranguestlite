<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard Super Admin
     */
    public function index()
    {
        $totalEnterprises = Enterprise::count();
        $activeEnterprises = Enterprise::where('status', 'active')->count();
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalStaff = User::where('role', 'staff')->count();
        $totalGuests = User::where('role', 'guest')->count();

        // Statistiques par entreprise (top 5)
        $topEnterprises = Enterprise::withCount(['users'])
            ->orderBy('users_count', 'desc')
            ->take(5)
            ->get();

        return view('pages.admin.dashboard', [
            'title' => 'Dashboard Super Admin',
            'totalEnterprises' => $totalEnterprises,
            'activeEnterprises' => $activeEnterprises,
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalStaff' => $totalStaff,
            'totalGuests' => $totalGuests,
            'topEnterprises' => $topEnterprises,
        ]);
    }
}
