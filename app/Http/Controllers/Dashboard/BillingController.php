<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\StaffSection;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Facturation / Notes de chambre (type Opera, Oracle).
 * Vue centralisée des séjours et de leur état de facturation (à régler / réglé).
 */
class BillingController extends Controller
{
    private function getEnterpriseId(): int
    {
        $id = auth()->user()->enterprise_id;
        if (!$id) {
            abort(403, 'Accès réservé à un établissement.');
        }
        return $id;
    }

    private function canAccessBilling(): bool
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isStaff()) {
            $sections = $user->managed_sections ?? [];
            return in_array(StaffSection::BILLING_INVOICING, $sections, true);
        }
        return false;
    }

    /**
     * Liste des réservations avec état de facturation (note de chambre).
     */
    public function index(Request $request): View
    {
        if (!$this->canAccessBilling()) {
            abort(403, 'Accès réservé au service Facturation ou à l\'administrateur.');
        }

        $query = Reservation::with(['room', 'guest', 'settlements']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reservation_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('guest', fn ($g) => $g->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $reservations = $query->orderByDesc('check_in')->paginate(15);

        // Pour chaque résa : montant dû (commandes room_bill non réglées) et montant déjà réglé
        $rows = [];
        foreach ($reservations as $r) {
            $totalDue = $r->roomBillOrdersUnsettled()->sum('total');
            $totalPaid = $r->settlements->sum('amount');
            $rows[] = (object) [
                'reservation' => $r,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'has_balance' => $totalDue > 0,
            ];
        }

        $stats = [
            'total_reservations' => Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->count(),
            'with_balance' => 0,
        ];
        foreach ($rows as $row) {
            if ($row->has_balance) {
                $stats['with_balance']++;
            }
        }

        return view('pages.dashboard.billing.index', [
            'title' => 'Facturation / Notes de chambre',
            'rows' => $rows,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
