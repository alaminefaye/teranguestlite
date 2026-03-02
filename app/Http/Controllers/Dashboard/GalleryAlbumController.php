<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseGalleryAlbum;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GalleryAlbumController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard.gallery.index');
    }

    public function create(): View
    {
        return view('pages.dashboard.gallery.albums.create', [
            'title' => 'Créer un album',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'display_order' => 'nullable|integer|min:0',
        ]);
        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = true;

        EnterpriseGalleryAlbum::create($validated);

        return redirect()->route('dashboard.gallery.index')
            ->with('success', 'Album créé.');
    }

    public function show(EnterpriseGalleryAlbum $galleryAlbum): View
    {
        $galleryAlbum->load('photos');
        return view('pages.dashboard.gallery.albums.show', [
            'title' => $galleryAlbum->name,
            'album' => $galleryAlbum,
        ]);
    }

    public function edit(EnterpriseGalleryAlbum $galleryAlbum): View
    {
        return view('pages.dashboard.gallery.albums.edit', [
            'title' => 'Modifier l\'album',
            'album' => $galleryAlbum,
        ]);
    }

    public function update(Request $request, EnterpriseGalleryAlbum $galleryAlbum): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $galleryAlbum->update($validated);

        return redirect()->route('dashboard.gallery.index')
            ->with('success', 'Album mis à jour.');
    }

    public function destroy(EnterpriseGalleryAlbum $galleryAlbum): RedirectResponse
    {
        $galleryAlbum->delete();
        return redirect()->route('dashboard.gallery.index')
            ->with('success', 'Album supprimé.');
    }
}
