<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class GuestReview extends Model
{
    use EnterpriseScopeTrait;

    protected $table = 'guest_reviews';

    protected $fillable = [
        'enterprise_id',
        'user_id',
        'guest_id',
        'room_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * Types éligibles pour un avis (après livraison / check-out / demande traitée / excursion terminée).
     */
    public static function reviewableTypes(): array
    {
        return [
            \App\Models\Order::class,
            \App\Models\Reservation::class,
            \App\Models\ExcursionBooking::class,
            \App\Models\LaundryRequest::class,
            \App\Models\PalaceRequest::class,
        ];
    }
}
