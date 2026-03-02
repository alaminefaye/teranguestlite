<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Galerie entreprise : image d'établissement (cover_photo) + liste des albums.
 * Taille max images : 20 Mo.
 */
class GalleryController extends Controller
{
    private const MAX_IMAGE_KB = 20480; // 20 Mo

    public function index(): View
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }
        $albums = $enterprise->galleryAlbums()->withCount('photos')->ordered()->get();

        return view('pages.dashboard.gallery.index', [
            'title' => 'Galerie',
            'enterprise' => $enterprise,
            'albums' => $albums,
        ]);
    }

    public function updateCoverPhoto(Request $request): RedirectResponse
    {
        $enterprise = auth()->user()->enterprise;
        if (!$enterprise) {
            abort(404, 'Établissement non trouvé.');
        }

        $request->validate([
            'cover_photo' => 'required|image|max:' . self::MAX_IMAGE_KB,
        ], [
            'cover_photo.max' => 'L\'image ne doit pas dépasser 20 Mo.',
        ]);

        if ($enterprise->cover_photo) {
            Storage::disk('public')->delete($enterprise->cover_photo);
        }
        $path = $request->file('cover_photo')->store('enterprise-gallery/' . $enterprise->id, 'public');
        $enterprise->update(['cover_photo' => $path]);

        return redirect()->route('dashboard.gallery.index')
            ->with('success', 'Image d\'établissement mise à jour.');
    }
}
