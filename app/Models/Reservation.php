<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'user_id',
        'guest_id',
        'room_id',
        'reservation_number',
        'check_in',
        'check_out',
        'guests_count',
        'status',
        'total_price',
        'special_requests',
        'notes',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_price' => 'decimal:2',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    /**
     * Boot method pour générer le numéro de réservation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (!$reservation->reservation_number) {
                $reservation->reservation_number = 'RES-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relation avec l'entreprise
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Relation avec l'utilisateur (optionnel, pour résa créées par le staff)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le client (invité) pour résa tablette / code
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Relation avec la chambre
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Règlements / factures (Wave, Orange Money, Espèce, Carte bancaire)
     */
    public function settlements()
    {
        return $this->hasMany(ReservationSettlement::class);
    }

    /**
     * Toutes les commandes « note de chambre » pour ce séjour (pour facture / reçu)
     */
    public function roomBillOrders()
    {
        return Order::withoutGlobalScope('enterprise')
            ->where('payment_method', 'room_bill')
            ->where('guest_id', $this->guest_id)
            ->where('room_id', $this->room_id)
            ->whereBetween('created_at', [$this->check_in, $this->check_out]);
    }

    /**
     * Commandes « note de chambre » non encore réglées pour ce séjour
     */
    public function roomBillOrdersUnsettled()
    {
        return $this->roomBillOrders()->whereNull('settled_at');
    }

    /**
     * Scope pour les réservations en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les réservations confirmées
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope pour les réservations actives (checked_in)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Scope pour les réservations terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'checked_out');
    }

    /**
     * Scope pour les réservations d'aujourd'hui (check-in)
     */
    public function scopeCheckInToday($query)
    {
        return $query->whereDate('check_in', today());
    }

    /**
     * Scope pour les réservations d'aujourd'hui (check-out)
     */
    public function scopeCheckOutToday($query)
    {
        return $query->whereDate('check_out', today());
    }

    /**
     * Calculer le nombre de nuits
     */
    public function getNightsCountAttribute()
    {
        return $this->check_in->diffInDays($this->check_out, false);
    }

    /**
     * Vérifie si le séjour est valide (now entre check_in et check_out)
     */
    public function isStayValid(): bool
    {
        $now = now();
        return $now->between($this->check_in, $this->check_out);
    }

    /**
     * Obtenir le nom du statut
     */
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'checked_in' => 'Check-in effectué',
            'checked_out' => 'Check-out effectué',
            'cancelled' => 'Annulée',
            default => ucfirst($this->status),
        };
    }

    /**
     * Obtenir la couleur du statut (pour UI)
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'success',
            'checked_in' => 'blue-light',
            'checked_out' => 'gray',
            'cancelled' => 'error',
            default => 'gray',
        };
    }
}
