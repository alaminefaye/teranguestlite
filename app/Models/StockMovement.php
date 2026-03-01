<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'stock_product_id',
        'type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function product()
    {
        return $this->belongsTo(StockProduct::class, 'stock_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN => 'Entrée',
            self::TYPE_OUT => 'Sortie',
            self::TYPE_ADJUSTMENT => 'Ajustement',
            default => $this->type,
        };
    }

    /** Quantité signée : positive pour entrée, négative pour sortie */
    public function getSignedQuantityAttribute(): float
    {
        return $this->type === self::TYPE_OUT
            ? -abs((float) $this->quantity)
            : abs((float) $this->quantity);
    }
}
