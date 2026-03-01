<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'client_code',
        'role',
        'enterprise_id',
        'department',
        'managed_sections',
        'room_number',
        'room_id',
        'must_change_password',
        'fcm_token',
        'fcm_token_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'fcm_token_updated_at' => 'datetime',
            'managed_sections' => 'array',
        ];
    }

    /**
     * Relation avec l'entreprise (hôtel)
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Chambre liée (pour les accès tablette, role=guest)
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' && $this->enterprise_id === null;
    }

    /**
     * Vérifier si l'utilisateur est admin d'hôtel
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Vérifier si l'utilisateur est guest
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Scope pour les utilisateurs d'une entreprise spécifique
     */
    public function scopeOfEnterprise($query, $enterpriseId)
    {
        return $query->where('enterprise_id', $enterpriseId);
    }

    /**
     * Scope pour les super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', 'super_admin')->whereNull('enterprise_id');
    }

    /**
     * Scope pour les admins d'hôtel
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope pour le staff
     */
    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    /**
     * Scope pour les guests
     */
    public function scopeGuests($query)
    {
        return $query->where('role', 'guest');
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class);
    }
}

