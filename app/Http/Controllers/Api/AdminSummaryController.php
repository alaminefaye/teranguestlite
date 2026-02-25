<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\RestaurantReservation;
use App\Models\SpaReservation;
use App\Models\ExcursionBooking;
use App\Models\LaundryRequest;
use App\Models\PalaceRequest;
use App\Models\HotelConversation;

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

        $orders = [
            'pending' => Order::where('enterprise_id', $enterpriseId)->where('status', 'pending')->count(),
            'in_progress' => Order::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivering'])
                ->count(),
            'delivered' => Order::where('enterprise_id', $enterpriseId)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('enterprise_id', $enterpriseId)->where('status', 'cancelled')->count(),
        ];

        $restaurantReservations = [
            'pending' => RestaurantReservation::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'today' => RestaurantReservation::where('enterprise_id', $enterpriseId)
                ->whereDate('reservation_date', today())
                ->count(),
        ];

        $spaReservations = [
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
        ];

        $excursionBookings = [
            'pending' => ExcursionBooking::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'today' => ExcursionBooking::where('enterprise_id', $enterpriseId)
                ->whereDate('booking_date', today())
                ->count(),
        ];

        $laundryRequests = [
            'pending' => LaundryRequest::where('enterprise_id', $enterpriseId)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => LaundryRequest::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['picked_up', 'processing', 'ready'])
                ->count(),
            'delivered' => LaundryRequest::where('enterprise_id', $enterpriseId)
                ->where('status', 'delivered')
                ->count(),
        ];

        $palaceRequests = [
            'pending' => PalaceRequest::where('enterprise_id', $enterpriseId)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => PalaceRequest::where('enterprise_id', $enterpriseId)
                ->where('status', 'in_progress')
                ->count(),
            'completed' => PalaceRequest::where('enterprise_id', $enterpriseId)
                ->where('status', 'completed')
                ->count(),
        ];

        $emergencyRequests = [
            'open' => PalaceRequest::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->where(function ($q) {
                    $q->where('metadata->type', 'doctor')
                        ->orWhere('metadata->type', 'security');
                })
                ->count(),
        ];

        $chatSummary = [
            'unread_conversations' => HotelConversation::where('enterprise_id', $enterpriseId)
                ->whereHas('messages', function ($query) {
                    $query->whereNull('read_at')
                        ->where('sender_type', 'guest');
                })
                ->count(),
            'open' => HotelConversation::where('enterprise_id', $enterpriseId)
                ->whereIn('status', ['open', 'pending'])
                ->count(),
        ];

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
            ],
        ], 200);
    }
}
