<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion des annonces par une ENTREPRISE.
 * Chaque entreprise voit et gère uniquement ses propres annonces.
 */
class EnterpriseAnnouncementController extends Controller
{
    public function index(): View
    {
        $enterpriseId = auth()->user()->enterprise_id;

        $announcements = Announcement::where('enterprise_id', $enterpriseId)
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(15);

        $stats = [
            'total' => Announcement::where('enterprise_id', $enterpriseId)->count(),
            'active' => Announcement::where('enterprise_id', $enterpriseId)->where('is_active', true)->count(),
            'total_views' => Announcement::where('enterprise_id', $enterpriseId)->sum('view_count'),
        ];

        return view('pages.dashboard.enterprise-announcements.index', [
            'title' => 'Mes Annonces',
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.enterprise-announcements.create', [
            'title' => 'Nouvelle annonce',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $enterpriseId = auth()->user()->enterprise_id;
        $validated = $this->validateRequest($request);
        $validated['enterprise_id'] = $enterpriseId;
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $validated = $this->handleFileUploads($request, $validated);

        Announcement::create($validated);

        return redirect()->route('dashboard.enterprise-announcements.index')
            ->with('success', 'Annonce créée avec succès !');
    }

    public function show(Announcement $enterpriseAnnouncement): View
    {
        $this->authorizeAnnouncement($enterpriseAnnouncement);

        return view('pages.dashboard.enterprise-announcements.show', [
            'title' => $enterpriseAnnouncement->title ?? 'Annonce #' . $enterpriseAnnouncement->id,
            'announcement' => $enterpriseAnnouncement,
        ]);
    }

    public function edit(Announcement $enterpriseAnnouncement): View
    {
        $this->authorizeAnnouncement($enterpriseAnnouncement);

        return view('pages.dashboard.enterprise-announcements.edit', [
            'title' => 'Modifier l\'annonce',
            'announcement' => $enterpriseAnnouncement,
        ]);
    }

    public function update(Request $request, Announcement $enterpriseAnnouncement): RedirectResponse
    {
        $this->authorizeAnnouncement($enterpriseAnnouncement);

        $validated = $this->validateRequest($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        $validated = $this->handleFileUploads($request, $validated, $enterpriseAnnouncement);

        $enterpriseAnnouncement->update($validated);

        return redirect()->route('dashboard.enterprise-announcements.index')
            ->with('success', 'Annonce mise à jour !');
    }

    public function destroy(Announcement $enterpriseAnnouncement): RedirectResponse
    {
        $this->authorizeAnnouncement($enterpriseAnnouncement);

        $enterpriseAnnouncement->deleteFiles();
        $enterpriseAnnouncement->delete();

        return redirect()->route('dashboard.enterprise-announcements.index')
            ->with('success', 'Annonce supprimée.');
    }

    public function toggleActive(Announcement $enterpriseAnnouncement): RedirectResponse
    {
        $this->authorizeAnnouncement($enterpriseAnnouncement);

        $enterpriseAnnouncement->update(['is_active' => !$enterpriseAnnouncement->is_active]);
        $label = $enterpriseAnnouncement->is_active ? 'activée' : 'désactivée';

        return redirect()->route('dashboard.enterprise-announcements.index')
            ->with('success', "Annonce {$label}.");
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    private function authorizeAnnouncement(Announcement $announcement): void
    {
        if ((int) $announcement->enterprise_id !== (int) auth()->user()->enterprise_id) {
            abort(403);
        }
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:51200',
            'video' => 'nullable|mimes:mp4,webm,mov,ogv|max:51200',
            'display_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'display_duration_minutes' => 'nullable|integer|min:1|max:60',
        ]);
    }

    private function handleFileUploads(Request $request, array $validated, ?Announcement $existing = null): array
    {
        if ($request->hasFile('poster')) {
            if ($existing?->poster_path) {
                Storage::disk('public')->delete($existing->poster_path);
            }
            $validated['poster_path'] = $request->file('poster')->store('announcements/posters', 'public');
        }

        if ($request->hasFile('video')) {
            if ($existing?->video_path) {
                Storage::disk('public')->delete($existing->video_path);
            }
            $validated['video_path'] = $request->file('video')->store('announcements/videos', 'public');
        }

        if ($request->boolean('remove_poster') && $existing?->poster_path) {
            Storage::disk('public')->delete($existing->poster_path);
            $validated['poster_path'] = null;
        }
        if ($request->boolean('remove_video') && $existing?->video_path) {
            Storage::disk('public')->delete($existing->video_path);
            $validated['video_path'] = null;
        }

        return $validated;
    }
}
