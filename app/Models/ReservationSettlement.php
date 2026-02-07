<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationSettlement extends Model
{
    protected $fillable = [
        'reservation_id',
        'amount',
        'payment_method',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public static function paymentMethodLabels(): array
    {
        return [
            'wave' => 'Wave',
            'orange_money' => 'Orange Money',
            'cash' => 'Espèce',
            'card' => 'Carte bancaire',
        ];
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return self::paymentMethodLabels()[$this->payment_method] ?? $this->payment_method;
    }
}
