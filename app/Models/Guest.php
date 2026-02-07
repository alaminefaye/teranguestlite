<?php

namespace App\Models;

use App\Models\Scopes\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'nationality',
        'address',
        'city',
        'country',
        'id_document_type',
        'id_document_number',
        'id_document_place_of_issue',
        'id_document_issued_at',
        'id_document_photo',
        'access_code',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'id_document_issued_at' => 'date',
    ];

    /**
     * Génère un code à 6 chiffres unique pour l'entreprise
     */
    public static function generateAccessCode(int $enterpriseId): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (static::where('enterprise_id', $enterpriseId)->where('access_code', $code)->exists());

        return $code;
    }

    /**
     * Régénère le code (appelé par admin/gérant depuis le dashboard)
     */
    public function regenerateAccessCode(): string
    {
        $this->access_code = static::generateAccessCode($this->enterprise_id);
        $this->save();
        return $this->access_code;
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Réservation active (séjour en cours : now entre check_in et check_out)
     */
    public function activeReservationForRoom(?int $roomId = null)
    {
        $query = $this->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now());

        if ($roomId !== null) {
            $query->where('room_id', $roomId);
        }

        return $query->first();
    }
}
