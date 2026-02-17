<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PalaceService;
use App\Models\PalaceRequest;
use App\Models\Room;
use App\Models\Vehicle;
use App\Services\GuestReservationHelper;
use Illuminate\Support\Facades\Log;

class PalaceServiceController extends Controller
{
    /**
     * Liste des services palace
     */
    public function index(Request $request)
    {
        $query = PalaceService::query();

        // Filtrer par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtrer par premium
        if ($request->has('premium')) {
            $query->where('is_premium', $request->boolean('premium'));
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
                    'price_on_request' => $service->price_on_request,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'is_premium' => $service->is_premium,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    /**
     * Détails d'un service palace
     */
    public function show($id)
    {
        $service = PalaceService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service palace non trouvé',
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
                'price_on_request' => $service->price_on_request,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'is_premium' => $service->is_premium,
                'is_available' => $service->is_available,
            ],
        ], 200);
    }

    /**
     * Créer une demande de service palace.
     * Pour "Location Voiture" : metadata peut contenir vehicle_request_type (taxi|rental),
     * taxi: pickup_address, destination_address, distance_km, pickup_lat, pickup_lng, ...
     * rental: number_of_seats, vehicle_type, rental_days, rental_duration_hours.
     */
    public function request(Request $request, $id)
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'requested_for' => 'nullable|date_format:Y-m-d H:i',
            'description' => 'nullable|string|max:2000',
            'special_requirements' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
            'metadata.vehicle_request_type' => 'nullable|in:taxi,rental',
            'metadata.pickup_address' => 'nullable|string|max:500',
            'metadata.pickup_lat' => 'nullable|numeric',
            'metadata.pickup_lng' => 'nullable|numeric',
            'metadata.destination_address' => 'nullable|string|max:500',
            'metadata.destination_lat' => 'nullable|numeric',
            'metadata.destination_lng' => 'nullable|numeric',
            'metadata.distance_km' => 'nullable|numeric|min:0',
            'metadata.number_of_seats' => 'nullable|integer|min:1|max:20',
            'metadata.vehicle_type' => 'nullable|string|max:100',
            'metadata.vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'metadata.rental_days' => 'nullable|integer|min:1|max:90',
            'metadata.rental_duration_hours' => 'nullable|integer|min:1|max:720',
            'metadata.tour_type' => 'nullable|string|max:100',
            'metadata.guests_count' => 'nullable|integer|min:1|max:50',
        ]);

        $metadata = $request->input('metadata');
        $hasDescription = !empty(trim($request->input('description', '')));
        $hasVehicleMeta = is_array($metadata) && !empty($metadata['vehicle_request_type']);
        $hasGuidedTourMeta = is_array($metadata) && (isset($metadata['tour_type']) || isset($metadata['guests_count']));
        if (!$hasDescription && !$hasVehicleMeta && !$hasGuidedTourMeta) {
            return response()->json([
                'success' => false,
                'message' => 'Indiquez les détails de la demande (description), le type véhicule (taxi/location) ou les infos visite guidée (type de circuit, nombre de personnes).',
            ], 422);
        }

        // Véhicule : doit appartenir à l'établissement du client (aucun mélange entre hôtels)
        if (is_array($metadata) && isset($metadata['vehicle_id'])) {
            $vehicleOk = Vehicle::where('id', (int) $metadata['vehicle_id'])
                ->where('enterprise_id', $request->user()->enterprise_id)
                ->exists();
            if (!$vehicleOk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Véhicule invalide ou non autorisé pour cet établissement.',
                ], 422);
            }
        }

        $service = PalaceService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service palace non trouvé',
            ], 404);
        }

        if (!$service->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ce service n\'est pas disponible actuellement',
            ], 400);
        }

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
        $roomId = $stay['room_id'];
        $guestId = $stay['guest_id'];
        $metadata = $request->input('metadata');

        // Prix : si demande location avec véhicule choisi, calculer selon véhicule (journée / demi-journée)
        $estimatedPrice = $service->price_on_request ? null : $service->price;
        if (is_array($metadata) && !empty($metadata['vehicle_id']) && ($metadata['vehicle_request_type'] ?? '') === 'rental') {
            $vehicle = Vehicle::where('id', (int) $metadata['vehicle_id'])
                ->where('enterprise_id', $user->enterprise_id)
                ->first();
            if ($vehicle) {
                $rentalDays = isset($metadata['rental_days']) ? (int) $metadata['rental_days'] : null;
                $rentalHours = isset($metadata['rental_duration_hours']) ? (int) $metadata['rental_duration_hours'] : null;
                $computed = $vehicle->computePriceForRental($rentalDays, $rentalHours);
                if ($computed !== null) {
                    $estimatedPrice = $computed;
                }
            }
        }

        $description = $request->description;
        if (empty(trim($description ?? '')) && is_array($metadata)) {
            $description = $this->buildDescriptionFromMetadata($metadata);
        }
        $description = $description ?: 'Demande sans précision';

        $palaceRequest = PalaceRequest::create([
            'user_id' => $user->id,
            'guest_id' => $guestId,
            'palace_service_id' => (int) $id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $roomId,
            'request_number' => $this->generateRequestNumber(),
            'requested_for' => $request->requested_for ?? now()->addHours(2)->format('Y-m-d H:i'),
            'description' => $description,
            'metadata' => $metadata,
            'estimated_price' => $estimatedPrice,
            'status' => 'pending',
        ]);

        // Notification au client de la chambre
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($palaceRequest->room_id) {
                $firebaseService->sendToClientOfRoom(
                    $palaceRequest->room_id,
                    'Demande de service palace enregistrée',
                    "Votre demande #{$palaceRequest->request_number} a été enregistrée",
                    ['type' => 'palace', 'request_id' => (string) $palaceRequest->id, 'screen' => 'PalaceRequests']
                );
            }
            // Notifier le staff
            $firebaseService->sendToStaff(
                $request->user()->enterprise_id,
                'Nouvelle demande palace',
                "Nouvelle demande de service : {$service->name}"
            );
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande de service palace enregistrée',
            'data' => [
                'id' => $palaceRequest->id,
                'request_number' => $palaceRequest->request_number,
                'palace_service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                ],
                'requested_for' => $palaceRequest->requested_for,
                'description' => $palaceRequest->description,
                'estimated_price' => $palaceRequest->estimated_price,
                'formatted_price' => $palaceRequest->estimated_price ? 
                    number_format($palaceRequest->estimated_price, 0, '', ' ') . ' FCFA' : 
                    'Prix sur demande',
                'price_on_request' => $service->price_on_request,
                'status' => $palaceRequest->status,
                'metadata' => $palaceRequest->metadata,
            ],
        ], 201);
    }

    /**
     * Mes demandes de services palace
     */
    public function myRequests(Request $request)
    {
        $user = $request->user();
        $isStaffOrAdmin = method_exists($user, 'isAdmin') && method_exists($user, 'isStaff')
            ? ($user->isAdmin() || $user->isStaff())
            : false;

        $onlyEmergency = $request->boolean('emergency');

        $query = PalaceRequest::with(['palaceService', 'room', 'guest']);

        if (! $isStaffOrAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($onlyEmergency) {
            $enterprise = $user->enterprise;
            $doctorServiceId = null;
            $securityServiceId = null;

            if ($enterprise) {
                $emergency = $enterprise->emergency;
                $doctorServiceId = $emergency['doctor_service_id'] ?? null;
                $securityServiceId = $emergency['security_service_id'] ?? null;
            }

            $query->where(function ($q) use ($doctorServiceId, $securityServiceId) {
                if ($doctorServiceId) {
                    $q->orWhere('palace_service_id', $doctorServiceId);
                }
                if ($securityServiceId) {
                    $q->orWhere('palace_service_id', $securityServiceId);
                }
                $q->orWhere(function ($qq) {
                    $qq->where('metadata->type', 'doctor')
                        ->orWhere('metadata->type', 'security');
                });
            });
        }

        $requests = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($req) {
                $metadata = is_array($req->metadata) ? $req->metadata : [];
                $emergencyType = null;
                if (isset($metadata['type']) && in_array($metadata['type'], ['doctor', 'security'], true)) {
                    $emergencyType = $metadata['type'];
                }

                return [
                    'id' => $req->id,
                    'request_number' => $req->request_number,
                    'palace_service' => [
                        'id' => $req->palaceService->id,
                        'name' => $req->palaceService->name,
                        'category' => $req->palaceService->category,
                    ],
                    'requested_for' => $req->requested_for,
                    'description' => $req->description,
                    'estimated_price' => $req->estimated_price,
                    'formatted_price' => $req->estimated_price ?
                        number_format($req->estimated_price, 0, '', ' ') . ' FCFA' :
                        'Prix sur demande',
                    'status' => $req->status,
                    'metadata' => $req->metadata,
                    'room_number' => $req->room ? $req->room->room_number : null,
                    'guest_name' => $req->guest ? $req->guest->name : null,
                    'created_at' => $req->created_at->toISOString(),
                    'emergency_type' => $emergencyType,
                ];
            }),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'total' => $requests->total(),
            ],
        ], 200);
    }

    /**
     * Mise à jour du statut d'une demande palace (staff/admin uniquement).
     */
    public function updateRequestStatus(Request $request, $id)
    {
        $user = $request->user();

        if (! method_exists($user, 'isAdmin') || ! method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (! ($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:accept,complete,cancel'],
        ]);

        $palaceRequest = PalaceRequest::with(['room', 'guest', 'palaceService'])->find($id);

        if (! $palaceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée',
            ], 404);
        }

        if ($palaceRequest->enterprise_id !== $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Demande introuvable pour cet établissement',
            ], 404);
        }

        $action = $validated['action'];
        $currentStatus = $palaceRequest->status;

        if ($action === 'accept') {
            if ($currentStatus !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les demandes en attente peuvent être acceptées',
                ], 400);
            }
            $palaceRequest->status = 'in_progress';
            $palaceRequest->confirmed_at = now();
        } elseif ($action === 'complete') {
            if (! in_array($currentStatus, ['pending', 'in_progress'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les demandes en cours ou en attente peuvent être terminées',
                ], 400);
            }
            $palaceRequest->status = 'completed';
        } elseif ($action === 'cancel') {
            if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d’annuler une demande déjà terminée ou annulée',
                ], 400);
            }
            $palaceRequest->status = 'cancelled';
            $palaceRequest->cancelled_at = now();
        }

        $palaceRequest->save();

        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            if ($palaceRequest->room_id) {
                $statusLabel = $palaceRequest->status;
                $firebaseService->sendToClientOfRoom(
                    $palaceRequest->room_id,
                    'Statut de demande palace mis à jour',
                    "Votre demande #{$palaceRequest->request_number} est maintenant {$statusLabel}",
                    [
                        'type' => 'palace_status',
                        'request_id' => (string) $palaceRequest->id,
                        'status' => $palaceRequest->status,
                        'screen' => 'PalaceRequests',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification error (palace status): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $palaceRequest->id,
                'request_number' => $palaceRequest->request_number,
                'palace_service' => [
                    'id' => $palaceRequest->palaceService->id,
                    'name' => $palaceRequest->palaceService->name,
                    'category' => $palaceRequest->palaceService->category,
                ],
                'requested_for' => $palaceRequest->requested_for,
                'description' => $palaceRequest->description,
                'estimated_price' => $palaceRequest->estimated_price,
                'formatted_price' => $palaceRequest->formatted_estimated_price,
                'status' => $palaceRequest->status,
                'metadata' => $palaceRequest->metadata,
                'room_number' => $palaceRequest->room ? $palaceRequest->room->room_number : null,
                'guest_name' => $palaceRequest->guest ? $palaceRequest->guest->name : null,
            ],
        ], 200);
    }

    /**
     * Construire un libellé lisible à partir des metadata (taxi ou location).
     */
    private function buildDescriptionFromMetadata(array $m): string
    {
        $type = $m['vehicle_request_type'] ?? null;
        if ($type === 'taxi') {
            $parts = ['Taxi'];
            if (!empty($m['pickup_address'])) {
                $parts[] = 'Prise en charge : ' . $m['pickup_address'];
            }
            if (!empty($m['destination_address'])) {
                $parts[] = 'Destination : ' . $m['destination_address'];
            }
            if (isset($m['distance_km']) && $m['distance_km'] > 0) {
                $parts[] = 'Distance : ' . round((float) $m['distance_km'], 1) . ' km';
            }
            return implode(' | ', $parts);
        }
        if ($type === 'rental') {
            $parts = ['Location véhicule'];
            if (!empty($m['number_of_seats'])) {
                $parts[] = $m['number_of_seats'] . ' place(s)';
            }
            if (!empty($m['vehicle_type'])) {
                $parts[] = $m['vehicle_type'];
            }
            if (!empty($m['rental_days'])) {
                $parts[] = $m['rental_days'] . ' jour(s)';
            }
            if (!empty($m['rental_duration_hours'])) {
                $parts[] = $m['rental_duration_hours'] . ' h';
            }
            return implode(' | ', $parts);
        }
        if (isset($m['tour_type']) || isset($m['guests_count'])) {
            $parts = ['Visite guidée'];
            $labels = ['cultural' => 'Culturel', 'gastronomic' => 'Gastronomique', 'historical' => 'Historique'];
            if (!empty($m['tour_type'])) {
                $parts[] = $labels[$m['tour_type']] ?? $m['tour_type'];
            }
            if (!empty($m['guests_count'])) {
                $parts[] = (int) $m['guests_count'] . ' personne(s)';
            }
            return implode(' | ', $parts);
        }
        return 'Demande sans précision';
    }

    /**
     * Générer un numéro de demande unique
     */
    private function generateRequestNumber()
    {
        $date = now()->format('Ymd');
        $lastRequest = PalaceRequest::whereDate('created_at', today())->latest()->first();
        $sequence = $lastRequest ? (intval(substr($lastRequest->request_number, -3)) + 1) : 1;
        
        return 'PAL-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
