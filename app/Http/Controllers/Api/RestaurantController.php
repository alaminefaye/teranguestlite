<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\RestaurantReservation;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('open_now')) {
            $query->open();
        }

        $restaurants = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants->map(function ($restaurant) {
                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'type' => $restaurant->type,
                    'type_label' => $restaurant->type_label,
                    'description' => $restaurant->description,
                    'cuisine_type' => $restaurant->cuisine_type,
                    'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                    'capacity' => $restaurant->capacity,
                    'opening_hours' => $restaurant->opening_hours,
                    'is_open_now' => $restaurant->is_open_now,
                    'today_hours' => $restaurant->today_hours,
                ];
            }),
        ], 200);
    }

    public function show($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'type' => $restaurant->type,
                'type_label' => $restaurant->type_label,
                'description' => $restaurant->description,
                'cuisine_type' => $restaurant->cuisine_type,
                'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                'capacity' => $restaurant->capacity,
                'opening_hours' => $restaurant->opening_hours,
                'is_open_now' => $restaurant->is_open_now,
                'today_hours' => $restaurant->today_hours,
            ],
        ], 200);
    }

    public function reserve(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'guests' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
        }

        $reservation = RestaurantReservation::create([
            'user_id' => $request->user()->id,
            'restaurant_id' => $id,
            'enterprise_id' => $request->user()->enterprise_id,
            'room_id' => $request->user()->room_number,
            'date' => $request->date,
            'time' => $request->time,
            'guests' => $request->guests,
            'special_requests' => $request->special_requests,
            'status' => 'confirmed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée',
            'data' => [
                'id' => $reservation->id,
                'restaurant' => ['id' => $restaurant->id, 'name' => $restaurant->name],
                'date' => $reservation->date,
                'time' => $reservation->time,
                'guests' => $reservation->guests,
                'status' => $reservation->status,
            ],
        ], 201);
    }

    public function myReservations(Request $request)
    {
        $reservations = RestaurantReservation::with('restaurant')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($res) {
                return [
                    'id' => $res->id,
                    'restaurant' => ['id' => $res->restaurant->id, 'name' => $res->restaurant->name],
                    'date' => $res->date,
                    'time' => $res->time,
                    'guests' => $res->guests,
                    'status' => $res->status,
                    'created_at' => $res->created_at->toISOString(),
                ];
            }),
            'meta' => ['current_page' => $reservations->currentPage(), 'total' => $reservations->total()],
        ], 200);
    }
}
