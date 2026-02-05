<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SpaService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SpaServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = SpaService::query();

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
            'total' => SpaService::count(),
            'available' => SpaService::available()->count(),
            'featured' => SpaService::featured()->count(),
            'massages' => SpaService::byCategory('massage')->count(),
        ];

        return view('pages.dashboard.spa-services.index', [
            'title' => 'Services Spa',
            'services' => $services,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.spa-services.create', ['title' => 'Créer un service spa']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:massage,facial,body_treatment,wellness',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'status' => 'required|in:available,unavailable',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('spa-services', 'public');
        }

        SpaService::create($validated);

        return redirect()->route('dashboard.spa-services.index')
            ->with('success', 'Service spa créé avec succès !');
    }

    public function show(SpaService $spaService): View
    {
        return view('pages.dashboard.spa-services.show', [
            'title' => $spaService->name,
            'service' => $spaService,
        ]);
    }

    public function edit(SpaService $spaService): View
    {
        return view('pages.dashboard.spa-services.edit', [
            'title' => 'Modifier ' . $spaService->name,
            'service' => $spaService,
        ]);
    }

    public function update(Request $request, SpaService $spaService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:massage,facial,body_treatment,wellness',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:30720',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'status' => 'required|in:available,unavailable',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            if ($spaService->image) {
                Storage::disk('public')->delete($spaService->image);
            }
            $validated['image'] = $request->file('image')->store('spa-services', 'public');
        }

        $spaService->update($validated);

        return redirect()->route('dashboard.spa-services.index')
            ->with('success', 'Service spa mis à jour avec succès !');
    }

    public function destroy(SpaService $spaService): RedirectResponse
    {
        if ($spaService->image) {
            Storage::disk('public')->delete($spaService->image);
        }

        $spaService->delete();

        return redirect()->route('dashboard.spa-services.index')
            ->with('success', 'Service spa supprimé avec succès !');
    }
}
