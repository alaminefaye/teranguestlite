<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestFcmToken extends Model
{
    protected $fillable = [
        'enterprise_id',
        'guest_id',
        'fcm_token',
        'source',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Enregistre ou met à jour le token FCM pour ce guest (évite doublons).
     */
    public static function register(int $enterpriseId, int $guestId, string $fcmToken, string $source = 'mobile'): self
    {
        $token = trim($fcmToken);
        if ($token === '') {
            throw new \InvalidArgumentException('FCM token cannot be empty');
        }

        $record = self::firstOrNew([
            'guest_id' => $guestId,
            'fcm_token' => $token,
        ]);

        $record->enterprise_id = $enterpriseId;
        $record->source = in_array($source, ['mobile', 'tablet'], true) ? $source : 'mobile';
        $record->last_used_at = now();
        $record->save();

        return $record;
    }
}
