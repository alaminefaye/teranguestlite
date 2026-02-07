<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tablet extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'room_id',
        'name',
        'device_identifier',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Relation avec la chambre
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Libellé d'affichage
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ('Chambre ' . $this->room?->room_number);
    }
}
