<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'description',
        'icon',
        'display_order',
        'type',
        'status',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Relations
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'room_service' => 'Room Service',
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'spa' => 'Spa',
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getStatusNameAttribute()
    {
        $statuses = [
            'active' => 'Actif',
            'inactive' => 'Inactif',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
