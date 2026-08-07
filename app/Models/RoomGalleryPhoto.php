<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomGalleryPhoto extends Model
{
    protected $table = 'room_gallery_photos';

    protected $fillable = [
        'enterprise_id',
        'type',
        'title',
        'description',
        'path',
        'original_extension',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active'     => 'boolean',
    ];

    /** Types disponibles */
    public const TYPE_CHAMBRE = 'chambre';
    public const TYPE_SUITE   = 'suite';

    public const TYPES = [
        self::TYPE_CHAMBRE => 'Chambres',
        self::TYPE_SUITE   => 'Suites',
    ];

    // Relations ---------------------------------------------------------------

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    // Scopes ------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors ---------------------------------------------------------------

    /** URL publique de l'image */
    public function getUrlAttribute(): string
    {
        return $this->path ? asset('storage/' . $this->path) : '';
    }
}
