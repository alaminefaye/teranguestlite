<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'cover_photo',
        'city',
        'country',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Relation avec les utilisateurs (admins, staff, guests)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec les chambres
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Relation avec les clients (invités) - par entreprise
     */
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * Relation avec les réservations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Relations modules métier
     */
    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function spaServices()
    {
        return $this->hasMany(SpaService::class);
    }

    public function laundryServices()
    {
        return $this->hasMany(LaundryService::class);
    }

    public function palaceServices()
    {
        return $this->hasMany(PalaceService::class);
    }

    public function excursions()
    {
        return $this->hasMany(Excursion::class);
    }

    /**
     * Scope pour les entreprises actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accesseur pour le logo complet
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : asset('images/logo/logo.svg');
    }
}
