<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PalaceService;
use App\Models\PalaceRequest;
use App\Models\Room;
use App\Models\Vehicle;
use App\Services\GuestReservationHelper;

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
        ]);

        $metadata = $request->input('metadata');
        $hasDescription = !empty(trim($request->input('description', '')));
        $hasVehicleMeta = is_array($metadata) && !empty($metadata['vehicle_request_type']);
        if (!$hasDescription && !$hasVehicleMeta) {
            return response()->json([
                'success' => false,
                'message' => 'Indiquez soit les détails de la demande (description), soit le type véhicule (taxi ou location) avec les champs associés.',
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
            return response()->json([
                'success' => false,
                'message' => GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT,
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

        // Notification
        try {
            $firebaseService = app(\App\Services\FirebaseNotificationService::class);
            $firebaseService->sendToUser(
                $request->user(),
                'Demande de service palace enregistrée',
                "Votre demande #{$palaceRequest->request_number} a été enregistrée"
            );

            // Notifier le staff
            $firebaseService->sendToStaff(
                $request->user()->enterprise_id,
                'Nouvelle demande palace',
                "Nouvelle demande de service : {$service->name}"
            );
        } catch (\Exception $e) {
            \Log::error('Firebase notification error: ' . $e->getMessage());
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
        $requests = PalaceRequest::with('palaceService')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($req) {
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
