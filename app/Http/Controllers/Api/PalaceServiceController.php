<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PalaceService;
use App\Models\PalaceRequest;
use App\Models\Room;

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
     * Créer une demande de service palace
     */
    public function request(Request $request, $id)
    {
        $request->validate([
            'requested_for' => 'nullable|date_format:Y-m-d H:i',
            'description' => 'required|string|max:1000',
            'special_requirements' => 'nullable|string|max:500',
        ]);

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

        // Déterminer le prix
        $estimatedPrice = $service->price_on_request ? null : $service->price;

        // room_id = id de la chambre dans la table rooms (pas le numéro 101, 102…)
        $user = $request->user();
        $roomId = null;
        if ($user->room_number) {
            $room = Room::where('room_number', $user->room_number)->first();
            $roomId = $room?->id;
        }

        $palaceRequest = PalaceRequest::create([
            'user_id' => $user->id,
            'palace_service_id' => (int) $id,
            'enterprise_id' => $user->enterprise_id,
            'room_id' => $roomId,
            'request_number' => $this->generateRequestNumber(),
            'requested_for' => $request->requested_for ?? now()->addHours(2)->format('Y-m-d H:i'),
            'description' => $request->description,
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
        $lastRequest = PalaceRequest::whereDate('created_at', today())->latest()->first();
        $sequence = $lastRequest ? (intval(substr($lastRequest->request_number, -3)) + 1) : 1;
        
        return 'PAL-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
