<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RoomGalleryPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Galerie Hébergements (Chambres & Suites) — Dashboard.
 *
 * Gestion des photos classées par type (chambre / suite).
 * Accepte les formats : jpg, jpeg, png, webp, gif, tif, tiff.
 * Les fichiers .tif/.tiff sont automatiquement convertis en JPEG via GD.
 */
class RoomGalleryController extends Controller
{
    private const MAX_IMAGE_KB  = 61440; // 60 Mo
    private const STORAGE_DISK  = 'public';
    private const STORAGE_PATH  = 'room-gallery';
    private const ALLOWED_MIMES = 'jpg,jpeg,png,webp,gif,tif,tiff';

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $enterpriseId = auth()->user()->enterprise_id;

        $photos = RoomGalleryPhoto::query()
            ->where('enterprise_id', $enterpriseId)
            ->ordered()
            ->get()
            ->groupBy('type');

        return view('pages.dashboard.room-gallery.index', [
            'title'   => 'Galerie Chambres & Suites',
            'photos'  => $photos,
            'types'   => RoomGalleryPhoto::TYPES,
        ]);
    }

    // -------------------------------------------------------------------------
    // Store (multi-upload)
    // -------------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type'     => 'required|in:chambre,suite',
            'photos'   => 'required|array|min:1',
            'photos.*' => 'required|file|max:' . self::MAX_IMAGE_KB,
        ], [
            'photos.required'   => 'Veuillez sélectionner au moins une photo.',
            'photos.*.max'      => 'Chaque image ne doit pas dépasser 30 Mo.',
        ]);

        $enterpriseId = auth()->user()->enterprise_id;
        $type         = $request->input('type');

        // Ordre de départ : après les photos existantes de ce type
        $nextOrder = RoomGalleryPhoto::query()
            ->where('enterprise_id', $enterpriseId)
            ->where('type', $type)
            ->max('display_order') ?? -1;

        foreach ($request->file('photos') as $file) {
            $originalExt = strtolower($file->getClientOriginalExtension());
            $storedPath  = $this->storePhoto($file, $originalExt);

            if (!$storedPath) {
                continue; // fichier corrompu – on skip
            }

            RoomGalleryPhoto::create([
                'enterprise_id'      => $enterpriseId,
                'type'               => $type,
                'title'              => null,
                'description'        => null,
                'path'               => $storedPath,
                'original_extension' => $originalExt,
                'display_order'      => ++$nextOrder,
                'is_active'          => true,
            ]);
        }

        return redirect()->route('dashboard.room-gallery.index')
            ->with('success', 'Photos ajoutées avec succès.');
    }

    // -------------------------------------------------------------------------
    // Update (titre / description)
    // -------------------------------------------------------------------------

    public function update(Request $request, RoomGalleryPhoto $roomGalleryPhoto): RedirectResponse
    {
        $this->authorizePhoto($roomGalleryPhoto);

        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_active'   => 'nullable|boolean',
        ]);

        $roomGalleryPhoto->update([
            'title'       => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('dashboard.room-gallery.index')
            ->with('success', 'Photo mise à jour.');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(RoomGalleryPhoto $roomGalleryPhoto): RedirectResponse
    {
        $this->authorizePhoto($roomGalleryPhoto);

        // Supprimer le fichier du storage
        if ($roomGalleryPhoto->path) {
            Storage::disk(self::STORAGE_DISK)->delete($roomGalleryPhoto->path);
        }

        $roomGalleryPhoto->delete();

        return redirect()->route('dashboard.room-gallery.index')
            ->with('success', 'Photo supprimée.');
    }

    // -------------------------------------------------------------------------
    // Reorder (AJAX)
    // -------------------------------------------------------------------------

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items'          => 'required|array',
            'items.*.id'     => 'required|integer',
            'items.*.order'  => 'required|integer|min:0',
        ]);

        $enterpriseId = auth()->user()->enterprise_id;

        foreach ($request->input('items') as $item) {
            RoomGalleryPhoto::query()
                ->where('id', $item['id'])
                ->where('enterprise_id', $enterpriseId)
                ->update(['display_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    /**
     * Stocke un fichier uploadé.
     * Les TIFF sont convertis en JPEG via GD avant stockage.
     *
     * @return string|null  chemin relatif au disk public, ou null en cas d'erreur
     */
    private function storePhoto(\Illuminate\Http\UploadedFile $file, string $ext): ?string
    {
        $isTiff = in_array($ext, ['tif', 'tiff'], true);

        if ($isTiff) {
            return $this->storeTiffAsJpeg($file);
        }

        // Fichier standard – stockage direct
        $path = $file->store(self::STORAGE_PATH, self::STORAGE_DISK);
        return $path ?: null;
    }

    /**
     * Convertit un fichier TIFF en JPEG via l'extension GD de PHP, puis le stocke.
     */
    private function storeTiffAsJpeg(\Illuminate\Http\UploadedFile $file): ?string
    {
        // GD ne supporte pas le TIFF nativement sur toutes les installations.
        // On essaie d'abord imagecreatefromtiff(), sinon on stocke brut.
        $tmpPath = $file->getRealPath();

        if (function_exists('imagecreatefromtiff')) {
            $gdImage = @imagecreatefromtiff($tmpPath);
        } else {
            // Fallback : stocker tel quel (le serveur n'a pas GD-TIFF)
            $path = $file->storeAs(
                self::STORAGE_PATH,
                uniqid('tif_', true) . '.tif',
                self::STORAGE_DISK
            );
            return $path ?: null;
        }

        if (!$gdImage) {
            // Fichier TIFF illisible par GD – stocker brut
            $path = $file->storeAs(
                self::STORAGE_PATH,
                uniqid('tif_', true) . '.tif',
                self::STORAGE_DISK
            );
            return $path ?: null;
        }

        // Générer un nom unique
        $filename = uniqid('room_', true) . '.jpg';
        $storagePath = self::STORAGE_PATH . '/' . $filename;
        $fullPath    = Storage::disk(self::STORAGE_DISK)->path($storagePath);

        // S'assurer que le répertoire existe
        @mkdir(dirname($fullPath), 0755, true);

        $ok = imagejpeg($gdImage, $fullPath, 90);
        imagedestroy($gdImage);

        return $ok ? $storagePath : null;
    }

    /** Vérifie que la photo appartient à l'entreprise de l'utilisateur connecté. */
    private function authorizePhoto(RoomGalleryPhoto $photo): void
    {
        abort_if($photo->enterprise_id !== auth()->user()->enterprise_id, 403);
    }
}
