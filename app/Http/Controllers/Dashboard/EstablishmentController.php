<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class EstablishmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Establishment::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $establishments = $query->ordered()->paginate(12);

        return view('pages.dashboard.establishments.index', [
            'title' => 'Nos établissements',
            'establishments' => $establishments,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.establishments.create', ['title' => 'Ajouter un établissement']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'cover_photo' => 'nullable|image|max:20480',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = true;

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store(
                'establishments/' . auth()->user()->enterprise_id,
                'public'
            );
        }

        Establishment::create($validated);

        return redirect()->route('dashboard.establishments.index')
            ->with('success', 'Établissement créé.');
    }

    public function show(Establishment $establishment): View
    {
        $establishment->load('photos');
        return view('pages.dashboard.establishments.show', [
            'title' => $establishment->name,
            'establishment' => $establishment,
        ]);
    }

    public function edit(Establishment $establishment): View
    {
        return view('pages.dashboard.establishments.edit', [
            'title' => 'Modifier ' . $establishment->name,
            'establishment' => $establishment,
        ]);
    }

    public function update(Request $request, Establishment $establishment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'cover_photo' => 'nullable|image|max:20480',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('cover_photo')) {
            if ($establishment->cover_photo) {
                Storage::disk('public')->delete($establishment->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')->store(
                'establishments/' . $establishment->enterprise_id,
                'public'
            );
        }

        $establishment->update($validated);

        return redirect()->route('dashboard.establishments.index')
            ->with('success', 'Établissement mis à jour.');
    }

    public function destroy(Establishment $establishment): RedirectResponse
    {
        if ($establishment->cover_photo) {
            Storage::disk('public')->delete($establishment->cover_photo);
        }
        foreach ($establishment->photos as $photo) {
            if ($photo->path) {
                Storage::disk('public')->delete($photo->path);
            }
        }
        $establishment->delete();

        return redirect()->route('dashboard.establishments.index')
            ->with('success', 'Établissement supprimé.');
    }
}
