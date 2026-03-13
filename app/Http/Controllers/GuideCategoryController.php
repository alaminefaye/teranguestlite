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
                'image' => $category->image,
                'order' => $category->order,
                'items' => $category->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'phone' => $item->phone,
                        'address' => $item->address,
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,
                        'image' => $item->image,
                        'order' => $item->order,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json($data);
    }
}
