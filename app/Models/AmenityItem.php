<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmenityItem extends Model
{
    protected $fillable = ['amenity_category_id', 'name', 'display_order', 'is_active'];

    protected $casts = ['display_order' => 'integer', 'is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function amenityCategory()
    {
        return $this->belongsTo(AmenityCategory::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }
}
