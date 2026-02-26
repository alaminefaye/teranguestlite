<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class LaundryService extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'name', 'category', 'description', 'price', 'turnaround_hours', 'status', 'display_order', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'turnaround_hours' => 'integer', 'display_order' => 'integer', 'is_active' => 'boolean'];

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function scopeAvailable($query) { return $query->where('status', 'available'); }
    public function scopeOrdered($query) { return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc'); }
    public function getFormattedPriceAttribute() { return number_format($this->price, 0, ',', ' ') . ' FCFA'; }
    public function getCategoryLabelAttribute() { return match($this->category ?? '') { 'washing' => 'Lavage', 'ironing' => 'Repassage', 'dry_cleaning' => 'Nettoyage à sec', 'express' => 'Express', default => ucfirst((string) $this->category) }; }
    public function getIsAvailableAttribute() { return $this->status === 'available'; }
}
