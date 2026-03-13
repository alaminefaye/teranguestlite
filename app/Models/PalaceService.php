<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PalaceService extends Model
{
    use EnterpriseScopeTrait, HasTranslations, TranslatesAutomatically;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['enterprise_id', 'name', 'category', 'description', 'image', 'price', 'price_on_request', 'status', 'is_premium', 'display_order', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'price_on_request' => 'boolean', 'is_premium' => 'boolean', 'display_order' => 'integer', 'is_active' => 'boolean'];

    public function scopeActive($query) { return $query->where('is_active', true); }

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

    /** True si le service est « Visites guidées » (utilisé par l’app mobile). */
    public function isGuidedToursService(): bool
    {
        $lower = strtolower($this->name ?? '');
        return str_contains($lower, 'visites guidées') || str_contains($lower, 'visite guidée');
    }
}
