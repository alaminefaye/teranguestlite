<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Restaurant::query();

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $restaurants = $query->ordered()->paginate(10);

        // Statistiques
        $stats = [
            'total' => Restaurant::count(),
            'open' => Restaurant::open()->count(),
            'closed' => Restaurant::closed()->count(),
            'restaurants' => Restaurant::byType('restaurant')->count(),
            'bars' => Restaurant::byType('bar')->count(),
        ];

        return view('pages.dashboard.restaurants.index', [
            'title' => 'Restaurants & Bars',
            'restaurants' => $restaurants,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.restaurants.create', [
            'title' => 'Créer un restaurant/bar',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:restaurant,bar,cafe,pool_bar',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:open,closed,coming_soon',
            'opening_hours' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'has_terrace' => 'nullable|boolean',
            'has_wifi' => 'nullable|boolean',
            'has_live_music' => 'nullable|boolean',
            'accepts_reservations' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;

        // Upload image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('restaurants', 'public');
        }

        // Traiter les horaires d'ouverture
        if ($request->has('opening_hours')) {
            $openingHours = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            foreach ($days as $day) {
                if ($request->input("opening_hours.{$day}.enabled")) {
                    $openingHours[$day] = [
                        'open' => $request->input("opening_hours.{$day}.open"),
                        'close' => $request->input("opening_hours.{$day}.close"),
                    ];
                }
            }
            
            $validated['opening_hours'] = $openingHours;
        }

        // Convertir les checkboxes
        $validated['has_terrace'] = $request->has('has_terrace');
        $validated['has_wifi'] = $request->has('has_wifi');
        $validated['has_live_music'] = $request->has('has_live_music');
        $validated['accepts_reservations'] = $request->has('accepts_reservations');

        Restaurant::create($validated);

        return redirect()->route('dashboard.restaurants.index')
            ->with('success', 'Restaurant créé avec succès !');
    }

    public function show(Restaurant $restaurant): View
    {
        return view('pages.dashboard.restaurants.show', [
            'title' => $restaurant->name,
            'restaurant' => $restaurant,
        ]);
    }

    public function edit(Restaurant $restaurant): View
    {
        return view('pages.dashboard.restaurants.edit', [
            'title' => 'Modifier ' . $restaurant->name,
            'restaurant' => $restaurant,
        ]);
    }

    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:restaurant,bar,cafe,pool_bar',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:open,closed,coming_soon',
            'opening_hours' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'has_terrace' => 'nullable|boolean',
            'has_wifi' => 'nullable|boolean',
            'has_live_music' => 'nullable|boolean',
            'accepts_reservations' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        // Upload image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($restaurant->image) {
                Storage::disk('public')->delete($restaurant->image);
            }
            $validated['image'] = $request->file('image')->store('restaurants', 'public');
        }

        // Traiter les horaires d'ouverture
        if ($request->has('opening_hours')) {
            $openingHours = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            foreach ($days as $day) {
                if ($request->input("opening_hours.{$day}.enabled")) {
                    $openingHours[$day] = [
                        'open' => $request->input("opening_hours.{$day}.open"),
                        'close' => $request->input("opening_hours.{$day}.close"),
                    ];
                }
            }
            
            $validated['opening_hours'] = $openingHours;
        }

        // Convertir les checkboxes
        $validated['has_terrace'] = $request->has('has_terrace');
        $validated['has_wifi'] = $request->has('has_wifi');
        $validated['has_live_music'] = $request->has('has_live_music');
        $validated['accepts_reservations'] = $request->has('accepts_reservations');

        $restaurant->update($validated);

        return redirect()->route('dashboard.restaurants.show', $restaurant)
            ->with('success', 'Restaurant mis à jour avec succès !');
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        // Supprimer l'image
        if ($restaurant->image) {
            Storage::disk('public')->delete($restaurant->image);
        }

        $restaurant->delete();

        return redirect()->route('dashboard.restaurants.index')
            ->with('success', 'Restaurant supprimé avec succès !');
    }
}
