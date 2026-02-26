<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class AmenityCategory extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'name', 'display_order', 'is_active'];

    protected $casts = ['display_order' => 'integer', 'is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function items()
    {
        return $this->hasMany(AmenityItem::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }
}
