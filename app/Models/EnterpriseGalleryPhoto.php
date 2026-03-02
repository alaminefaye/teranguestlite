<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class EnterpriseGalleryPhoto extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'enterprise_gallery_album_id',
        'path',
        'title',
        'description',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function album()
    {
        return $this->belongsTo(EnterpriseGalleryAlbum::class, 'enterprise_gallery_album_id');
    }

    public function getUrlAttribute(): string
    {
        return $this->path ? asset('storage/' . $this->path) : '';
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
