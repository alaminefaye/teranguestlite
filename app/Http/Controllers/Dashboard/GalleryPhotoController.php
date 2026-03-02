<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseGalleryAlbum;
use App\Models\EnterpriseGalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Photos d'un album. Taille max image : 20 Mo.
 */
class GalleryPhotoController extends Controller
{
    private const MAX_IMAGE_KB = 20480; // 20 Mo

    public function index(EnterpriseGalleryAlbum $galleryAlbum): View
    {
        $photos = $galleryAlbum->photos()->ordered()->get();
        return view('pages.dashboard.gallery.photos.index', [
            'title' => 'Photos : ' . $galleryAlbum->name,
            'album' => $galleryAlbum,
            'photos' => $photos,
        ]);
    }

    public function store(Request $request, EnterpriseGalleryAlbum $galleryAlbum): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|max:' . self::MAX_IMAGE_KB,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ], [
            'photo.max' => 'L\'image ne doit pas dépasser 20 Mo.',
        ]);

        $path = $request->file('photo')->store(
            'enterprise-gallery/' . $galleryAlbum->enterprise_id . '/albums/' . $galleryAlbum->id,
            'public'
        );
        $maxOrder = $galleryAlbum->photos()->max('display_order') ?? 0;

        EnterpriseGalleryPhoto::create([
            'enterprise_id' => $galleryAlbum->enterprise_id,
            'enterprise_gallery_album_id' => $galleryAlbum->id,
            'path' => $path,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'display_order' => $maxOrder + 1,
        ]);

        return redirect()->route('dashboard.gallery-albums.photos.index', $galleryAlbum)
            ->with('success', 'Photo ajoutée.');
    }

    public function update(Request $request, EnterpriseGalleryAlbum $galleryAlbum, EnterpriseGalleryPhoto $photo): RedirectResponse
    {
        if ($photo->enterprise_gallery_album_id != $galleryAlbum->id) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'display_order' => 'nullable|integer|min:0',
        ]);
        $photo->update($validated);

        return redirect()->route('dashboard.gallery-albums.photos.index', $galleryAlbum)
            ->with('success', 'Photo mise à jour.');
    }

    public function destroy(EnterpriseGalleryAlbum $galleryAlbum, EnterpriseGalleryPhoto $photo): RedirectResponse
    {
        if ($photo->enterprise_gallery_album_id != $galleryAlbum->id) {
            abort(404);
        }
        if ($photo->path) {
            Storage::disk('public')->delete($photo->path);
        }
        $photo->delete();

        return redirect()->route('dashboard.gallery-albums.photos.index', $galleryAlbum)
            ->with('success', 'Photo supprimée.');
    }
}
