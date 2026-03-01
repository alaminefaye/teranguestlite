<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'ingredients',
        'allergens',
        'preparation_time',
        'is_available',
        'is_featured',
        'display_order',
        'stock_product_id',
        'stock_quantity_per_portion',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'ingredients' => 'array',
        'allergens' => 'array',
        'preparation_time' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
        'stock_quantity_per_portion' => 'decimal:3',
    ];

    /**
     * Relations
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockProduct()
    {
        return $this->belongsTo(StockProduct::class, 'stock_product_id');
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Accessors
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function getPreparationTimeTextAttribute()
    {
        if (!$this->preparation_time) {
            return null;
        }

        return $this->preparation_time . ' min';
    }
}
