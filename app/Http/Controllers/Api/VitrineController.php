<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\TranslatableApiHelper;
use App\Models\Enterprise;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitrineController extends Controller
{
    private function resolveEnterpriseId(): int
    {
        $envId = (int) env('VITRINE_ENTERPRISE_ID', 0);
        if ($envId > 0) {
            return $envId;
        }

        $activeId = Enterprise::query()->active()->value('id');
        if ($activeId) {
            return (int) $activeId;
        }

        $anyId = Enterprise::query()->value('id');
        if ($anyId) {
            return (int) $anyId;
        }

        abort(500, 'Aucune entreprise disponible pour la vitrine.');
    }

    public function enterprise(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $enterprise = Enterprise::find($enterpriseId);

        if (!$enterprise) {
            return response()->json([
                'success' => false,
                'message' => 'Entreprise non trouvée',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $enterprise->id,
                'name' => $enterprise->name,
                'logo' => $enterprise->logo ? asset('storage/' . $enterprise->logo) : null,
                'cover_photo' => $enterprise->cover_photo ? asset('storage/' . $enterprise->cover_photo) : null,
                'gym_hours' => $enterprise->gym_hours,
                'type' => $enterprise->type ?? null,
                'hotel_infos' => $enterprise->hotel_infos,
                'emergency' => $enterprise->emergency,
                'chatbot_url' => $enterprise->chatbot_url,
                'address' => $enterprise->address,
                'phone' => $enterprise->phone,
                'email' => $enterprise->email,
            ],
        ], 200);
    }

    public function restaurants(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();

        $query = Restaurant::query()->where('enterprise_id', $enterpriseId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('open_now')) {
            $query->open();
        }

        $restaurants = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants->map(function ($restaurant) {
                return [
                    'id' => $restaurant->id,
                    'name' => TranslatableApiHelper::translationsFor($restaurant, 'name'),
                    'type' => $restaurant->type,
                    'type_label' => $restaurant->type_label,
                    'description' => TranslatableApiHelper::translationsFor($restaurant, 'description'),
                    'cuisine_type' => $restaurant->cuisine_type,
                    'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                    'capacity' => $restaurant->capacity,
                    'opening_hours' => $restaurant->opening_hours,
                    'is_open_now' => $restaurant->is_open_now,
                    'today_hours' => $restaurant->today_hours,
                ];
            }),
        ], 200);
    }

    public function restaurantShow(int $id): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $restaurant = Restaurant::query()
            ->where('enterprise_id', $enterpriseId)
            ->where('id', $id)
            ->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $restaurant->id,
                'name' => TranslatableApiHelper::translationsFor($restaurant, 'name'),
                'type' => $restaurant->type,
                'type_label' => $restaurant->type_label,
                'description' => TranslatableApiHelper::translationsFor($restaurant, 'description'),
                'cuisine_type' => $restaurant->cuisine_type,
                'image' => $restaurant->image ? asset('storage/' . $restaurant->image) : null,
                'capacity' => $restaurant->capacity,
                'opening_hours' => $restaurant->opening_hours,
                'is_open_now' => $restaurant->is_open_now,
                'today_hours' => $restaurant->today_hours,
            ],
        ], 200);
    }

    public function roomServiceCategories(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();

        $query = MenuCategory::query()
            ->where('enterprise_id', $enterpriseId)
            ->withCount(['menuItems'])
            ->where('status', 'active');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => TranslatableApiHelper::translationsFor($category, 'name'),
                    'description' => TranslatableApiHelper::translationsFor($category, 'description'),
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'display_order' => $category->display_order,
                    'is_available' => $category->status === 'active',
                    'items_count' => $category->menu_items_count,
                ];
            }),
        ], 200);
    }

    public function roomServiceItems(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();

        $query = MenuItem::query()
            ->where('enterprise_id', $enterpriseId)
            ->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $items = $query->ordered()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => TranslatableApiHelper::translationsFor($item, 'name'),
                    'description' => TranslatableApiHelper::translationsFor($item, 'description'),
                    'price' => $item->price,
                    'formatted_price' => $item->formatted_price,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'preparation_time' => $item->preparation_time,
                    'is_available' => $item->is_available,
                    'category' => [
                        'id' => $item->category->id,
                        'name' => TranslatableApiHelper::translationsFor($item->category, 'name'),
                    ],
                ];
            }),
            'meta' => [
                'current_page' => $items->currentPage(),
                'from' => $items->firstItem(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'to' => $items->lastItem(),
                'total' => $items->total(),
            ],
            'links' => [
                'first' => $items->url(1),
                'last' => $items->url($items->lastPage()),
                'prev' => $items->previousPageUrl(),
                'next' => $items->nextPageUrl(),
            ],
        ], 200);
    }

    public function roomServiceItemShow(int $id): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $item = MenuItem::query()
            ->where('enterprise_id', $enterpriseId)
            ->with('category')
            ->where('id', $id)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'name' => TranslatableApiHelper::translationsFor($item, 'name'),
                'description' => TranslatableApiHelper::translationsFor($item, 'description'),
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'image' => $item->image ? asset('storage/' . $item->image) : null,
                'preparation_time' => $item->preparation_time,
                'preparation_time_text' => $item->preparation_time_text,
                'is_available' => $item->is_available,
                'category' => [
                    'id' => $item->category->id,
                    'name' => TranslatableApiHelper::translationsFor($item->category, 'name'),
                    'description' => TranslatableApiHelper::translationsFor($item->category, 'description'),
                ],
            ],
        ], 200);
    }
}

