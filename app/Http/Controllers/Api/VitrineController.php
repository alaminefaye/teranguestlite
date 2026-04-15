<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\TranslatableApiHelper;
use App\Models\AmenityCategory;
use App\Models\Announcement;
use App\Models\Establishment;
use App\Models\Excursion;
use App\Models\Enterprise;
use App\Models\GuideCategory;
use App\Models\LaundryService;
use App\Models\LeisureCategory;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\PalaceService;
use App\Models\Restaurant;
use App\Models\SeminarRoom;
use App\Models\SpaService;
use App\Models\Vehicle;
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

    private function decodeJsonList(mixed $value): array
    {
        if (is_array($value)) return $value;
        if ($value === null) return [];
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') return [];
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return [$trimmed];
        }
        return [];
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
                'animations' => $enterprise->animations,
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
                    'menu_file' => $restaurant->menu_file ? asset('storage/' . $restaurant->menu_file) : null,
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
                'menu_file' => $restaurant->menu_file ? asset('storage/' . $restaurant->menu_file) : null,
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

    public function spaServices(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $query = SpaService::query()
            ->where('enterprise_id', $enterpriseId)
            ->active();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => TranslatableApiHelper::translationsFor($service, 'name'),
                    'category' => $service->category,
                    'category_label' => $service->category_label,
                    'description' => TranslatableApiHelper::translationsFor($service, 'description'),
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'duration' => $service->duration,
                    'duration_text' => $service->duration_text,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'features' => $service->features,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    public function spaServiceShow(int $id): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $service = SpaService::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->where('id', $id)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service spa non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'name' => TranslatableApiHelper::translationsFor($service, 'name'),
                'category' => $service->category,
                'category_label' => $service->category_label,
                'description' => TranslatableApiHelper::translationsFor($service, 'description'),
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'duration' => $service->duration,
                'duration_text' => $service->duration_text,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'features' => $service->features,
                'is_available' => $service->is_available,
            ],
        ], 200);
    }

    public function excursions(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $query = Excursion::query()
            ->where('enterprise_id', $enterpriseId)
            ->active();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $excursions = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $excursions->map(function ($excursion) {
                return [
                    'id' => $excursion->id,
                    'name' => TranslatableApiHelper::translationsFor($excursion, 'name'),
                    'type' => $excursion->type,
                    'type_label' => $excursion->type_label,
                    'description' => TranslatableApiHelper::translationsFor($excursion, 'description'),
                    'price_adult' => $excursion->price_adult,
                    'price_child' => $excursion->price_child,
                    'formatted_price_adult' => $excursion->formatted_price_adult,
                    'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                    'duration_hours' => $excursion->duration_hours,
                    'departure_time' => $excursion->departure_time,
                    'schedule_description' => $excursion->schedule_description,
                    'children_age_range' => $excursion->children_age_range,
                    'image' => $excursion->image ? asset('storage/' . $excursion->image) : null,
                    'min_participants' => $excursion->min_participants,
                    'max_participants' => $excursion->max_participants,
                    'included' => $this->decodeJsonList($excursion->included),
                    'not_included' => $this->decodeJsonList($excursion->not_included),
                    'is_available' => $excursion->is_available,
                    'is_featured' => $excursion->is_featured,
                ];
            }),
        ], 200);
    }

    public function excursionShow(int $id): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $excursion = Excursion::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->where('id', $id)
            ->first();

        if (!$excursion) {
            return response()->json([
                'success' => false,
                'message' => 'Excursion non trouvée',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $excursion->id,
                'name' => TranslatableApiHelper::translationsFor($excursion, 'name'),
                'type' => $excursion->type,
                'type_label' => $excursion->type_label,
                'description' => TranslatableApiHelper::translationsFor($excursion, 'description'),
                'price_adult' => $excursion->price_adult,
                'price_child' => $excursion->price_child,
                'formatted_price_adult' => $excursion->formatted_price_adult,
                'formatted_price_child' => number_format($excursion->price_child, 0, '', ' ') . ' FCFA',
                'duration_hours' => $excursion->duration_hours,
                'departure_time' => $excursion->departure_time,
                'schedule_description' => $excursion->schedule_description,
                'children_age_range' => $excursion->children_age_range,
                'image' => $excursion->image ? asset('storage/' . $excursion->image) : null,
                'min_participants' => $excursion->min_participants,
                'max_participants' => $excursion->max_participants,
                'included' => $this->decodeJsonList($excursion->included),
                'not_included' => $this->decodeJsonList($excursion->not_included),
                'is_available' => $excursion->is_available,
            ],
        ], 200);
    }

    public function laundryServices(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $query = LaundryService::query()
            ->where('enterprise_id', $enterpriseId)
            ->active();

        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => TranslatableApiHelper::translationsFor($service, 'name'),
                    'category' => $service->category,
                    'category_label' => $service->category_label,
                    'description' => TranslatableApiHelper::translationsFor($service, 'description'),
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'turnaround_hours' => $service->turnaround_hours,
                    'is_available' => $service->is_available,
                ];
            }),
        ], 200);
    }

    public function palaceServices(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $services = PalaceService::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services->map(fn ($s) => [
                'id' => $s->id,
                'name' => TranslatableApiHelper::translationsFor($s, 'name'),
                'category' => $s->category,
                'category_label' => $s->category_label,
                'description' => TranslatableApiHelper::translationsFor($s, 'description'),
                'price' => $s->price,
                'formatted_price' => $s->formatted_price,
                'price_on_request' => $s->price_on_request,
                'is_premium' => $s->is_premium,
                'image' => $s->image ? asset('storage/' . $s->image) : null,
                'is_available' => $s->is_available,
                'is_emergency' => $s->is_emergency,
                'is_guided_tours' => $s->isGuidedToursService(),
            ]),
        ], 200);
    }

    public function seminars(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $rooms = SeminarRoom::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms->map(fn ($r) => [
                'id' => $r->id,
                'name' => TranslatableApiHelper::translationsFor($r, 'name'),
                'description' => TranslatableApiHelper::translationsFor($r, 'description'),
                'capacity' => $r->capacity,
                'equipments' => $r->equipments ?? [],
                'image' => $r->image ? asset('storage/' . $r->image) : null,
                'contact_phone' => $r->contact_phone,
                'contact_email' => $r->contact_email,
            ]),
        ], 200);
    }

    public function vehicles(Request $request): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $enterprise = Enterprise::find($enterpriseId);
        $settings = $enterprise && is_array($enterprise->settings) ? $enterprise->settings : [];
        $rentalMode = in_array($settings['vehicle_rental_mode'] ?? '', ['form'], true)
            ? 'form'
            : 'catalogue';

        $query = Vehicle::query()
            ->where('enterprise_id', $enterpriseId)
            ->available()
            ->ordered();

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('seats') && is_numeric($request->seats)) {
            $query->where('number_of_seats', '>=', (int) $request->seats);
        }

        $vehicles = $query->get()->map(fn (Vehicle $v) => [
            'id' => $v->id,
            'name' => $v->name,
            'vehicle_type' => $v->vehicle_type,
            'vehicle_type_label' => $v->type_label,
            'number_of_seats' => $v->number_of_seats,
            'image' => $v->image ? url('storage/' . $v->image) : null,
            'price_per_day' => $v->price_per_day !== null ? (float) $v->price_per_day : null,
            'price_half_day' => $v->price_half_day !== null ? (float) $v->price_half_day : null,
            'formatted_price_per_day' => $v->formatted_price_per_day,
            'formatted_price_half_day' => $v->formatted_price_half_day,
        ]);

        return response()->json([
            'success' => true,
            'rental_mode' => $rentalMode,
            'data' => $vehicles,
        ]);
    }

    public function leisureCategories(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $mainCategories = LeisureCategory::query()
            ->where('enterprise_id', $enterpriseId)
            ->with(['children' => function ($q) {
                $q->active()->ordered();
            }])
            ->topLevel()
            ->active()
            ->ordered()
            ->get();

        $data = $mainCategories->map(function ($main) {
            return [
                'id' => $main->id,
                'name' => $main->name,
                'description' => $main->description,
                'type' => $main->type,
                'display_order' => $main->display_order,
                'children' => $main->children->filter(fn ($c) => $c->is_active)->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'description' => $c->description,
                    'type' => $c->type,
                    'display_order' => $c->display_order,
                ])->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function amenityCategories(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $categories = AmenityCategory::query()
            ->where('enterprise_id', $enterpriseId)
            ->with(['items' => function ($q) {
                $q->active()->orderBy('display_order')->orderBy('name');
            }])
            ->active()
            ->ordered()
            ->get();

        $data = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'display_order' => $cat->display_order,
                'items' => $cat->items->filter(fn ($item) => $item->is_active)->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'display_order' => $item->display_order,
                ])->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function establishments(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $establishments = Establishment::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Establishment $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'location' => $e->location,
                'cover_photo' => $e->cover_photo ? asset('storage/' . $e->cover_photo) : null,
            ]);

        return response()->json([
            'success' => true,
            'data' => $establishments,
        ], 200);
    }

    public function establishmentShow(int $id): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $establishment = Establishment::query()
            ->where('enterprise_id', $enterpriseId)
            ->active()
            ->where('id', $id)
            ->first();

        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Établissement introuvable.',
            ], 404);
        }

        $establishment->load('photos');
        $photos = $establishment->photos->map(fn ($p) => [
            'id' => $p->id,
            'url' => $p->path ? asset('storage/' . $p->path) : null,
            'caption' => $p->caption,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $establishment->id,
                'name' => $establishment->name,
                'location' => $establishment->location,
                'cover_photo' => $establishment->cover_photo ? asset('storage/' . $establishment->cover_photo) : null,
                'description' => $establishment->description,
                'address' => $establishment->address,
                'phone' => $establishment->phone,
                'website' => $establishment->website,
                'photos' => $photos,
            ],
        ], 200);
    }

    public function announcements(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $announcements = Announcement::eligibleForEnterprise($enterpriseId)->get();

        return response()->json([
            'data' => $announcements->map(fn ($a) => $a->toApiArray())->values(),
        ]);
    }

    public function guides(): JsonResponse
    {
        $enterpriseId = $this->resolveEnterpriseId();
        $baseQuery = GuideCategory::with([
            'items' => function ($query) {
                $query->where('is_active', true)->orderBy('order', 'asc');
            },
        ])
            ->where('is_active', true);

        $hasEnterpriseGuides = (clone $baseQuery)
            ->where('enterprise_id', $enterpriseId)
            ->exists();

        $categories = $hasEnterpriseGuides
            ? $baseQuery->where('enterprise_id', $enterpriseId)->orderBy('order', 'asc')->get()
            : $baseQuery->whereNull('enterprise_id')->orderBy('order', 'asc')->get();

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
