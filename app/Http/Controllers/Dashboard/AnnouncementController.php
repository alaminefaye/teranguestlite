<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion des annonces par le SUPER ADMIN.
 * Accessible uniquement aux utilisateurs ayant le rôle 'super_admin'.
 */
class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::whereNull('enterprise_id')
            ->with('targetEnterprises')
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(15);

        $stats = [
            'total' => Announcement::whereNull('enterprise_id')->count(),
            'active' => Announcement::whereNull('enterprise_id')->where('is_active', true)->count(),
            'total_views' => Announcement::whereNull('enterprise_id')->sum('view_count'),
        ];

        return view('pages.dashboard.announcements.index', [
            'title' => 'Annonces (Super Admin)',
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $enterprises = Enterprise::orderBy('name')->get(['id', 'name']);

        return view('pages.dashboard.announcements.create', [
            'title' => 'Nouvelle annonce',
            'enterprises' => $enterprises,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['enterprise_id'] = null; // super admin
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['target_all_enterprises'] = $request->boolean('target_all_enterprises', false);

        $validated = $this->handleFileUploads($request, $validated);

        $announcement = Announcement::create($validated);

        // Ciblage sélectif
        if (!$announcement->target_all_enterprises) {
            $enterpriseIds = array_filter((array) $request->input('enterprise_ids', []));
            $announcement->targetEnterprises()->sync($enterpriseIds);
        }

        return redirect()->route('dashboard.announcements.index')
            ->with('success', 'Annonce créée avec succès !');
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load('targetEnterprises');

        return view('pages.dashboard.announcements.show', [
            'title' => $announcement->title ?? 'Annonce #' . $announcement->id,
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        $enterprises = Enterprise::orderBy('name')->get(['id', 'name']);
        $announcement->load('targetEnterprises');

        return view('pages.dashboard.announcements.edit', [
            'title' => 'Modifier l\'annonce',
            'announcement' => $announcement,
            'enterprises' => $enterprises,
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $this->validateRequest($request, $announcement);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['target_all_enterprises'] = $request->boolean('target_all_enterprises', false);

        $validated = $this->handleFileUploads($request, $validated, $announcement);

        $announcement->update($validated);

        // Ciblage sélectif
        if ($announcement->target_all_enterprises) {
            $announcement->targetEnterprises()->detach();
        } else {
            $enterpriseIds = array_filter((array) $request->input('enterprise_ids', []));
            $announcement->targetEnterprises()->sync($enterpriseIds);
        }

        return redirect()->route('dashboard.announcements.index')
            ->with('success', 'Annonce mise à jour !');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->deleteFiles();
        $announcement->targetEnterprises()->detach();
        $announcement->delete();

        return redirect()->route('dashboard.announcements.index')
            ->with('success', 'Annonce supprimée.');
    }

    public function toggleActive(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        $label = $announcement->is_active ? 'activée' : 'désactivée';
        return redirect()->route('dashboard.announcements.index')
            ->with('success', "Annonce {$label}.");
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    private function validateRequest(Request $request, ?Announcement $existing = null): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:20480',
            'video' => 'nullable|mimes:mp4,webm,mov,ogv|max:20480',
            'display_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'display_duration_minutes' => 'nullable|integer|min:1|max:60',
            'enterprise_ids' => 'nullable|array',
            'enterprise_ids.*' => 'exists:enterprises,id',
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

        // Permettre la suppression explicite des fichiers
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
