<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideCategory extends Model
{
    use HasTranslations, TranslatesAutomatically, EnterpriseScopeTrait;

    public array $translatable = ['name'];

    protected $fillable = [
        'enterprise_id',
        'name',
        'category_type',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(GuideItem::class)->orderBy('order')->orderBy('id');
    }
}
