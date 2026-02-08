<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'vehicle_type',
        'number_of_seats',
        'image',
        'display_order',
        'is_available',
    ];

    protected $casts = [
        'number_of_seats' => 'integer',
        'display_order' => 'integer',
        'is_available' => 'boolean',
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
}
