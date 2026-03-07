<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GuideItem;
use App\Models\GuideCategory;
use Illuminate\Support\Facades\Storage;

class GuideItemController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->get('category');
        $query = GuideItem::with('category')->orderBy('order');

        if ($categoryId) {
            $query->where('guide_category_id', $categoryId);
            $category = GuideCategory::find($categoryId);
        } else {
            $category = null;
        }

        $items = $query->paginate(20);
        $categories = GuideCategory::orderBy('name')->get();

        return view('pages.admin.guide-items.index', compact('items', 'category', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = GuideCategory::orderBy('name')->get();
        $selectedCategory = $request->get('category');
        return view('pages.admin.guide-items.create', compact('categories', 'selectedCategory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guide_category_id' => 'required|exists:guide_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('guides/items', 'public');
        }

        GuideItem::create($validated);

        return redirect()->route('admin.guide-items.index', ['category' => $validated['guide_category_id']])
            ->with('success', 'Élément créé avec succès !');
    }

    public function edit(GuideItem $guideItem)
    {
        $categories = GuideCategory::orderBy('name')->get();
        return view('pages.admin.guide-items.edit', compact('guideItem', 'categories'));
    }

    public function update(Request $request, GuideItem $guideItem)
    {
        $validated = $request->validate([
            'guide_category_id' => 'required|exists:guide_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($guideItem->image) {
                Storage::disk('public')->delete($guideItem->image);
            }
            $validated['image'] = $request->file('image')->store('guides/items', 'public');
        }

        if ($request->boolean('remove_image') && $guideItem->image) {
            Storage::disk('public')->delete($guideItem->image);
            $validated['image'] = null;
        }

        $guideItem->update($validated);

        return redirect()->route('admin.guide-items.index', ['category' => $guideItem->guide_category_id])
            ->with('success', 'Élément mis à jour !');
    }

    public function destroy(GuideItem $guideItem)
    {
        $categoryId = $guideItem->guide_category_id;
        if ($guideItem->image) {
            Storage::disk('public')->delete($guideItem->image);
        }
        $guideItem->delete();

        return redirect()->route('admin.guide-items.index', ['category' => $categoryId])
            ->with('success', 'Élément supprimé.');
    }

    public function toggleActive(GuideItem $guideItem)
    {
        $guideItem->update(['is_active' => !$guideItem->is_active]);
        $label = $guideItem->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Élément {$label}.");
    }
}
