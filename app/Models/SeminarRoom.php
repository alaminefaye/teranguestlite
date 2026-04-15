<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use App\Models\Traits\TranslatesAutomatically;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SeminarRoom extends Model
{
    use HasFactory, EnterpriseScopeTrait, HasTranslations, TranslatesAutomatically;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'enterprise_id',
        'name',
        'description',
        'capacity',
        'equipments',
        'image',
        'contact_phone',
        'contact_email',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'equipments' => 'array',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id', 'desc');
    }
}

