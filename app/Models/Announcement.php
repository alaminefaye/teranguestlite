<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Announcement extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['title'];

    protected $fillable = [
        'enterprise_id',
        'poster_path',
        'video_path',
        'title',
        'display_order',
        'is_active',
        'starts_at',
        'ends_at',
        'display_duration_minutes',
        'view_count',
        'target_all_enterprises',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_all_enterprises' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'display_duration_minutes' => 'integer',
        'view_count' => 'integer',
    ];

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function targetEnterprises()
    {
        return $this->belongsToMany(Enterprise::class, 'announcement_enterprises');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    /**
     * Annonces actives dans la plage de dates.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /**
     * Annonces appartenant à une entreprise donnée (annonces propres de l'entreprise).
     */
    public function scopeForEnterprise($query, int $enterpriseId)
    {
        return $query->where('enterprise_id', $enterpriseId);
    }

    /**
     * Annonces super admin (enterprise_id null).
     */
    public function scopeSuperAdmin($query)
    {
        return $query->whereNull('enterprise_id');
    }

    /**
     * Annonces super admin éligibles pour une entreprise donnée :
     * soit target_all_enterprises = true, soit l'entreprise est dans la table pivot.
     */
    public function scopeSuperAdminForEnterprise($query, int $enterpriseId)
    {
        return $query
            ->whereNull('enterprise_id')
            ->where(function ($q) use ($enterpriseId) {
                $q->where('target_all_enterprises', true)
                    ->orWhereHas('targetEnterprises', fn($r) => $r->where('enterprise_id', $enterpriseId));
            });
    }

    /**
     * Retourne toutes les annonces éligibles (mélangées) pour une entreprise donnée.
     * = annonces super admin ciblant cette entreprise + annonces propres de l'entreprise.
     */
    public static function eligibleForEnterprise(int $enterpriseId)
    {
        return static::active()
            ->where(function ($q) use ($enterpriseId) {
                // Annonces propres de l'entreprise
                $q->where('enterprise_id', $enterpriseId)
                    // OU annonces super admin (enterprise_id null) ciblant cette entreprise
                    ->orWhere(function ($r) use ($enterpriseId) {
                    $r->whereNull('enterprise_id')
                        ->where(function ($s) use ($enterpriseId) {
                            $s->where('target_all_enterprises', true)
                                ->orWhereHas('targetEnterprises', fn($t) => $t->where('enterprise_id', $enterpriseId));
                        });
                });
            })
            ->orderBy('display_order')
            ->orderBy('id');
    }

    // ──────────────────────────────────────────
    // Accesseurs
    // ──────────────────────────────────────────

    /**
     * URL publique de l'affiche (ou null si pas d'affiche).
     */
    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_path ? asset('storage/' . $this->poster_path) : null;
    }

    /**
     * URL publique de la vidéo (ou null si pas de vidéo).
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path ? asset('storage/' . $this->video_path) : null;
    }

    /**
     * Type de contenu : 'both' | 'poster_only' | 'video_only'
     */
    public function getTypeAttribute(): string
    {
        if ($this->poster_path && $this->video_path) {
            return 'both';
        }
        if ($this->video_path) {
            return 'video_only';
        }
        return 'poster_only';
    }

    /**
     * Indique si l'annonce est une annonce super admin (enterprise_id null).
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->enterprise_id === null;
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    /**
     * Supprime les fichiers physiques (affiche + vidéo) du storage.
     */
    public function deleteFiles(): void
    {
        if ($this->poster_path) {
            Storage::disk('public')->delete($this->poster_path);
        }
        if ($this->video_path) {
            Storage::disk('public')->delete($this->video_path);
        }
    }

    /**
     * Représentation JSON pour l'API mobile.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'poster_url' => $this->poster_url,
            'video_url' => $this->video_url,
            'type' => $this->type,
            'display_order' => $this->display_order,
            'display_duration_minutes' => $this->display_duration_minutes ?? 1,
        ];
    }
}
