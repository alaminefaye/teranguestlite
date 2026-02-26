<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class SpaService extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'category',
        'description',
        'image',
        'price',
        'duration',
        'status',
        'is_featured',
        'benefits',
        'contraindications',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_featured' => 'boolean',
        'benefits' => 'array',
        'contraindications' => 'array',
        'display_order' => 'integer',
    ];

    /**
     * Relations
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Accessors
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'massage' => 'Massage',
            'facial' => 'Soin du visage',
            'body_treatment' => 'Soin du corps',
            'wellness' => 'Bien-être',
            default => ucfirst($this->category),
        };
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function getDurationTextAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h{$minutes}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}min";
        }
    }
}
