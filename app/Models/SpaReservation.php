<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class SpaReservation extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'user_id', 'spa_service_id', 'room_id', 'reservation_date', 'reservation_time', 'special_requests', 'price', 'status', 'confirmed_at', 'cancelled_at'];
    
    protected $casts = ['reservation_date' => 'date', 'price' => 'decimal:2', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function spaService() { return $this->belongsTo(SpaService::class); }
    public function room() { return $this->belongsTo(Room::class); }
    
    public function getFormattedPriceAttribute() { return number_format($this->price, 0, ',', ' ') . ' FCFA'; }
}
