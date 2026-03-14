<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\SafeTranslatableRead;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Excursion extends Model
{
    use EnterpriseScopeTrait, HasTranslations, SafeTranslatableRead;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['enterprise_id', 'name', 'type', 'description', 'image', 'price_adult', 'price_child', 'children_age_range', 'duration_hours', 'departure_time', 'schedule_description', 'included', 'not_included', 'min_participants', 'max_participants', 'status', 'is_featured', 'display_order', 'is_active'];

    protected $casts = ['price_adult' => 'decimal:2', 'price_child' => 'decimal:2', 'duration_hours' => 'integer', 'min_participants' => 'integer', 'max_participants' => 'integer', 'is_featured' => 'boolean', 'display_order' => 'integer', 'is_active' => 'boolean', 'included' => 'array', 'not_included' => 'array'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }
    public function getFormattedPriceAdultAttribute()
    {
        return number_format($this->price_adult, 0, ',', ' ') . ' FCFA';
    }
    public function getFormattedPriceChildAttribute()
    {
        return $this->price_child ? number_format($this->price_child, 0, ',', ' ') . ' FCFA' : '';
    }
    public function getTypeLabelAttribute()
    {
        return match ($this->type ?? '') { 'cultural' => 'Culturel', 'adventure' => 'Aventure', 'relaxation' => 'Détente', 'city_tour' => 'Tour de ville', default => ucfirst((string) $this->type)};
    }
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }
}
