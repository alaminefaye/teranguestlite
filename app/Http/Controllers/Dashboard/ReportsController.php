<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ExcursionBooking;
use App\Models\Guest;
use App\Models\LaundryRequest;
use App\Models\Order;
use App\Models\PalaceRequest;
use App\Models\Reservation;
use App\Models\RestaurantReservation;
use App\Models\SpaReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private function getEnterpriseId(): int
    {
        $id = auth()->user()->enterprise_id;
        if (!$id) {
            abort(403, 'Accès réservé à un établissement.');
        }
        return $id;
    }

    /**
     * Liste des types de rapports + formulaire période.
     */
    public function index(Request $request): View
    {
        $types = [
            'global' => ['name' => 'Rapport global complet', 'description' => 'Tout en un : CA et nombre de réservations par mois, commandes, tous les services (spa, restaurant, blanchisserie, palace, excursions), clients. Idéal pour audits et bilans.'],
            'overview' => ['name' => 'Synthèse globale', 'description' => 'Vue d\'ensemble : réservations, commandes, chiffre d\'affaires, clients'],
            'reservations' => ['name' => 'Rapport réservations', 'description' => 'Liste des réservations chambres sur la période'],
            'orders' => ['name' => 'Rapport commandes', 'description' => 'Liste des commandes (room service, etc.)'],
            'billing' => ['name' => 'Rapport facturation', 'description' => 'État des notes de chambre et encaissements'],
            'services' => ['name' => 'Rapport services', 'description' => 'Spa, restaurants, blanchisserie, palace, excursions'],
            'audit' => ['name' => 'Journal d\'audit', 'description' => 'Historique des actions (création, modification, check-in/out)'],
        ];

        return view('pages.dashboard.reports.index', [
            'title' => 'Rapports & Audits',
            'types' => $types,
            'date_from' => $request->get('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->get('date_to', now()->format('Y-m-d')),
        ]);
    }

    /**
     * Afficher un rapport (données à l'écran).
     */
    public function show(Request $request, string $type): View|StreamedResponse
    {
        $validTypes = ['global', 'overview', 'reservations', 'orders', 'billing', 'services', 'audit'];
        if (!in_array($type, $validTypes, true)) {
            abort(404);
        }

        $enterpriseId = $this->getEnterpriseId();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($type, $enterpriseId, $dateFrom, $dateTo);
        }

        $data = $this->buildReportData($type, $enterpriseId, $dateFrom, $dateTo, false);

        return view('pages.dashboard.reports.show', [
            'title' => 'Rapport : ' . $this->getReportTypeName($type),
            'type' => $type,
            'data' => $data,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    private function getReportTypeName(string $type): string
    {
        $names = [
            'global' => 'Rapport global complet',
            'overview' => 'Synthèse globale',
            'reservations' => 'Réservations',
            'orders' => 'Commandes',
            'billing' => 'Facturation',
            'services' => 'Services',
            'audit' => 'Journal d\'audit',
        ];
        return $names[$type] ?? $type;
    }

    private function buildReportData(string $type, int $enterpriseId, string $dateFrom, string $dateTo, bool $paginateAudit = true): array
    {
        $baseReservations = Reservation::where('enterprise_id', $enterpriseId)
            ->whereDate('check_in', '<=', $dateTo)
            ->whereDate('check_out', '>=', $dateFrom);

        $baseOrders = Order::where('enterprise_id', $enterpriseId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        switch ($type) {
            case 'global':
                return $this->buildGlobalReportData($enterpriseId, $dateFrom, $dateTo);

            case 'overview':
                $reservationsCount = (clone $baseReservations)->count();
                $reservationsRevenue = (clone $baseReservations)->sum('total_price');
                $ordersCount = (clone $baseOrders)->count();
                $ordersRevenue = (clone $baseOrders)->sum('total');
                $newGuests = Guest::where('enterprise_id', $enterpriseId)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->count();
                return [
                    'reservations_count' => $reservationsCount,
                    'reservations_revenue' => $reservationsRevenue,
                    'orders_count' => $ordersCount,
                    'orders_revenue' => $ordersRevenue,
                    'total_revenue' => $reservationsRevenue + $ordersRevenue,
                    'new_guests' => $newGuests,
                ];

            case 'reservations':
                $items = $baseReservations->with(['room', 'guest', 'user'])
                    ->orderBy('check_in', 'desc')
                    ->get();
                return ['items' => $items];

            case 'orders':
                $items = $baseOrders->with(['room', 'guest', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                return ['items' => $items];

            case 'billing':
                $reservations = $baseReservations->with(['room', 'guest', 'settlements'])->get();
                $rows = [];
                foreach ($reservations as $r) {
                    $totalDue = $r->roomBillOrdersUnsettled()->sum('total');
                    $totalPaid = $r->settlements->sum('amount');
                    $rows[] = (object)[
                        'reservation' => $r,
                        'total_due' => $totalDue,
                        'total_paid' => $totalPaid,
                    ];
                }
                $totalDue = array_sum(array_map(fn ($r) => $r->total_due, $rows));
                $totalPaid = array_sum(array_map(fn ($r) => $r->total_paid, $rows));
                return ['rows' => $rows, 'total_due' => $totalDue, 'total_paid' => $totalPaid];

            case 'services':
                $spa = SpaReservation::where('enterprise_id', $enterpriseId)
                    ->whereBetween('reservation_date', [$dateFrom, $dateTo])->count();
                $restaurant = RestaurantReservation::where('enterprise_id', $enterpriseId)
                    ->whereBetween('reservation_date', [$dateFrom, $dateTo])->count();
                $laundry = LaundryRequest::where('enterprise_id', $enterpriseId)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
                $palace = PalaceRequest::where('enterprise_id', $enterpriseId)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
                $excursions = ExcursionBooking::where('enterprise_id', $enterpriseId)
                    ->whereBetween('booking_date', [$dateFrom, $dateTo])->count();
                return [
                    'spa' => $spa,
                    'restaurant' => $restaurant,
                    'laundry' => $laundry,
                    'palace' => $palace,
                    'excursions' => $excursions,
                ];

            case 'audit':
                $query = ActivityLog::forEnterprise($enterpriseId)
                    ->betweenDates($dateFrom, $dateTo)
                    ->with('user')
                    ->orderBy('created_at', 'desc');
                $items = $paginateAudit ? $query->paginate(50) : $query->limit(5000)->get();
                return ['items' => $items];
        }

        return [];
    }

    /**
     * Rapport global complet : tous les indicateurs par mois + totaux.
     */
    private function buildGlobalReportData(int $enterpriseId, string $dateFrom, string $dateTo): array
    {
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();
        $months = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $monthKey = $current->format('Y-m');
            $monthStart = $current->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $current->copy()->endOfMonth()->format('Y-m-d');
            $monthStartDt = $current->copy()->startOfMonth();
            $monthEndDt = $current->copy()->endOfMonth();

            // Réservations chambres : check-in dans le mois (pour CA et nombre cohérents)
            $reservationsQuery = Reservation::where('enterprise_id', $enterpriseId)
                ->whereBetween('check_in', [$monthStartDt->startOfDay(), $monthEndDt->endOfDay()]);
            $reservations_count = (clone $reservationsQuery)->count();
            $reservations_revenue = (clone $reservationsQuery)->sum('total_price');

            // Commandes (créées dans le mois)
            $ordersQuery = Order::where('enterprise_id', $enterpriseId)
                ->whereBetween('created_at', [$monthStartDt->format('Y-m-d 00:00:00'), $monthEndDt->format('Y-m-d 23:59:59')]);
            $orders_count = (clone $ordersQuery)->count();
            $orders_revenue = (clone $ordersQuery)->sum('total');

            // Services
            $spa = SpaReservation::where('enterprise_id', $enterpriseId)
                ->whereBetween('reservation_date', [$monthStart, $monthEnd])->count();
            $restaurant = RestaurantReservation::where('enterprise_id', $enterpriseId)
                ->whereBetween('reservation_date', [$monthStart, $monthEnd])->count();
            $laundry = LaundryRequest::where('enterprise_id', $enterpriseId)
                ->whereBetween('created_at', [$monthStartDt, $monthEndDt])->count();
            $palace = PalaceRequest::where('enterprise_id', $enterpriseId)
                ->whereBetween('created_at', [$monthStartDt, $monthEndDt])->count();
            $excursions = ExcursionBooking::where('enterprise_id', $enterpriseId)
                ->whereBetween('booking_date', [$monthStart, $monthEnd])->count();

            // Nouveaux clients (créés dans le mois)
            $new_guests = Guest::where('enterprise_id', $enterpriseId)
                ->whereBetween('created_at', [$monthStartDt, $monthEndDt])->count();

            $monthsFr = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
            $months[$monthKey] = [
                'label' => $monthsFr[(int) $current->format('n') - 1] . ' ' . $current->format('Y'),
                'reservations_count' => $reservations_count,
                'reservations_revenue' => (float) $reservations_revenue,
                'orders_count' => $orders_count,
                'orders_revenue' => (float) $orders_revenue,
                'spa' => $spa,
                'restaurant' => $restaurant,
                'laundry' => $laundry,
                'palace' => $palace,
                'excursions' => $excursions,
                'new_guests' => $new_guests,
            ];
            $current->addMonth();
        }

        // Totaux sur toute la période
        $totals = [
            'reservations_count' => 0,
            'reservations_revenue' => 0,
            'orders_count' => 0,
            'orders_revenue' => 0,
            'spa' => 0,
            'restaurant' => 0,
            'laundry' => 0,
            'palace' => 0,
            'excursions' => 0,
            'new_guests' => 0,
        ];
        foreach ($months as $row) {
            $totals['reservations_count'] += $row['reservations_count'];
            $totals['reservations_revenue'] += $row['reservations_revenue'];
            $totals['orders_count'] += $row['orders_count'];
            $totals['orders_revenue'] += $row['orders_revenue'];
            $totals['spa'] += $row['spa'];
            $totals['restaurant'] += $row['restaurant'];
            $totals['laundry'] += $row['laundry'];
            $totals['palace'] += $row['palace'];
            $totals['excursions'] += $row['excursions'];
            $totals['new_guests'] += $row['new_guests'];
        }
        $totals['total_revenue'] = $totals['reservations_revenue'] + $totals['orders_revenue'];

        return [
            'months' => $months,
            'totals' => $totals,
        ];
    }

    private function exportCsv(string $type, int $enterpriseId, string $dateFrom, string $dateTo): StreamedResponse
    {
        $data = $this->buildReportData($type, $enterpriseId, $dateFrom, $dateTo, false);
        $filename = "rapport-{$type}-{$dateFrom}-{$dateTo}.csv";

        return response()->streamDownload(function () use ($type, $data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Rapport: ' . $this->getReportTypeName($type)]);
            fputcsv($out, []);

            switch ($type) {
                case 'global':
                    fputcsv($out, ['Mois', 'Réservations (nbre)', 'CA Hébergement (FCFA)', 'Commandes (nbre)', 'CA Commandes (FCFA)', 'Spa', 'Restaurants', 'Blanchisserie', 'Palace', 'Excursions', 'Nouveaux clients']);
                    foreach ($data['months'] ?? [] as $monthKey => $row) {
                        fputcsv($out, [
                            $row['label'],
                            $row['reservations_count'],
                            $row['reservations_revenue'],
                            $row['orders_count'],
                            $row['orders_revenue'],
                            $row['spa'],
                            $row['restaurant'],
                            $row['laundry'],
                            $row['palace'],
                            $row['excursions'],
                            $row['new_guests'],
                        ]);
                    }
                    fputcsv($out, []);
                    fputcsv($out, ['TOTAL', $data['totals']['reservations_count'] ?? 0, $data['totals']['reservations_revenue'] ?? 0, $data['totals']['orders_count'] ?? 0, $data['totals']['orders_revenue'] ?? 0, $data['totals']['spa'] ?? 0, $data['totals']['restaurant'] ?? 0, $data['totals']['laundry'] ?? 0, $data['totals']['palace'] ?? 0, $data['totals']['excursions'] ?? 0, $data['totals']['new_guests'] ?? 0]);
                    break;
                case 'reservations':
                    fputcsv($out, ['Référence', 'Chambre', 'Client', 'Check-in', 'Check-out', 'Statut', 'Montant (FCFA)']);
                    foreach ($data['items'] ?? [] as $r) {
                        fputcsv($out, [
                            $r->reservation_number,
                            $r->room?->room_number ?? '—',
                            $r->guest?->name ?? '—',
                            $r->check_in?->format('d/m/Y'),
                            $r->check_out?->format('d/m/Y'),
                            $r->status ?? '',
                            $r->total_price ?? 0,
                        ]);
                    }
                    break;
                case 'orders':
                    fputcsv($out, ['N° Commande', 'Chambre', 'Client', 'Date', 'Statut', 'Paiement', 'Total (FCFA)']);
                    foreach ($data['items'] ?? [] as $o) {
                        fputcsv($out, [
                            $o->order_number,
                            $o->room?->room_number ?? '—',
                            $o->guest?->name ?? $o->user?->name ?? '—',
                            $o->created_at?->format('d/m/Y H:i'),
                            $o->status ?? '',
                            $o->payment_method ?? '',
                            $o->total ?? 0,
                        ]);
                    }
                    break;
                case 'audit':
                    fputcsv($out, ['Date/Heure', 'Utilisateur', 'Action', 'Description', 'Modèle']);
                    foreach ($data['items'] ?? [] as $a) {
                        fputcsv($out, [
                            $a->created_at?->format('d/m/Y H:i'),
                            $a->user?->name ?? '—',
                            $a->action,
                            $a->description ?? '',
                            $a->model_type ?? '',
                        ]);
                    }
                    break;
                default:
                    fputcsv($out, ['Données non exportables en CSV pour ce type. Utilisez la vue à l\'écran.']);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
