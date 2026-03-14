<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\ExcursionBooking;
use App\Models\LaundryRequest;
use App\Models\PalaceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuestReviewsController extends Controller
{
    private const TYPE_LABELS = [
        Order::class => 'Commandes',
        Reservation::class => 'Séjours (check-out)',
        ExcursionBooking::class => 'Excursions',
        LaundryRequest::class => 'Blanchisserie',
        PalaceRequest::class => 'Services Palace',
    ];

    /**
     * Liste des avis clients (satisfaction) pour l'entreprise + statistiques et analyse par type d'événement.
     */
    public function index(Request $request): View
    {
        $query = GuestReview::with(['user', 'room'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('reviewable_type')) {
            $type = $request->reviewable_type;
            if (in_array($type, array_keys(self::TYPE_LABELS), true)) {
                $query->where('reviewable_type', $type);
            }
        }

        $reviews = $query->paginate(20)->withQueryString();

        $reviews->getCollection()->transform(function (GuestReview $r) {
            $r->reviewable_label = $this->getReviewableLabel($r);
            $r->reviewable_type_label = self::TYPE_LABELS[$r->reviewable_type] ?? class_basename($r->reviewable_type);
            return $r;
        });

        $stats = $this->buildStats();
        $statsByType = $this->buildStatsByType();

        return view('pages.dashboard.guest-reviews.index', [
            'title' => 'Avis clients',
            'reviews' => $reviews,
            'stats' => $stats,
            'statsByType' => $statsByType,
            'typeLabels' => self::TYPE_LABELS,
        ]);
    }

    private function buildStats(): array
    {
        $total = GuestReview::count();
        $avg = round((float) GuestReview::avg('rating'), 1);
        $ratingCounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingCounts[$i] = GuestReview::where('rating', $i)->count();
        }
        $positive = GuestReview::whereIn('rating', [4, 5])->count();
        $pctPositive = $total > 0 ? round(100 * $positive / $total, 1) : 0;

        return [
            'total' => $total,
            'avg_rating' => $avg,
            'rating_5' => $ratingCounts[5],
            'rating_4' => $ratingCounts[4],
            'rating_3' => $ratingCounts[3],
            'rating_2' => $ratingCounts[2],
            'rating_1' => $ratingCounts[1],
            'pct_positive' => $pctPositive,
            'positive_count' => $positive,
        ];
    }

    private function buildStatsByType(): array
    {
        $rows = GuestReview::query()
            ->select('reviewable_type', DB::raw('COUNT(*) as count'), DB::raw('ROUND(AVG(rating), 1) as avg_rating'))
            ->groupBy('reviewable_type')
            ->orderByDesc('count')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'type' => $row->reviewable_type,
                'label' => self::TYPE_LABELS[$row->reviewable_type] ?? class_basename($row->reviewable_type),
                'count' => (int) $row->count,
                'avg_rating' => round((float) $row->avg_rating, 1),
            ];
        }
        return $result;
    }

    private function getReviewableLabel(GuestReview $r): string
    {
        $model = $r->reviewable_type;
        $id = $r->reviewable_id;
        $enterpriseId = $r->enterprise_id; // Ne résoudre que les entités de la même entreprise (SaaS)
        if ($model === Order::class) {
            $o = Order::where('enterprise_id', $enterpriseId)->find($id);
            return $o ? 'Commande ' . $o->order_number : 'Commande #' . $id;
        }
        if ($model === Reservation::class) {
            $res = Reservation::where('enterprise_id', $enterpriseId)->find($id);
            return $res ? 'Séjour ' . $res->reservation_number : 'Réservation #' . $id;
        }
        if ($model === ExcursionBooking::class) {
            $b = ExcursionBooking::where('enterprise_id', $enterpriseId)->with('excursion')->find($id);
            return $b && $b->excursion ? 'Excursion ' . $b->excursion->name : 'Excursion #' . $id;
        }
        if ($model === LaundryRequest::class) {
            $l = LaundryRequest::where('enterprise_id', $enterpriseId)->find($id);
            return $l ? 'Blanchisserie ' . $l->request_number : 'Blanchisserie #' . $id;
        }
        if ($model === PalaceRequest::class) {
            $p = PalaceRequest::where('enterprise_id', $enterpriseId)->find($id);
            return $p ? 'Service ' . $p->request_number : 'Service #' . $id;
        }
        return class_basename($model) . ' #' . $id;
    }
}
