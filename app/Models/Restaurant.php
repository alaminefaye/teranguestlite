<?php

namespace App\Models;

use Carbon\Carbon;
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
        'menu_file',
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
        if ($this->status !== 'open' || !$this->opening_hours || !is_array($this->opening_hours)) {
            return false;
        }

        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);

        $todayKey = self::weekdayKeyForCarbon($now);

        if (isset($this->opening_hours[$todayKey]) && $this->openingHoursSlotCoversNow($now, $this->opening_hours[$todayKey])) {
            return true;
        }

        $yesterdayKey = self::weekdayKeyForCarbon($now->copy()->subDay());
        if (isset($this->opening_hours[$yesterdayKey]) && $this->openingHoursOvernightFromYesterdayCoversNow($now, $this->opening_hours[$yesterdayKey])) {
            return true;
        }

        return false;
    }

    /**
     * Clés JSON du dashboard : monday … sunday (toujours en anglais).
     * Ne pas utiliser format('l') : dépend de la locale de l’application.
     */
    private static function weekdayKeyForCarbon(Carbon $dt): string
    {
        $keys = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        return $keys[(int) $dt->format('w')];
    }

    /**
     * Plage du jour : même jour calendaire, ou overnight (ex. 22:00–02:00 sur une même ligne),
     * ou fermeture à minuit affichée en 00:00 / 24:00.
     */
    private function openingHoursSlotCoversNow(Carbon $now, array $hours): bool
    {
        if (!isset($hours['open'], $hours['close'])) {
            return false;
        }

        $openStr = $this->normalizeTimeString($hours['open']);
        $closeStr = $this->normalizeTimeString($hours['close']);
        if ($openStr === null || $closeStr === null) {
            return false;
        }

        try {
            $open = $now->copy()->setTimeFromTimeString($openStr);
            $close = $now->copy()->setTimeFromTimeString($closeStr);
        } catch (\Throwable) {
            return false;
        }

        // Fermeture à minuit : 09:00 – 00:00 signifie jusqu’à la fin de la journée locale.
        if ($closeStr === '00:00' || $closeStr === '24:00') {
            return $now->greaterThanOrEqualTo($open)
                && $now->lessThanOrEqualTo($now->copy()->endOfDay());
        }

        if ($close->greaterThan($open)) {
            return $now->greaterThanOrEqualTo($open) && $now->lessThanOrEqualTo($close);
        }

        // Même ligne : ouverture le soir, fermeture le lendemain matin (ex. 22h–02h).
        return $now->greaterThanOrEqualTo($open) || $now->lessThanOrEqualTo($close);
    }

    /**
     * Hier : horaires type 22:00–02:00 ; le mercredi 01:00 est encore dans la plage du mardi soir.
     */
    private function openingHoursOvernightFromYesterdayCoversNow(Carbon $now, array $hours): bool
    {
        if (!isset($hours['open'], $hours['close'])) {
            return false;
        }

        $openStr = $this->normalizeTimeString($hours['open']);
        $closeStr = $this->normalizeTimeString($hours['close']);
        if ($openStr === null || $closeStr === null) {
            return false;
        }

        if ($closeStr === '00:00' || $closeStr === '24:00') {
            return false;
        }

        try {
            $openYesterday = $now->copy()->subDay()->setTimeFromTimeString($openStr);
            $closeToday = $now->copy()->setTimeFromTimeString($closeStr);
        } catch (\Throwable) {
            return false;
        }

        if (!$this->clockTimesIndicateOvernightSpan($openStr, $closeStr)) {
            return false;
        }

        return $now->greaterThanOrEqualTo($openYesterday)
            && $now->lessThanOrEqualTo($closeToday);
    }

    /** Ex. 22:00 → 02:00 : la fermeture est le lendemain matin. */
    private function clockTimesIndicateOvernightSpan(string $openStr, string $closeStr): bool
    {
        if ($closeStr === '00:00' || $closeStr === '24:00') {
            return false;
        }

        return strcmp($openStr, $closeStr) > 0;
    }

    private function normalizeTimeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = substr(trim((string) $value), 0, 8);

        return $s !== '' ? substr($s, 0, 5) : null;
    }

    public function getTodayHoursAttribute()
    {
        if (!$this->opening_hours || !is_array($this->opening_hours)) {
            return null;
        }

        $tz = config('app.timezone', 'UTC');
        $dayKey = self::weekdayKeyForCarbon(Carbon::now($tz));

        if (!isset($this->opening_hours[$dayKey])) {
            return 'Fermé';
        }

        $hours = $this->opening_hours[$dayKey];
        if (!isset($hours['open']) || !isset($hours['close'])) {
            return 'Fermé';
        }

        return $hours['open'] . ' - ' . $hours['close'];
    }
}
