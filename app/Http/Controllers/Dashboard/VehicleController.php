<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vehicle::query();

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('seats')) {
            $query->where('number_of_seats', '>=', (int) $request->seats);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vehicles = $query->ordered()->paginate(12);
        $stats = [
            'total' => Vehicle::count(),
            'available' => Vehicle::available()->count(),
        ];

        return view('pages.dashboard.vehicles.index', [
            'vehicles' => $vehicles,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_type' => 'required|in:berline,suv,minibus,van,other',
            'number_of_seats' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|max:30720',
            'display_order' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_half_day' => 'nullable|numeric|min:0',
        ]);
        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['price_per_day'] = $request->filled('price_per_day') ? (float) $request->price_per_day : null;
        $validated['price_half_day'] = $request->filled('price_half_day') ? (float) $request->price_half_day : null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        Vehicle::create($validated);

        return redirect()->route('dashboard.vehicles.index')
            ->with('success', 'Véhicule créé avec succès.');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('pages.dashboard.vehicles.show', ['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('pages.dashboard.vehicles.edit', ['vehicle' => $vehicle]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_type' => 'required|in:berline,suv,minibus,van,other',
            'number_of_seats' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|max:30720',
            'display_order' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_half_day' => 'nullable|numeric|min:0',
        ]);
        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['price_per_day'] = $request->filled('price_per_day') ? (float) $request->price_per_day : null;
        $validated['price_half_day'] = $request->filled('price_half_day') ? (float) $request->price_half_day : null;

        if ($request->hasFile('image')) {
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        return redirect()->route('dashboard.vehicles.index')
            ->with('success', 'Véhicule mis à jour.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->image) {
            Storage::disk('public')->delete($vehicle->image);
        }
        $vehicle->delete();
        return redirect()->route('dashboard.vehicles.index')
            ->with('success', 'Véhicule supprimé.');
    }

    /** Masquer / Afficher (sans supprimer) : utilise is_available. */
    public function toggleActive(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['is_available' => !$vehicle->is_available]);
        $label = $vehicle->is_available ? 'affiché' : 'masqué';
        return redirect()->route('dashboard.vehicles.index')
            ->with('success', "Véhicule {$label}.");
    }
}
