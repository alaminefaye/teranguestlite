<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentPhoto extends Model
{
    protected $fillable = [
        'establishment_id',
        'path',
        'caption',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function getUrlAttribute(): string
    {
        return $this->path ? asset('storage/' . $this->path) : '';
    }
}
