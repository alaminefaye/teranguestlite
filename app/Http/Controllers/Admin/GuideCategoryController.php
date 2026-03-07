<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GuideCategory;

class GuideCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = GuideCategory::withCount('items')->orderBy('order')->get();
        return view('pages.admin.guide-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.admin.guide-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('guides/categories', 'public');
        }

        GuideCategory::create($validated);

        return redirect()->route('admin.guide-categories.index')
            ->with('success', 'Catégorie créée avec succès !');
    }

    public function show(string $id)
    {
        // View items (Alternative: GuideItemController filter)
    }

    public function edit(GuideCategory $guideCategory)
    {
        return view('pages.admin.guide-categories.edit', compact('guideCategory'));
    }

    public function update(Request $request, GuideCategory $guideCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($guideCategory->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($guideCategory->image);
            }
            $validated['image'] = $request->file('image')->store('guides/categories', 'public');
        }

        if ($request->boolean('remove_image') && $guideCategory->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($guideCategory->image);
            $validated['image'] = null;
        }

        $guideCategory->update($validated);

        return redirect()->route('admin.guide-categories.index')
            ->with('success', 'Catégorie mise à jour !');
    }

    public function destroy(GuideCategory $guideCategory)
    {
        if ($guideCategory->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($guideCategory->image);
        }
        $guideCategory->delete();

        return redirect()->route('admin.guide-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }

    public function toggleActive(GuideCategory $guideCategory)
    {
        $guideCategory->update(['is_active' => !$guideCategory->is_active]);
        $label = $guideCategory->is_active ? 'activée' : 'désactivée';
        return redirect()->route('admin.guide-categories.index')
            ->with('success', "Catégorie {$label}.");
    }
}
