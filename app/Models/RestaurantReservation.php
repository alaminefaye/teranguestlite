<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class RestaurantReservation extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'user_id', 'guest_id', 'restaurant_id', 'room_id', 'reservation_date', 'reservation_time', 'number_of_guests', 'special_requests', 'status', 'confirmed_at', 'cancelled_at'];
    
    protected $casts = ['reservation_date' => 'date', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function restaurant() { return $this->belongsTo(Restaurant::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
}
