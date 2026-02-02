<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class Excursion extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'name', 'type', 'description', 'image', 'price_adult', 'price_child', 'duration_hours', 'departure_time', 'included', 'not_included', 'min_participants', 'max_participants', 'status', 'is_featured', 'display_order'];
    
    protected $casts = ['price_adult' => 'decimal:2', 'price_child' => 'decimal:2', 'duration_hours' => 'integer', 'min_participants' => 'integer', 'max_participants' => 'integer', 'is_featured' => 'boolean', 'display_order' => 'integer', 'included' => 'array', 'not_included' => 'array'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function scopeAvailable($query) { return $query->where('status', 'available'); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
    public function getFormattedPriceAdultAttribute() { return number_format($this->price_adult, 0, ',', ' ') . ' FCFA'; }
}
