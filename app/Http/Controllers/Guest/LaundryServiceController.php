<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\LaundryService;
use App\Models\LaundryRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LaundryServiceController extends Controller
{
    public function index(): View
    {
        $services = LaundryService::where('enterprise_id', auth()->user()->enterprise_id)
            ->available()
            ->orderBy('display_order', 'asc')
            ->get()
            ->groupBy('category');

        return view('pages.guest.laundry.index', [
            'title' => 'Blanchisserie',
            'services' => $services,
        ]);
    }

    public function request(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.laundry_service_id' => 'required|exists:laundry_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $room = Room::where('enterprise_id', $user->enterprise_id)
            ->where('room_number', $user->room_number)
            ->first();

        $itemsData = [];
        $totalPrice = 0;

        foreach ($validated['items'] as $item) {
            $service = LaundryService::find($item['laundry_service_id']);
            $quantity = $item['quantity'];
            $price = $service->price * $quantity;
            $totalPrice += $price;

            $itemsData[] = [
                'laundry_service_id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'quantity' => $quantity,
                'unit_price' => $service->price,
                'total_price' => $price,
                'turnaround_hours' => $service->turnaround_hours,
            ];
        }

        LaundryRequest::create([
            'enterprise_id' => $user->enterprise_id,
            'user_id' => $user->id,
            'room_id' => $room->id ?? null,
            'items' => $itemsData,
            'total_price' => $totalPrice,
            'special_instructions' => $validated['special_instructions'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('guest.laundry.my-requests')
            ->with('success', 'Votre demande de blanchisserie a été enregistrée avec succès !');
    }

    public function myRequests(): View
    {
        $requests = LaundryRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.guest.laundry.my-requests', [
            'title' => 'Mes Demandes de Blanchisserie',
            'requests' => $requests,
        ]);
    }
}
