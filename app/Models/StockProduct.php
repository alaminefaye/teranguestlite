<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'stock_category_id',
        'name',
        'sku',
        'barcode',
        'unit',
        'quantity_current',
        'quantity_min',
        'quantity_max',
        'unit_cost',
        'location',
        'description',
        'is_active',
    ];

    protected $casts = [
        'quantity_current' => 'decimal:3',
        'quantity_min' => 'decimal:3',
        'quantity_max' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const UNITS = [
        'piece' => 'Pièce',
        'unit' => 'Unité',
        'kg' => 'Kg',
        'g' => 'Gramme',
        'liter' => 'Litre',
        'ml' => 'Millilitre',
        'box' => 'Carton / Boîte',
        'pack' => 'Pack',
        'bottle' => 'Bouteille',
        'can' => 'Canette',
        'dozen' => 'Douzaine',
        'meter' => 'Mètre',
        'roll' => 'Rouleau',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function category()
    {
        return $this->belongsTo(StockCategory::class, 'stock_category_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'stock_product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Alerte : stock actuel <= seuil minimum */
    public function scopeBelowMin($query)
    {
        return $query->whereRaw('quantity_current <= quantity_min')
            ->where('quantity_min', '>', 0);
    }

    /** En alerte (seuil min dépassé) ou stock à zéro si min = 0 */
    public function scopeInAlert($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw('quantity_current <= quantity_min')
                ->orWhere('quantity_current', '<=', 0);
        });
    }

    public function getUnitLabelAttribute(): string
    {
        return self::UNITS[$this->unit] ?? $this->unit;
    }

    public function isBelowMin(): bool
    {
        if ($this->quantity_min <= 0) {
            return $this->quantity_current <= 0;
        }
        return $this->quantity_current <= $this->quantity_min;
    }

    public function getAlertLevelAttribute(): ?string
    {
        if ($this->quantity_current <= 0) {
            return 'critical';
        }
        if ($this->quantity_min > 0 && $this->quantity_current <= $this->quantity_min) {
            return 'warning';
        }
        return null;
    }
}
