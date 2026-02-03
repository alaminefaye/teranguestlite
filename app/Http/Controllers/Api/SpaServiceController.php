<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpaService;
use App\Models\SpaReservation;

class SpaServiceController extends Controller
{
    /**
     * Liste des services spa
     */
    public function index(Request $request)
    {
        $query = SpaService::query();

        // Filtrer par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtrer par disponibilité
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'category_label' => $service->category_label,
                    'description' => $service->description,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'duration' => $service->duration,
                    'duration_text' => $service->duration_text,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'features' => $service->features,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    /**
     * Détails d'un service spa
     */
    public function show($id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service spa non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'category_label' => $service->category_label,
                'description' => $service->description,
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'duration' => $service->duration,
                'duration_text' => $service->duration_text,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'features' => $service->features,
                'is_available' => $service->is_available,
            ],
        ], 200);
    }

    /**
     * Réserver un service spa
     */
    public function reserve(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service spa non trouvé',
            ], 404);
        }

        if (!$service->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ce service n\'est pas disponible actuellement',
            ], 400);
        }

        $reservation = SpaReservation::create([
            'user_id' => $request->user()->id,
            'spa_service_id' => $id,
            'enterprise_id' => $request->user()->enterprise_id,
            'room_id' => $request->user()->room_number,
            'date' => $request->date,
            'time' => $request->time,
            'special_requests' => $request->special_requests,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);

        // Notification
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendReservationConfirmation($request->user(), $reservation);
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation spa confirmée',
            'data' => [
                'id' => $reservation->id,
                'spa_service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration' => $service->duration,
                ],
                'date' => $reservation->date,
                'time' => $reservation->time,
                'price' => $reservation->price,
                'formatted_price' => number_format($reservation->price, 0, '', ' ') . ' FCFA',
                'status' => $reservation->status,
            ],
        ], 201);
    }

    /**
     * Mes réservations spa
     */
    public function myReservations(Request $request)
    {
        $reservations = SpaReservation::with('spaService')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($res) {
                return [
                    'id' => $res->id,
                    'spa_service' => [
                        'id' => $res->spaService->id,
                        'name' => $res->spaService->name,
                        'duration' => $res->spaService->duration,
                    ],
                    'date' => $res->date,
                    'time' => $res->time,
                    'price' => $res->price,
                    'formatted_price' => number_format($res->price, 0, '', ' ') . ' FCFA',
                    'status' => $res->status,
                    'created_at' => $res->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'total' => $reservations->total(),
            ],
        ], 200);
    }
}
