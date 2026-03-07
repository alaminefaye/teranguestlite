<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideItem extends Model
{
    protected $fillable = [
        'guide_category_id',
        'title',
        'description',
        'phone',
        'address',
        'latitude',
        'longitude',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function category()
    {
        return $this->belongsTo(GuideCategory::class, 'guide_category_id');
    }
}
