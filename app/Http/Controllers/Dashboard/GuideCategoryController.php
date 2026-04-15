<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GuideCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GuideCategoryController extends Controller
{
    public function index(): View
    {
        $categories = GuideCategory::withCount('items')->orderBy('order')->get();

        return view('pages.dashboard.guides.categories.index', [
            'title' => 'Guides & Infos',
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('pages.dashboard.guides.categories.create', [
            'title' => 'Nouvelle catégorie',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_type' => 'nullable|in:equipment_guide,useful_numbers,other',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('guides/categories', 'public');
        }

        GuideCategory::create($validated);

        return redirect()->route('dashboard.guide-categories.index')
            ->with('success', 'Catégorie créée !');
    }

    public function edit(GuideCategory $guide_category): View
    {
        return view('pages.dashboard.guides.categories.edit', [
            'title' => 'Modifier catégorie',
            'category' => $guide_category,
        ]);
    }

    public function update(Request $request, GuideCategory $guide_category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_type' => 'nullable|in:equipment_guide,useful_numbers,other',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($guide_category->image) {
                Storage::disk('public')->delete($guide_category->image);
            }
            $validated['image'] = $request->file('image')->store('guides/categories', 'public');
        }

        if ($request->boolean('remove_image') && $guide_category->image) {
            Storage::disk('public')->delete($guide_category->image);
            $validated['image'] = null;
        }

        $guide_category->update($validated);

        return redirect()->route('dashboard.guide-categories.index')
            ->with('success', 'Catégorie mise à jour !');
    }

    public function destroy(GuideCategory $guide_category): RedirectResponse
    {
        if ($guide_category->image) {
            Storage::disk('public')->delete($guide_category->image);
        }
        $guide_category->delete();

        return redirect()->route('dashboard.guide-categories.index')
            ->with('success', 'Catégorie supprimée !');
    }

    public function toggleActive(GuideCategory $guide_category): RedirectResponse
    {
        $guide_category->update(['is_active' => !$guide_category->is_active]);

        return redirect()->route('dashboard.guide-categories.index')
            ->with('success', $guide_category->is_active ? 'Catégorie activée.' : 'Catégorie désactivée.');
    }
}

