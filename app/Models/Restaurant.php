<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Restaurant extends Model
{
    use HasFactory, EnterpriseScopeTrait, HasTranslations, TranslatesAutomatically;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'enterprise_id',
        'name',
        'type',
        'description',
        'image',
        'location',
        'capacity',
        'status',
        'opening_hours',
        'phone',
        'email',
        'has_terrace',
        'has_wifi',
        'has_live_music',
        'accepts_reservations',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'has_terrace' => 'boolean',
        'has_wifi' => 'boolean',
        'has_live_music' => 'boolean',
        'accepts_reservations' => 'boolean',
        'capacity' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
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
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'cafe' => 'Café',
            'pool_bar' => 'Bar Piscine',
            default => ucfirst($this->type),
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'open' => 'Ouvert',
            'closed' => 'Fermé',
            'coming_soon' => 'Bientôt disponible',
            default => ucfirst($this->status),
        };
    }

    public function getIsOpenNowAttribute()
    {
        if ($this->status !== 'open' || !$this->opening_hours) {
            return false;
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($this->opening_hours[$dayOfWeek])) {
            return false;
        }

        $hours = $this->opening_hours[$dayOfWeek];
        if (!isset($hours['open']) || !isset($hours['close'])) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    public function getTodayHoursAttribute()
    {
        if (!$this->opening_hours) {
            return null;
        }

        $dayOfWeek = strtolower(now()->format('l'));
        
        if (!isset($this->opening_hours[$dayOfWeek])) {
            return 'Fermé';
        }

        $hours = $this->opening_hours[$dayOfWeek];
        if (!isset($hours['open']) || !isset($hours['close'])) {
            return 'Fermé';
        }

        return $hours['open'] . ' - ' . $hours['close'];
    }
}
