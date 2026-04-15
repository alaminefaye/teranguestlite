<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GuideCategory;
use App\Models\GuideItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuideItemController extends Controller
{
    public function index(Request $request): View
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
        $categories = GuideCategory::orderBy('order')->get();

        return view('pages.dashboard.guides.items.index', [
            'title' => 'Éléments guides',
            'items' => $items,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): View
    {
        $categoryId = $request->get('category');
        $categories = GuideCategory::orderBy('order')->get();
        $category = $categoryId ? GuideCategory::find($categoryId) : null;

        return view('pages.dashboard.guides.items.create', [
            'title' => 'Nouvel élément',
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guide_category_id' => [
                'required',
                Rule::exists('guide_categories', 'id')->where(
                    fn ($q) => $q->where('enterprise_id', auth()->user()->enterprise_id)
                ),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['enterprise_id'] = auth()->user()->enterprise_id;
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('guides/items', 'public');
        }

        GuideItem::create($validated);

        return redirect()->route('dashboard.guide-items.index', [
            'category' => $validated['guide_category_id'],
        ])->with('success', 'Élément créé !');
    }

    public function edit(GuideItem $guide_item): View
    {
        $categories = GuideCategory::orderBy('order')->get();

        return view('pages.dashboard.guides.items.edit', [
            'title' => 'Modifier élément',
            'item' => $guide_item,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, GuideItem $guide_item): RedirectResponse
    {
        $validated = $request->validate([
            'guide_category_id' => [
                'required',
                Rule::exists('guide_categories', 'id')->where(
                    fn ($q) => $q->where('enterprise_id', auth()->user()->enterprise_id)
                ),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($guide_item->image) {
                Storage::disk('public')->delete($guide_item->image);
            }
            $validated['image'] = $request->file('image')->store('guides/items', 'public');
        }

        if ($request->boolean('remove_image') && $guide_item->image) {
            Storage::disk('public')->delete($guide_item->image);
            $validated['image'] = null;
        }

        $guide_item->update($validated);

        return redirect()->route('dashboard.guide-items.index', [
            'category' => $validated['guide_category_id'],
        ])->with('success', 'Élément mis à jour !');
    }

    public function destroy(GuideItem $guide_item): RedirectResponse
    {
        if ($guide_item->image) {
            Storage::disk('public')->delete($guide_item->image);
        }
        $categoryId = $guide_item->guide_category_id;
        $guide_item->delete();

        return redirect()->route('dashboard.guide-items.index', [
            'category' => $categoryId,
        ])->with('success', 'Élément supprimé !');
    }

    public function toggleActive(GuideItem $guide_item): RedirectResponse
    {
        $guide_item->update(['is_active' => !$guide_item->is_active]);

        return redirect()->back()->with('success', $guide_item->is_active ? 'Élément activé.' : 'Élément désactivé.');
    }
}
