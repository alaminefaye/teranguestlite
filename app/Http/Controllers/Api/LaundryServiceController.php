<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaundryService;
use App\Models\LaundryRequest;
use App\Models\Room;
use App\Services\GuestReservationHelper;

class LaundryServiceController extends Controller
{
    /**
     * Liste des services de blanchisserie
     */
    public function index(Request $request)
    {
        $query = LaundryService::query();

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
                    'turnaround_hours' => $service->turnaround_hours,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    /**
     * Créer une demande de blanchisserie
     */
    public function request(Request $request)
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.laundry_service_id' => 'required|exists:laundry_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'pickup_time' => 'nullable|date_format:Y-m-d H:i',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        // Calculer le total et préparer les items
        $total = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $service = LaundryService::find($item['laundry_service_id']);
            
            if (!$service || !$service->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => "Le service {$service->name} n'est pas disponible",
                ], 400);
            }

            $subtotal = $service->price * $item['quantity'];
            $total += $subtotal;

            $itemsData[] = [
                'laundry_service_id' => $service->id,
                'service_name' => $service->name,
                'quantity' => $item['quantity'],
                'unit_price' => $service->price,
                'subtotal' => $subtotal,
            ];
        }

        // Calculer le temps de livraison (prendre le max des turnaround_hours)
        $maxTurnaround = collect($request->items)->map(function($item) {
            return LaundryService::find($item['laundry_service_id'])->turnaround_hours;
        })->max();

        $pickupTime = $request->pickup_time ? $request->pickup_time : now()->addHours(1)->format('Y-m-d H:i');
        $deliveryTime = \Carbon\Carbon::parse($pickupTime)->addHours($maxTurnaround)->format('Y-m-d H:i');

        $user = $request->user();
        $stay = GuestReservationHelper::requireActiveStayOrClientCode($user, $request->input('client_code'));
        if (! $stay) {
            return response()->json([
                'success' => false,
                'message' => GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT,
                'error_code' => GuestReservationHelper::ERROR_CODE_INVALID_CLIENT,
            ], 403);
        }

        // Créer la demande
        $laundryRequest = LaundryRequest::create([
            'user_id' => $user->id,
            'guest_id' => $stay['guest_id'],
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $stay['room_id'],
            'request_number' => $this->generateRequestNumber(),
            'items' => $itemsData,
            'total_price' => $total,
            'pickup_time' => $pickupTime,
            'delivery_time' => $deliveryTime,
            'special_instructions' => $request->special_instructions,
            'status' => 'pending',
        ]);

        // Notification
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendToUser(
                $request->user(),
                'Demande de blanchisserie enregistrée',
                "Votre demande #{$laundryRequest->request_number} a été enregistrée"
            );
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande de blanchisserie enregistrée',
            'data' => [
                'id' => $laundryRequest->id,
                'request_number' => $laundryRequest->request_number,
                'total_price' => $laundryRequest->total_price,
                'formatted_total' => number_format($laundryRequest->total_price, 0, '', ' ') . ' FCFA',
                'items' => collect($itemsData)->map(function($item) {
                    return [
                        'service' => [
                            'id' => $item['laundry_service_id'],
                            'name' => $item['service_name'],
                        ],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ];
                }),
                'pickup_time' => $laundryRequest->pickup_time,
                'estimated_delivery' => $laundryRequest->delivery_time,
                'status' => $laundryRequest->status,
            ],
        ], 201);
    }

    /**
     * Mes demandes de blanchisserie
     */
    public function myRequests(Request $request)
    {
        $requests = LaundryRequest::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'request_number' => $req->request_number,
                    'total_price' => $req->total_price,
                    'formatted_total' => number_format($req->total_price, 0, '', ' ') . ' FCFA',
                    'items_count' => count($req->items),
                    'pickup_time' => $req->pickup_time,
                    'delivery_time' => $req->delivery_time,
                    'status' => $req->status,
                    'created_at' => $req->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'total' => $requests->total(),
            ],
        ], 200);
    }

    /**
     * Générer un numéro de demande unique
     */
    private function generateRequestNumber()
    {
        $date = now()->format('Ymd');
        $lastRequest = LaundryRequest::whereDate('created_at', today())->latest()->first();
        $sequence = $lastRequest ? (intval(substr($lastRequest->request_number, -3)) + 1) : 1;
        
        return 'LAU-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
