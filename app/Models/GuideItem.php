<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideItem extends Model
{
    use HasTranslations, TranslatesAutomatically, EnterpriseScopeTrait;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'enterprise_id',
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
