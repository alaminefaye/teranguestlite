<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GuideCategory;

class GuideCategoryController extends Controller
{
    /**
     * Get all active guide categories with their active items.
     */
    public function index()
    {
        $categories = GuideCategory::with([
            'items' => function ($query) {
                $query->where('is_active', true)->orderBy('order', 'asc');
            }
        ])
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'category_type' => $category->category_type,
                'image' => $category->image,
                'order' => $category->order,
                'is_active' => $category->is_active,
                'items' => $category->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'guide_category_id' => $item->guide_category_id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'phone' => $item->phone,
                        'address' => $item->address,
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,
                        'image' => $item->image,
                        'order' => $item->order,
                        'is_active' => $item->is_active,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json($data);
    }
}
