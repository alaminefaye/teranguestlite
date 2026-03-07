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

        return response()->json($categories);
    }
}
