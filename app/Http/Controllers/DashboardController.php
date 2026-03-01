<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\RestaurantReservation;
use App\Models\SpaReservation;
use App\Models\LaundryRequest;
use App\Models\PalaceRequest;
use App\Models\ExcursionBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Statistiques chambres
        $totalRooms = Room::count();
        $availableRooms = Room::available()->count();
        $occupiedRooms = Room::occupied()->count();
        $maintenanceRooms = Room::maintenance()->count();

        // Statistiques réservations hébergement
        $totalReservations = Reservation::count();
        $checkInsToday = Reservation::checkInToday()->count();
        $checkOutsToday = Reservation::checkOutToday()->count();
        $pendingReservations = Reservation::pending()->count();
        $confirmedReservations = Reservation::confirmed()->count();

        // Commandes Room Service
        $ordersTotal = Order::count();
        $ordersToday = Order::whereDate('created_at', today())->count();
        $ordersPending = Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering'])->count();
        $ordersDelivered = Order::where('status', 'delivered')->count();

        // Réservations restaurant
        $restaurantReservationsTotal = RestaurantReservation::count();
        $restaurantReservationsToday = RestaurantReservation::whereDate('reservation_date', today())->count();
        $restaurantReservationsPending = RestaurantReservation::whereIn('status', ['pending', 'confirmed'])->count();

        // Réservations spa
        $spaReservationsTotal = SpaReservation::count();
        $spaReservationsToday = SpaReservation::whereDate('reservation_date', today())->count();
        $spaReservationsPending = SpaReservation::whereIn('status', ['pending', 'confirmed'])->count();

        // Blanchisserie
        $laundryRequestsTotal = LaundryRequest::count();
        $laundryRequestsPending = LaundryRequest::whereNotIn('status', ['delivered', 'cancelled'])->count();

        // Services palace / conciergerie
        $palaceRequestsTotal = PalaceRequest::count();
        $palaceRequestsPending = PalaceRequest::whereNotIn('status', ['completed', 'cancelled'])->count();

        // Excursions
        $excursionBookingsTotal = ExcursionBooking::count();
        $excursionBookingsPending = ExcursionBooking::whereIn('status', ['pending', 'confirmed'])->count();

        // Courbe : commandes par jour sur les 14 derniers jours
        $ordersChartLabels = [];
        $ordersChartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $ordersChartLabels[] = $date->format('d/m');
            $ordersChartData[] = Order::whereDate('created_at', $date)->count();
        }

        // Courbe : chiffre d'affaires commandes (total par jour) sur 14 jours
        $revenueChartLabels = [];
        $revenueChartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenueChartLabels[] = $date->format('d/m');
            $revenueChartData[] = (int) Order::whereDate('created_at', $date)->sum('total');
        }

        // Articles les plus commandés (top 10)
        $topOrderedItems = OrderItem::query()
            ->whereHas('order')
            ->select('item_name', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('item_name')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

        // Répartition des commandes par statut (pour diagramme)
        $statusLabelsMap = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'preparing' => 'En préparation',
            'ready' => 'Prête',
            'delivering' => 'En livraison',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];
        $ordersByStatusRaw = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
        $ordersByStatusLabels = $ordersByStatusRaw->map(fn ($r) => $statusLabelsMap[$r->status] ?? $r->status)->values()->toArray();
        $ordersByStatusData = $ordersByStatusRaw->pluck('count')->values()->toArray();

        // Réservations récentes
        $recentReservations = Reservation::with(['room', 'user', 'guest'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Chambres par type
        $roomsByType = Room::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        return view('pages.dashboard.index', [
            'title' => 'Dashboard',
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'totalReservations' => $totalReservations,
            'checkInsToday' => $checkInsToday,
            'checkOutsToday' => $checkOutsToday,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'ordersTotal' => $ordersTotal,
            'ordersToday' => $ordersToday,
            'ordersPending' => $ordersPending,
            'ordersDelivered' => $ordersDelivered,
            'restaurantReservationsTotal' => $restaurantReservationsTotal,
            'restaurantReservationsToday' => $restaurantReservationsToday,
            'restaurantReservationsPending' => $restaurantReservationsPending,
            'spaReservationsTotal' => $spaReservationsTotal,
            'spaReservationsToday' => $spaReservationsToday,
            'spaReservationsPending' => $spaReservationsPending,
            'laundryRequestsTotal' => $laundryRequestsTotal,
            'laundryRequestsPending' => $laundryRequestsPending,
            'palaceRequestsTotal' => $palaceRequestsTotal,
            'palaceRequestsPending' => $palaceRequestsPending,
            'excursionBookingsTotal' => $excursionBookingsTotal,
            'excursionBookingsPending' => $excursionBookingsPending,
            'ordersChartLabels' => $ordersChartLabels,
            'ordersChartData' => $ordersChartData,
            'revenueChartLabels' => $revenueChartLabels,
            'revenueChartData' => $revenueChartData,
            'topOrderedItems' => $topOrderedItems,
            'ordersByStatusLabels' => $ordersByStatusLabels,
            'ordersByStatusData' => $ordersByStatusData,
            'recentReservations' => $recentReservations,
            'roomsByType' => $roomsByType,
        ]);
    }
}
