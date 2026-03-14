<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Vehicle extends Model
{
    use EnterpriseScopeTrait, HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'enterprise_id',
        'name',
        'vehicle_type',
        'number_of_seats',
        'image',
        'display_order',
        'is_available',
        'price_per_day',
        'price_half_day',
    ];

    protected $casts = [
        'number_of_seats' => 'integer',
        'display_order' => 'integer',
        'is_available' => 'boolean',
        'price_per_day' => 'decimal:2',
        'price_half_day' => 'decimal:2',
    ];

    public const TYPES = [
        'berline' => 'Berline',
        'suv' => 'SUV',
        'minibus' => 'Minibus',
        'van' => 'Van',
        'other' => 'Autre',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /** Alias pour cohérence avec les autres modules (masquer = is_available false). */
    public function scopeActive($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->vehicle_type] ?? ucfirst($this->vehicle_type);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return Storage::disk('public')->url($this->image);
    }

    /** Prix journée formaté ou "Sur demande" */
    public function getFormattedPricePerDayAttribute(): string
    {
        return $this->price_per_day !== null
            ? number_format((float) $this->price_per_day, 0, '', ' ') . ' FCFA'
            : 'Sur demande';
    }

    /** Prix demi-journée formaté ou "Sur demande" */
    public function getFormattedPriceHalfDayAttribute(): string
    {
        return $this->price_half_day !== null
            ? number_format((float) $this->price_half_day, 0, '', ' ') . ' FCFA'
            : 'Sur demande';
    }

    /**
     * Calcule le prix estimé pour une location (demi-journée ou jour(s)).
     * Règle : durée <= 5 h et pas de jours → demi-journée ; sinon prix/jour × nombre de jours (min 1).
     * @return float|null Prix en FCFA ou null si sur demande
     */
    public function computePriceForRental(?int $rentalDays, ?int $rentalDurationHours): ?float
    {
        $useHalfDay = $rentalDurationHours !== null && $rentalDurationHours <= 5
            && ($rentalDays === null || $rentalDays < 1);
        if ($useHalfDay && $this->price_half_day !== null) {
            return (float) $this->price_half_day;
        }
        if ($this->price_per_day === null) {
            return null;
        }
        $days = $rentalDays !== null && $rentalDays >= 1 ? $rentalDays : 1;
        return (float) $this->price_per_day * $days;
    }
}
