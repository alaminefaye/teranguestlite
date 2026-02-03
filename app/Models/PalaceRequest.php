<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class PalaceRequest extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'user_id', 'palace_service_id', 'room_id', 'request_number', 'description', 'requested_for', 'estimated_price', 'status', 'confirmed_at', 'cancelled_at'];
    
    protected $casts = ['estimated_price' => 'decimal:2', 'requested_for' => 'datetime', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = 'PR-' . strtoupper(uniqid());
            }
        });
    }

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function palaceService() { return $this->belongsTo(PalaceService::class); }
    public function room() { return $this->belongsTo(Room::class); }
    
    public function getFormattedEstimatedPriceAttribute() { 
        return $this->estimated_price ? number_format($this->estimated_price, 0, ',', ' ') . ' FCFA' : 'Sur demande'; 
    }
}
