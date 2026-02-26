<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PalaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PalaceServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = PalaceService::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->ordered()->paginate(12);

        $stats = [
            'total' => PalaceService::count(),
            'available' => PalaceService::available()->count(),
            'premium' => PalaceService::premium()->count(),
            'concierge' => PalaceService::where('category', 'concierge')->count(),
        ];

        return view('pages.dashboard.palace-services.index', [
            'title' => 'Services Palace',
            'services' => $services,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $preset = $request->query('preset');
        $defaults = [];
        if ($preset === 'guided_tours') {
            $defaults = [
                'name' => 'Visites guidées personnalisées',
                'category' => 'concierge',
                'description' => 'Réservation de guides certifiés pour circuits culturels, gastronomiques ou historiques.',
                'status' => 'available',
                'price_on_request' => true,
                'display_order' => 0,
            ];
        }
        return view('pages.dashboard.palace-services.create', [
            'title' => 'Créer un service palace',
            'defaults' => $defaults,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:concierge,transport,vip,butler',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price' => 'nullable|numeric|min:0',
            'price_on_request' => 'nullable|boolean',
            'status' => 'required|in:available,unavailable',
            'is_premium' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['price_on_request'] = $request->has('price_on_request');
        $validated['is_premium'] = $request->has('is_premium');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('palace-services', 'public');
        }

        PalaceService::create($validated);

        return redirect()->route('dashboard.palace-services.index')
            ->with('success', 'Service palace créé avec succès !');
    }

    public function show(PalaceService $palaceService): View
    {
        return view('pages.dashboard.palace-services.show', [
            'title' => $palaceService->name,
            'service' => $palaceService,
        ]);
    }

    public function edit(PalaceService $palaceService): View
    {
        return view('pages.dashboard.palace-services.edit', [
            'title' => 'Modifier ' . $palaceService->name,
            'service' => $palaceService,
        ]);
    }

    public function update(Request $request, PalaceService $palaceService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:concierge,transport,vip,butler',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price' => 'nullable|numeric|min:0',
            'price_on_request' => 'nullable|boolean',
            'status' => 'required|in:available,unavailable',
            'is_premium' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['price_on_request'] = $request->has('price_on_request');
        $validated['is_premium'] = $request->has('is_premium');

        if ($request->hasFile('image')) {
            if ($palaceService->image) {
                Storage::disk('public')->delete($palaceService->image);
            }
            $validated['image'] = $request->file('image')->store('palace-services', 'public');
        }

        $palaceService->update($validated);

        return redirect()->route('dashboard.palace-services.index')
            ->with('success', 'Service palace mis à jour avec succès !');
    }

    public function destroy(PalaceService $palaceService): RedirectResponse
    {
        if ($palaceService->image) {
            Storage::disk('public')->delete($palaceService->image);
        }

        $palaceService->delete();

        return redirect()->route('dashboard.palace-services.index')
            ->with('success', 'Service palace supprimé avec succès !');
    }
}
