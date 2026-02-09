<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class LaundryRequest extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'user_id', 'guest_id', 'room_id', 'request_number', 'items', 'total_price', 'pickup_time', 'delivery_time', 'special_instructions', 'status'];
    
    protected $casts = ['items' => 'array', 'total_price' => 'decimal:2', 'pickup_time' => 'datetime', 'delivery_time' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = 'LR-' . strtoupper(uniqid());
            }
        });
    }

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    
    public function getFormattedTotalPriceAttribute() { return number_format($this->total_price, 0, ',', ' ') . ' FCFA'; }
}
