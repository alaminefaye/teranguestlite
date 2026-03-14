<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Room::query();

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $query->where('room_number', 'like', '%' . $request->search . '%');
            }

            $sort = $request->get('sort', 'room_number_asc');
            if ($sort === 'room_number_desc') {
                $query->orderBy('room_number', 'desc');
            } elseif ($sort === 'type_asc') {
                $query->orderBy('type', 'asc')->orderBy('room_number', 'asc');
            } elseif ($sort === 'status_asc') {
                $query->orderBy('status', 'asc')->orderBy('room_number', 'asc');
            } else {
                $query->orderBy('room_number', 'asc');
            }

            $rooms = $query->with('tabletAccessUser')->paginate(10);

            $stats = [
                'total' => Room::count(),
                'available' => Room::available()->count(),
                'occupied' => Room::occupied()->count(),
                'maintenance' => Room::maintenance()->count(),
            ];

            return view('pages.dashboard.rooms.index', [
                'title' => 'Chambres',
                'rooms' => $rooms,
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('RoomController::index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->route('dashboard.index')
                ->with('error', 'Impossible de charger les chambres. Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.dashboard.rooms.create', [
            'title' => 'Créer une chambre',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $validated = $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rooms', 'room_number')->where('enterprise_id', $enterpriseId),
            ],
            'floor' => 'nullable|integer|min:0',
            'type' => 'required|in:single,double,suite,deluxe,presidential',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:30720',
            'wifi_network' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ]);

        // Ajouter enterprise_id automatiquement
        $validated['enterprise_id'] = $enterpriseId;

        // Upload image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        Room::create($validated);

        return redirect()->route('dashboard.rooms.index')
            ->with('success', 'Chambre créée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $room->load(['reservations' => function ($query) {
            $query->orderBy('check_in', 'desc')->take(10);
        }]);

        // Statistiques de la chambre
        $stats = [
            'total_reservations' => $room->reservations()->count(),
            'upcoming_reservations' => $room->reservations()
                ->where('status', '!=', 'cancelled')
                ->where('check_in', '>=', now())
                ->count(),
            'completed_reservations' => $room->reservations()
                ->where('status', 'checked_out')
                ->count(),
        ];

        return view('pages.dashboard.rooms.show', [
            'title' => 'Chambre ' . $room->room_number,
            'room' => $room,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        return view('pages.dashboard.rooms.edit', [
            'title' => 'Modifier chambre ' . $room->room_number,
            'room' => $room,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $validated = $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rooms', 'room_number')->where('enterprise_id', $enterpriseId)->ignore($room->id),
            ],
            'floor' => 'nullable|integer|min:0',
            'type' => 'required|in:single,double,suite,deluxe,presidential',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:30720',
            'wifi_network' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ]);

        // Upload nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($validated);

        return redirect()->route('dashboard.rooms.index')
            ->with('success', 'Chambre mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        // Vérifier qu'il n'y a pas de réservations actives ou à venir
        $activeReservations = $room->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_out', '>=', now())
            ->count();

        if ($activeReservations > 0) {
            return redirect()->route('dashboard.rooms.index')
                ->with('error', 'Impossible de supprimer cette chambre : des réservations actives ou à venir existent.');
        }

        // Supprimer l'image
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()->route('dashboard.rooms.index')
            ->with('success', 'Chambre supprimée avec succès !');
    }
}
