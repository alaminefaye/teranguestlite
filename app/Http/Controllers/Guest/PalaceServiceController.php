<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\PalaceService;
use App\Models\PalaceRequest;
use App\Models\Room;
use App\Models\Vehicle;
use App\Services\GuestReservationHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PalaceServiceController extends Controller
{
    public function index(): View
    {
        $services = PalaceService::where('enterprise_id', auth()->user()->enterprise_id)
            ->available()
            ->orderBy('is_premium', 'desc')
            ->orderBy('display_order', 'asc')
            ->get()
            ->groupBy('category');

        return view('pages.guest.palace.index', [
            'title' => 'Services Palace',
            'services' => $services,
        ]);
    }

    public function show(PalaceService $palaceService): View
    {
        return view('pages.guest.palace.show', [
            'title' => $palaceService->name,
            'service' => $palaceService,
        ]);
    }

    public function request(Request $request, PalaceService $palaceService): RedirectResponse
    {
        $request->validate([
            'client_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:2000',
            'requested_for' => 'nullable|date|after_or_equal:today',
            'metadata' => 'nullable|array',
            'metadata.vehicle_request_type' => 'nullable|in:taxi,rental',
            'metadata.pickup_address' => 'nullable|string|max:500',
            'metadata.pickup_lat' => 'nullable|numeric',
            'metadata.pickup_lng' => 'nullable|numeric',
            'metadata.destination_address' => 'nullable|string|max:500',
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
            return redirect()->back()
                ->withInput()
                ->withErrors(['description' => 'Indiquez soit la description, soit le type véhicule (Taxi ou Location) avec les champs associés.']);
        }

        $user = auth()->user();
        $stay = GuestReservationHelper::requireActiveStayOrClientCode($user, $request->input('client_code'));
        if (! $stay) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['client_code' => GuestReservationHelper::MESSAGE_REQUIRE_VALID_CLIENT]);
        }

        // Véhicule : doit appartenir à l'établissement du client (chaque hôtel a ses propres données)
        if (is_array($metadata) && isset($metadata['vehicle_id'])) {
            if (!Vehicle::where('id', (int) $metadata['vehicle_id'])->where('enterprise_id', $user->enterprise_id)->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['metadata.vehicle_id' => 'Véhicule invalide ou non autorisé pour cet établissement.']);
            }
        }

        $description = $request->description ? trim($request->description) : null;
        if (empty($description) && is_array($metadata)) {
            $description = $this->buildDescriptionFromMetadata($metadata);
        }
        $description = $description ?: 'Demande sans précision';

        $requestedFor = $request->requested_for
            ? preg_replace('/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})/', '$1 $2:00', $request->requested_for)
            : null;

        $estimatedPrice = $palaceService->price_on_request ? null : $palaceService->price;
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

        PalaceRequest::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'guest_id' => $stay['guest_id'],
            'palace_service_id' => $palaceService->id,
            'room_id' => $stay['room_id'],
            'description' => $description,
            'metadata' => $metadata,
            'requested_for' => $requestedFor,
            'estimated_price' => $estimatedPrice,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.palace.my-requests')
            ->with('success', 'Votre demande a été enregistrée avec succès !');
    }

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
            if (isset($m['distance_km']) && (float) $m['distance_km'] > 0) {
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

    public function myRequests(): View
    {
        $requests = PalaceRequest::where('user_id', auth()->id())
            ->with(['palaceService'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.guest.palace.my-requests', [
            'title' => 'Mes Demandes de Services',
            'requests' => $requests,
        ]);
    }
}
