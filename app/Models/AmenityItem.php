<?php

namespace App\Models;

use App\Models\Traits\SafeTranslatableRead;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AmenityItem extends Model
{
    use HasTranslations, TranslatesAutomatically, SafeTranslatableRead;

    public array $translatable = ['name'];

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
