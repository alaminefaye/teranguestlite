<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class ExcursionBooking extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'user_id', 'guest_id', 'excursion_id', 'room_id', 'booking_date', 'number_of_adults', 'number_of_children', 'total_price', 'special_requests', 'status', 'confirmed_at', 'cancelled_at'];
    
    protected $casts = ['booking_date' => 'date', 'total_price' => 'decimal:2', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function excursion() { return $this->belongsTo(Excursion::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    
    public function getFormattedTotalPriceAttribute() { return number_format($this->total_price, 0, ',', ' ') . ' FCFA'; }
}
