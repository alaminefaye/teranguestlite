<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LaundryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LaundryServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = LaundryService::query();

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
            'total' => LaundryService::count(),
            'available' => LaundryService::available()->count(),
            'washing' => LaundryService::where('category', 'washing')->count(),
            'express' => LaundryService::where('category', 'express')->count(),
        ];

        return view('pages.dashboard.laundry-services.index', [
            'title' => 'Services Blanchisserie',
            'services' => $services,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.laundry-services.create', ['title' => 'Créer un service blanchisserie']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:washing,ironing,dry_cleaning,express',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'turnaround_hours' => 'required|integer|min:1',
            'status' => 'required|in:available,unavailable',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;

        LaundryService::create($validated);

        return redirect()->route('dashboard.laundry-services.index')
            ->with('success', 'Service blanchisserie créé avec succès !');
    }

    public function show(LaundryService $laundryService): View
    {
        return view('pages.dashboard.laundry-services.show', [
            'title' => $laundryService->name,
            'service' => $laundryService,
        ]);
    }

    public function edit(LaundryService $laundryService): View
    {
        return view('pages.dashboard.laundry-services.edit', [
            'title' => 'Modifier ' . $laundryService->name,
            'service' => $laundryService,
        ]);
    }

    public function update(Request $request, LaundryService $laundryService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:washing,ironing,dry_cleaning,express',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'turnaround_hours' => 'required|integer|min:1',
            'status' => 'required|in:available,unavailable',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $laundryService->update($validated);

        return redirect()->route('dashboard.laundry-services.index')
            ->with('success', 'Service blanchisserie mis à jour avec succès !');
    }

    public function destroy(LaundryService $laundryService): RedirectResponse
    {
        $laundryService->delete();

        return redirect()->route('dashboard.laundry-services.index')
            ->with('success', 'Service blanchisserie supprimé avec succès !');
    }
}
