<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class LeisureCategory extends Model
{
    use EnterpriseScopeTrait;

    /** Catégories principales (écran 1) */
    public const TYPE_SPORT = 'sport';
    public const TYPE_LOISIRS = 'loisirs';

    /** Sous-catégories (écran 2) - type = slug pour l'app */
    public const TYPE_SPA = 'spa';
    public const TYPE_GOLF_TENNIS = 'golf_tennis';
    public const TYPE_FITNESS = 'fitness';
    public const TYPE_OTHER = 'other';

    protected $fillable = ['enterprise_id', 'parent_id', 'name', 'description', 'type', 'display_order'];

    protected $casts = ['display_order' => 'integer'];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function parent()
    {
        return $this->belongsTo(LeisureCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LeisureCategory::class, 'parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SPORT => 'Sport',
            self::TYPE_LOISIRS => 'Loisirs',
            self::TYPE_SPA => 'Spa & Wellness',
            self::TYPE_GOLF_TENNIS => 'Golf & Tennis',
            self::TYPE_FITNESS => 'Sport & Fitness',
            self::TYPE_OTHER => 'Autre',
            default => ucfirst(str_replace('_', ' ', $this->type ?? '')),
        };
    }

    public function isMainCategory(): bool
    {
        return $this->parent_id === null;
    }
}
