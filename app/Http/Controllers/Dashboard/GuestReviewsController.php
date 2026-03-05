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
use Illuminate\View\View;

class GuestReviewsController extends Controller
{
    /**
     * Liste des avis clients (satisfaction) pour l'entreprise.
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

        $reviews = $query->paginate(20)->withQueryString();

        $reviews->getCollection()->transform(function (GuestReview $r) {
            $r->reviewable_label = $this->getReviewableLabel($r);
            return $r;
        });

        $stats = [
            'total' => GuestReview::count(),
            'avg_rating' => round(GuestReview::avg('rating') ?? 0, 1),
            'rating_5' => GuestReview::where('rating', 5)->count(),
        ];

        return view('pages.dashboard.guest-reviews.index', [
            'title' => 'Avis clients',
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    private function getReviewableLabel(GuestReview $r): string
    {
        $model = $r->reviewable_type;
        $id = $r->reviewable_id;
        if ($model === Order::class) {
            $o = Order::withoutGlobalScope('enterprise')->find($id);
            return $o ? 'Commande ' . $o->order_number : 'Commande #' . $id;
        }
        if ($model === Reservation::class) {
            $res = Reservation::withoutGlobalScope('enterprise')->find($id);
            return $res ? 'Séjour ' . $res->reservation_number : 'Réservation #' . $id;
        }
        if ($model === ExcursionBooking::class) {
            $b = ExcursionBooking::withoutGlobalScope('enterprise')->with('excursion')->find($id);
            return $b && $b->excursion ? 'Excursion ' . $b->excursion->name : 'Excursion #' . $id;
        }
        if ($model === LaundryRequest::class) {
            $l = LaundryRequest::withoutGlobalScope('enterprise')->find($id);
            return $l ? 'Blanchisserie ' . $l->request_number : 'Blanchisserie #' . $id;
        }
        if ($model === PalaceRequest::class) {
            $p = PalaceRequest::withoutGlobalScope('enterprise')->find($id);
            return $p ? 'Service ' . $p->request_number : 'Service #' . $id;
        }
        return class_basename($model) . ' #' . $id;
    }
}
