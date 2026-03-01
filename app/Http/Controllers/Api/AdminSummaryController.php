<?php

namespace App\Http\Controllers\Api;

use App\Helpers\StaffSection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\RestaurantReservation;
use App\Models\SpaReservation;
use App\Models\ExcursionBooking;
use App\Models\LaundryRequest;
use App\Models\PalaceRequest;
use App\Models\HotelConversation;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class AdminSummaryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

        $enterpriseId = $user->enterprise_id;
        $sections = $user->managed_sections;
        $isAdmin = $user->isAdmin();
        // Admin : tout. Staff avec managed_sections = null (legacy) : tout. Staff avec [] : rien. Staff avec [x,y] : seulement x,y.
        $hasAccess = fn (string $section) => $isAdmin || $sections === null || in_array($section, $sections ?? [], true);

        $zeroOrders = ['pending' => 0, 'in_progress' => 0, 'delivered' => 0, 'cancelled' => 0];
        $orders = $hasAccess(StaffSection::ROOM_SERVICE_ORDERS)
            ? [
                'pending' => Order::where('enterprise_id', $enterpriseId)->where('status', 'pending')->count(),
                'in_progress' => Order::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivering'])
                    ->count(),
                'delivered' => Order::where('enterprise_id', $enterpriseId)->where('status', 'delivered')->count(),
                'cancelled' => Order::where('enterprise_id', $enterpriseId)->where('status', 'cancelled')->count(),
            ]
            : $zeroOrders;

        $zeroRest = ['pending' => 0, 'today' => 0];
        $restaurantReservations = $hasAccess(StaffSection::RESTAURANT_RESERVATIONS)
            ? [
                'pending' => RestaurantReservation::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
                'today' => RestaurantReservation::where('enterprise_id', $enterpriseId)
                    ->whereDate('reservation_date', today())
                    ->count(),
            ]
            : $zeroRest;

        $zeroSpa = ['pending' => 0, 'rescheduled_confirmed' => 0, 'today' => 0, 'cancelled_today' => 0];
        $spaReservations = $hasAccess(StaffSection::SPA_RESERVATIONS)
            ? [
                'pending' => SpaReservation::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
                'rescheduled_confirmed' => SpaReservation::where('enterprise_id', $enterpriseId)
                    ->where('status', 'confirmed')
                    ->whereNotNull('confirmed_at')
                    ->count(),
                'today' => SpaReservation::where('enterprise_id', $enterpriseId)
                    ->whereDate('reservation_date', today())
                    ->count(),
                'cancelled_today' => SpaReservation::where('enterprise_id', $enterpriseId)
                    ->where('status', 'cancelled')
                    ->whereDate('updated_at', today())
                    ->count(),
            ]
            : $zeroSpa;

        $zeroExc = ['pending' => 0, 'today' => 0];
        $excursionBookings = $hasAccess(StaffSection::EXCURSIONS)
            ? [
                'pending' => ExcursionBooking::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
                'today' => ExcursionBooking::where('enterprise_id', $enterpriseId)
                    ->whereDate('booking_date', today())
                    ->count(),
            ]
            : $zeroExc;

        $zeroLaundry = ['pending' => 0, 'in_progress' => 0, 'delivered' => 0];
        $laundryRequests = $hasAccess(StaffSection::LAUNDRY_REQUESTS)
            ? [
                'pending' => LaundryRequest::where('enterprise_id', $enterpriseId)
                    ->where('status', 'pending')
                    ->count(),
                'in_progress' => LaundryRequest::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['picked_up', 'processing', 'ready'])
                    ->count(),
                'delivered' => LaundryRequest::where('enterprise_id', $enterpriseId)
                    ->where('status', 'delivered')
                    ->count(),
            ]
            : $zeroLaundry;

        $zeroPalace = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
        $palaceRequests = $hasAccess(StaffSection::PALACE_SERVICES)
            ? [
                'pending' => PalaceRequest::where('enterprise_id', $enterpriseId)
                    ->where('status', 'pending')
                    ->count(),
                'in_progress' => PalaceRequest::where('enterprise_id', $enterpriseId)
                    ->where('status', 'in_progress')
                    ->count(),
                'completed' => PalaceRequest::where('enterprise_id', $enterpriseId)
                    ->where('status', 'completed')
                    ->count(),
            ]
            : $zeroPalace;

        $zeroEmergency = ['open' => 0];
        $emergencyRequests = $hasAccess(StaffSection::ASSISTANCE_EMERGENCY)
            ? [
                'open' => PalaceRequest::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->where(function ($q) {
                        $q->where('metadata->type', 'doctor')
                            ->orWhere('metadata->type', 'security');
                    })
                    ->count(),
            ]
            : $zeroEmergency;

        $zeroChat = ['unread_conversations' => 0, 'open' => 0];
        $chatSummary = $hasAccess(StaffSection::CHAT_MESSAGES)
            ? [
                'unread_conversations' => HotelConversation::where('enterprise_id', $enterpriseId)
                    ->whereHas('messages', function ($query) {
                        $query->whereNull('read_at')
                            ->where('sender_type', 'guest');
                    })
                    ->count(),
                'open' => HotelConversation::where('enterprise_id', $enterpriseId)
                    ->whereIn('status', ['open', 'pending'])
                    ->count(),
            ]
            : $zeroChat;

        $zeroBilling = ['with_balance' => 0];
        $billingSummary = $hasAccess(StaffSection::BILLING_INVOICING)
            ? [
                'with_balance' => Reservation::withoutGlobalScope('enterprise')
                    ->where('reservations.enterprise_id', $enterpriseId)
                    ->whereIn('reservations.status', ['confirmed', 'checked_in', 'checked_out'])
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('orders')
                            ->whereColumn('orders.guest_id', 'reservations.guest_id')
                            ->whereColumn('orders.room_id', 'reservations.room_id')
                            ->where('orders.enterprise_id', $enterpriseId)
                            ->where('orders.payment_method', 'room_bill')
                            ->whereNull('orders.settled_at')
                            ->whereColumn('orders.created_at', '>=', 'reservations.check_in')
                            ->whereColumn('orders.created_at', '<=', 'reservations.check_out');
                    })
                    ->count(),
            ]
            : $zeroBilling;

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'restaurants' => $restaurantReservations,
                'spa' => $spaReservations,
                'excursions' => $excursionBookings,
                'laundry' => $laundryRequests,
                'palace' => $palaceRequests,
                'emergency' => $emergencyRequests,
                'chat' => $chatSummary,
                'billing' => $billingSummary,
            ],
        ], 200);
    }
}
