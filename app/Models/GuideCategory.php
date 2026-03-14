<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

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
