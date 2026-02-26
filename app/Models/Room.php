<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'room_number',
        'floor',
        'type',
        'status',
        'price_per_night',
        'capacity',
        'description',
        'amenities',
        'image',
        'wifi_network',
        'wifi_password',
    ];

    protected $casts = [
        'amenities' => 'array',
        'price_per_night' => 'decimal:2',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Relation avec les réservations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Tablette associée à cette chambre (une chambre = une tablette)
     */
    public function tablet()
    {
        return $this->hasOne(Tablet::class);
    }

    /**
     * Accès tablette (compte User role=guest) lié à cette chambre
     */
    public function tabletAccessUser()
    {
        return $this->hasOne(User::class, 'room_id')->where('role', 'guest');
    }

    /**
     * Scope pour les chambres disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope pour les chambres occupées
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope pour les chambres en maintenance
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Vérifier si la chambre est disponible pour une période.
     * On ne bloque que les réservations actives : pending, confirmed, checked_in.
     * Les réservations annulées ou déjà terminées (checked_out) ne bloquent pas.
     */
    public function isAvailableForPeriod($checkIn, $checkOut)
    {
        if ($this->status !== 'available') {
            return false;
        }

        $activeStatuses = ['pending', 'confirmed', 'checked_in'];

        return !$this->reservations()
            ->whereIn('status', $activeStatuses)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();
    }

    /**
     * Obtenir le nom complet du type
     */
    public function getTypeNameAttribute()
    {
        return match($this->type) {
            'single' => 'Chambre Simple',
            'double' => 'Chambre Double',
            'suite' => 'Suite',
            'deluxe' => 'Deluxe',
            'presidential' => 'Suite Présidentielle',
            default => ucfirst($this->type),
        };
    }

    /**
     * Obtenir le nom du statut
     */
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'available' => 'Disponible',
            'occupied' => 'Occupée',
            'maintenance' => 'Maintenance',
            'reserved' => 'Réservée',
            default => ucfirst($this->status),
        };
    }
}
