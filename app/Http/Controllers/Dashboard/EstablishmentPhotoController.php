<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentPhoto;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/** Photos de la galerie d'un établissement. Taille max image : 20 Mo. */
class EstablishmentPhotoController extends Controller
{
    private const MAX_IMAGE_KB = 20480;

    public function index(Establishment $establishment): View
    {
        $photos = $establishment->photos;
        return view('pages.dashboard.establishments.photos.index', [
            'title' => 'Galerie : ' . $establishment->name,
            'establishment' => $establishment,
            'photos' => $photos,
        ]);
    }

    public function store(Request $request, Establishment $establishment): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|max:' . self::MAX_IMAGE_KB,
            'caption' => 'nullable|string|max:255',
        ], [
            'photo.max' => 'L\'image ne doit pas dépasser 20 Mo.',
        ]);

        $path = $request->file('photo')->store(
            'establishments/' . $establishment->enterprise_id . '/' . $establishment->id,
            'public'
        );
        $maxOrder = $establishment->photos()->max('display_order') ?? 0;

        EstablishmentPhoto::create([
            'establishment_id' => $establishment->id,
            'path' => $path,
            'caption' => $request->input('caption'),
            'display_order' => $maxOrder + 1,
        ]);

        return redirect()->route('dashboard.establishments.photos.index', $establishment)
            ->with('success', 'Photo ajoutée.');
    }

    public function update(Request $request, Establishment $establishment, EstablishmentPhoto $photo): RedirectResponse
    {
        if ($photo->establishment_id != $establishment->id) {
            abort(404);
        }
        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);
        $photo->update($validated);

        return redirect()->route('dashboard.establishments.photos.index', $establishment)
            ->with('success', 'Photo mise à jour.');
    }

    public function destroy(Establishment $establishment, EstablishmentPhoto $photo): RedirectResponse
    {
        if ($photo->establishment_id != $establishment->id) {
            abort(404);
        }
        if ($photo->path) {
            Storage::disk('public')->delete($photo->path);
        }
        $photo->delete();

        return redirect()->route('dashboard.establishments.photos.index', $establishment)
            ->with('success', 'Photo supprimée.');
    }
}
