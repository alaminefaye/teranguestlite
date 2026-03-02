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
        'gym_hours',
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

    public function galleryAlbums()
    {
        return $this->hasMany(EnterpriseGalleryAlbum::class, 'enterprise_id');
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

    /**
     * Hotel Infos & Sécurité : livret d'accueil (Wi‑Fi, plans, règlement, infos pratiques).
     * Stocké dans settings['hotel_infos'].
     */
    public function getHotelInfosAttribute(): array
    {
        $s = is_array($this->settings) ? ($this->settings['hotel_infos'] ?? []) : [];
        $mapUrl = $s['map_url'] ?? null;
        if (!$mapUrl && !empty($s['map_path'])) {
            $mapUrl = asset('storage/' . $s['map_path']);
        }
        return [
            'wifi_network' => $s['wifi_network'] ?? '',
            'wifi_password' => $s['wifi_password'] ?? '',
            'house_rules' => $s['house_rules'] ?? '',
            'map_url' => $mapUrl,
            'practical_info' => $s['practical_info'] ?? '',
        ];
    }

    /**
     * Livret d'accueil pour une chambre donnée : infos hôtel avec Wi‑Fi chambre si renseigné.
     */
    public function getHotelInfosForRoom(Room $room): array
    {
        $infos = $this->hotel_infos;
        if (trim((string) $room->wifi_network) !== '') {
            $infos['wifi_network'] = $room->wifi_network;
        }
        if (trim((string) $room->wifi_password) !== '') {
            $infos['wifi_password'] = $room->wifi_password;
        }
        return $infos;
    }

    /**
     * Galerie pour l'API (app mobile) : image d'établissement + albums actifs avec photos.
     * Uniquement les données de cette entreprise.
     */
    public function getGalleryForApi(): array
    {
        $establishmentPhotoUrl = $this->cover_photo
            ? asset('storage/' . $this->cover_photo)
            : null;

        $albums = $this->galleryAlbums()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->with(['photos' => fn ($q) => $q->orderBy('display_order')->orderBy('id')])
            ->get()
            ->map(fn ($album) => [
                'id' => $album->id,
                'name' => $album->name,
                'description' => $album->description,
                'photos' => $album->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => asset('storage/' . $p->path),
                    'title' => $p->title,
                    'description' => $p->description,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return [
            'establishment_photo_url' => $establishmentPhotoUrl,
            'albums' => $albums,
        ];
    }

    /**
     * Assistance & Urgence : médecin et urgence sécurité (activés ou non).
     * Stocké dans settings['emergency'].
     */
    public function getEmergencyAttribute(): array
    {
        $s = is_array($this->settings) ? ($this->settings['emergency'] ?? []) : [];
        return [
            'doctor_enabled' => (bool) ($s['doctor_enabled'] ?? true),
            'security_enabled' => (bool) ($s['security_enabled'] ?? true),
        ];
    }

    /**
     * Chatbot IA : URL du chatbot (null si non configuré).
     * Stocké dans settings['chatbot_url'].
     */
    public function getChatbotUrlAttribute(): ?string
    {
        $s = $this->settings;
        if (!is_array($s)) {
            return null;
        }
        $url = $s['chatbot_url'] ?? null;
        return $url && is_string($url) && trim($url) !== '' ? trim($url) : null;
    }
}
