<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaundryService;
use App\Models\LaundryRequest;
use App\Models\Room;
use App\Services\GuestReservationHelper;
use Illuminate\Support\Facades\Log;

class LaundryServiceController extends Controller
{
    /**
     * Liste des services de blanchisserie
     */
    public function index(Request $request)
    {
        $query = LaundryService::query()->active();

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
            $service = LaundryService::active()->find($item['laundry_service_id']);
            
            if (!$service || !$service->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => "Un des services n'est pas disponible ou a été masqué.",
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
            return LaundryService::active()->find($item['laundry_service_id'])->turnaround_hours;
        })->max();

        $pickupTime = $request->pickup_time ? $request->pickup_time : now()->addHours(1)->format('Y-m-d H:i');
        $deliveryTime = \Carbon\Carbon::parse($pickupTime)->addHours($maxTurnaround)->format('Y-m-d H:i');

        $user = $request->user();
        $stay = GuestReservationHelper::requireValidCodeOrActiveStay($user, $request->input('client_code'));
        if (! $stay) {
            $message = $request->filled('client_code') && trim((string) $request->input('client_code')) !== ''
                ? GuestReservationHelper::MESSAGE_CLIENT_CODE_INVALID_OR_EXPIRED
                : GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT;
            return response()->json([
                'success' => false,
                'message' => $message,
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

        // Notifications
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendToStaffForSection(
                $user->enterprise_id,
                \App\Helpers\StaffSection::LAUNDRY_REQUESTS,
                'Nouvelle demande blanchisserie',
                "Nouvelle demande #{$laundryRequest->request_number}",
                ['type' => 'laundry_status', 'request_id' => (string) $laundryRequest->id, 'screen' => 'LaundryRequests']
            );
            if ($laundryRequest->room_id) {
                $firebaseService->sendToClientOfRoom(
                    $laundryRequest->room_id,
                    'Demande de blanchisserie enregistrée',
                    "Votre demande #{$laundryRequest->request_number} a été enregistrée",
                    ['type' => 'laundry', 'request_id' => (string) $laundryRequest->id, 'screen' => 'LaundryRequests']
                );
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
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
        $user = $request->user();
        $isStaffOrAdmin = method_exists($user, 'isAdmin') && method_exists($user, 'isStaff')
            ? ($user->isAdmin() || $user->isStaff())
            : false;

        $query = LaundryRequest::with(['room', 'guest']);

        if (! $isStaffOrAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $perPage = (int) $request->input('per_page', 15);

        $requests = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($req) {
                $items = is_array($req->items) ? $req->items : [];

                return [
                    'id' => $req->id,
                    'request_number' => $req->request_number,
                    'total_price' => $req->total_price,
                    'formatted_total' => number_format($req->total_price, 0, '', ' ') . ' FCFA',
                    'items' => collect($items)->map(function ($item) {
                        $serviceId = $item['laundry_service_id'] ?? null;
                        $serviceName = $item['service_name'] ?? null;
                        $unitPrice = $item['unit_price'] ?? ($item['price'] ?? 0);
                        $quantity = $item['quantity'] ?? 0;
                        $subtotal = $item['subtotal'] ?? ($unitPrice * $quantity);

                        return [
                            'service' => [
                                'id' => $serviceId,
                                'name' => $serviceName,
                            ],
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                        ];
                    }),
                    'items_count' => count($items),
                    'pickup_time' => $req->pickup_time,
                    'delivery_time' => $req->delivery_time,
                    'status' => $req->status,
                    'room_number' => $req->room ? $req->room->room_number : null,
                    'guest_name' => $req->guest ? $req->guest->name : null,
                    'created_at' => $req->created_at->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'from' => $requests->firstItem(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'to' => $requests->lastItem(),
                'total' => $requests->total(),
            ],
        ], 200);
    }

    public function updateRequestStatus(Request $request, $id)
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

        $validated = $request->validate([
            'action' => 'required|string|in:pickup,ready,deliver,cancel',
            'reason' => 'required_if:action,cancel|string|max:255',
        ]);

        $laundryRequest = LaundryRequest::with(['room', 'guest'])->find($id);

        if (! $laundryRequest || $laundryRequest->enterprise_id != $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée',
            ], 404);
        }

        $action = $validated['action'];
        $validActions = ['pickup', 'ready', 'deliver', 'cancel'];

        if (!in_array($action, $validActions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action invalide',
            ], 400);
        }

        $statusTransitions = [
            'pickup' => ['pending' => 'picked_up'],
            'ready' => ['picked_up' => 'ready'],
            'deliver' => ['ready' => 'delivered'],
            'cancel' => [
                'pending' => 'cancelled',
                'picked_up' => 'cancelled',
                'ready' => 'cancelled',
            ],
        ];

        if (!isset($statusTransitions[$action][$laundryRequest->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Transition de statut non autorisée',
            ], 400);
        }

        $laundryRequest->status = $statusTransitions[$action][$laundryRequest->status];
        $laundryRequest->save();

        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($laundryRequest->room_id) {
                $statusMessages = [
                    'picked_up' => 'Votre linge a été pris en charge',
                    'ready' => 'Votre linge est prêt',
                    'delivered' => 'Votre linge a été livré',
                    'cancelled' => 'Votre demande de blanchisserie a été annulée',
                ];

                $title = "Blanchisserie #{$laundryRequest->request_number}";
                $body = $statusMessages[$laundryRequest->status] ?? 'Statut de blanchisserie mis à jour';

                $firebaseService->sendToClientOfRoom(
                    $laundryRequest->room_id,
                    $title,
                    $body,
                    [
                        'type' => 'laundry_status',
                        'request_id' => (string) $laundryRequest->id,
                        'status' => $laundryRequest->status,
                        'screen' => 'LaundryRequests',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification error (laundry status): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $laundryRequest->id,
                'request_number' => $laundryRequest->request_number,
                'total_price' => $laundryRequest->total_price,
                'formatted_total' => number_format($laundryRequest->total_price, 0, '', ' ') . ' FCFA',
                'items_count' => count($laundryRequest->items),
                'pickup_time' => $laundryRequest->pickup_time,
                'delivery_time' => $laundryRequest->delivery_time,
                'status' => $laundryRequest->status,
                'room_number' => $laundryRequest->room ? $laundryRequest->room->room_number : null,
                'guest_name' => $laundryRequest->guest ? $laundryRequest->guest->name : null,
                'created_at' => $laundryRequest->created_at->toISOString(),
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
