<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\PalaceService;
use App\Models\PalaceRequest;
use App\Models\Room;
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
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'requested_for' => 'nullable|date|after_or_equal:today',
        ]);

        $user = auth()->user();
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        PalaceRequest::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'palace_service_id' => $palaceService->id,
            'room_id' => $room->id ?? null,
            'description' => $validated['description'],
            'requested_for' => $validated['requested_for'] ?? null,
            'estimated_price' => $palaceService->price_on_request ? null : $palaceService->price,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.palace.my-requests')
            ->with('success', 'Votre demande a été enregistrée avec succès !');
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
