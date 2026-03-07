<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideCategory extends Model
{
    protected $fillable = [
        'name',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(GuideItem::class);
    }
}
