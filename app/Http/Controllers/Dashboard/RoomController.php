<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Room::query();

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('room_number', 'asc')->paginate(10);

        // Statistiques
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
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'floor' => 'nullable|integer|min:0',
            'type' => 'required|in:single,double,suite,deluxe,presidential',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
        ]);

        // Ajouter enterprise_id automatiquement
        $validated['enterprise_id'] = auth()->user()->enterprise_id;

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
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number,' . $room->id,
            'floor' => 'nullable|integer|min:0',
            'type' => 'required|in:single,double,suite,deluxe,presidential',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
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
