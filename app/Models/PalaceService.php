<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class PalaceService extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'name', 'category', 'description', 'image', 'price', 'price_on_request', 'status', 'is_premium', 'display_order'];
    
    protected $casts = ['price' => 'decimal:2', 'price_on_request' => 'boolean', 'is_premium' => 'boolean', 'display_order' => 'integer'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function scopeAvailable($query) { return $query->where('status', 'available'); }
    public function scopePremium($query) { return $query->where('is_premium', true); }
    public function scopeOrdered($query) { return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc'); }
    public function getFormattedPriceAttribute() { return $this->price_on_request ? 'Sur demande' : number_format($this->price, 0, ',', ' ') . ' FCFA'; }
    public function getCategoryLabelAttribute() { return match($this->category ?? '') { 'concierge' => 'Conciergerie', 'transport' => 'Transport', 'vip' => 'VIP', 'butler' => 'Butler', default => ucfirst((string) $this->category) }; }
    public function getIsAvailableAttribute() { return $this->status === 'available'; }

    /** True si le service concerne un véhicule (taxi ou location avec chauffeur). */
    public function isVehicleService(): bool
    {
        $lower = strtolower($this->name ?? '');
        return str_contains($lower, 'voiture')
            || str_contains($lower, 'chauffeur')
            || str_contains($lower, 'location');
    }
}
