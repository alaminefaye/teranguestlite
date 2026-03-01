<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'user_id',
        'guest_id',
        'room_id',
        'order_number',
        'type',
        'status',
        'subtotal',
        'tax',
        'delivery_fee',
        'total',
        'special_instructions',
        'payment_method',
        'delivery_notes',
        'confirmed_at',
        'prepared_at',
        'ready_at',
        'preparing_at',
        'delivering_at',
        'delivered_at',
        'settled_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'delivered_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relations
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeDelivering($query)
    {
        return $query->where('status', 'delivering');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'preparing' => 'En préparation',
            'ready' => 'Prête',
            'delivering' => 'En livraison',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'preparing' => 'blue-light',
            'ready' => 'brand',
            'delivering' => 'blue-light',
            'delivered' => 'success',
            'cancelled' => 'error',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function getTypeNameAttribute()
    {
        $types = [
            'room_service' => 'Room Service',
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'spa' => 'Spa',
            'laundry' => 'Blanchisserie',
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0, ',', ' ') . ' FCFA';
    }

    public function getPaymentMethodNameAttribute(): ?string
    {
        $methods = [
            'cash' => 'Espèce',
            'room_bill' => 'Note de chambre',
            'wave' => 'Wave',
            'orange_money' => 'Orange Money',
        ];
        return $this->payment_method ? ($methods[$this->payment_method] ?? $this->payment_method) : null;
    }
}
