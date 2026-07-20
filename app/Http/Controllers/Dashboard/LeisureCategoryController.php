<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\LeisureCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class LeisureCategoryController extends Controller
{
    /** Liste des catégories principales uniquement (Sport, Loisirs). */
    public function index(Request $request): View
    {
        $query = LeisureCategory::withCount('children')->topLevel();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->ordered()->paginate(12);

        return view('pages.dashboard.leisure-categories.index', [
            'title' => 'Bien-être, Sport & Loisirs',
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.leisure-categories.create', [
            'title' => 'Ajouter une catégorie principale',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:sport,loisirs',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['parent_id'] = null;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        LeisureCategory::create($validated);

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(LeisureCategory $leisureCategory): View
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        return view('pages.dashboard.leisure-categories.edit', [
            'title' => 'Modifier la catégorie',
            'category' => $leisureCategory,
        ]);
    }

    public function update(Request $request, LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:sport,loisirs',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $leisureCategory->update($validated);

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $leisureCategory->delete();

        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }

    /** Masquer / Afficher (sans supprimer). */
    public function toggleActive(LeisureCategory $leisureCategory): RedirectResponse
    {
        if ($leisureCategory->parent_id !== null) {
            abort(404);
        }
        $leisureCategory->update(['is_active' => !$leisureCategory->is_active]);
        $label = $leisureCategory->is_active ? 'affichée' : 'masquée';
        return redirect()->route('dashboard.leisure-categories.index')
            ->with('success', "Catégorie {$label}.");
    }

    public function sportSettings(): View
    {
        $enterprise = Enterprise::find(auth()->user()->enterprise_id);
        return view('pages.dashboard.leisure-categories.sport-settings', [
            'title'        => 'Paramètres Sport & Fitness',
            'sportSettings' => $enterprise ? $enterprise->sport_settings : ['display_mode' => 'catalog', 'document_url' => null, 'document_url_fr' => null, 'document_url_en' => null],
        ]);
    }

    public function updateSportSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'display_mode' => 'required|in:catalog,document',
            'document'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
            'document_fr'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
            'document_en'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        $enterprise = Enterprise::find(auth()->user()->enterprise_id);
        if (!$enterprise) {
            return back()->with('error', 'Entreprise introuvable.');
        }

        $settings = is_array($enterprise->settings) ? $enterprise->settings : [];
        $sport = $settings['sport'] ?? [];
        $sport['display_mode'] = $request->display_mode;

        if ($request->hasFile('document')) {
            if (!empty($sport['document_path'])) {
                Storage::disk('public')->delete($sport['document_path']);
            }
            $sport['document_path'] = $request->file('document')->store('sport-documents', 'public');
            unset($sport['document_url']);
        }

        if ($request->hasFile('document_fr')) {
            if (!empty($sport['document_path_fr'])) {
                Storage::disk('public')->delete($sport['document_path_fr']);
            }
            $sport['document_path_fr'] = $request->file('document_fr')->store('sport-documents', 'public');
            unset($sport['document_url_fr']);
        }

        if ($request->hasFile('document_en')) {
            if (!empty($sport['document_path_en'])) {
                Storage::disk('public')->delete($sport['document_path_en']);
            }
            $sport['document_path_en'] = $request->file('document_en')->store('sport-documents', 'public');
            unset($sport['document_url_en']);
        }

        $settings['sport'] = $sport;
        $enterprise->update(['settings' => $settings]);

        return redirect()->route('dashboard.sport-settings')
            ->with('success', 'Paramètres Sport & Fitness mis à jour avec succès !');
    }
}
