<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HotelMessage;
use App\Models\Scopes\EnterpriseScopeTrait;

class HotelConversation extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'user_id',
        'guest_id',
        'room_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Client (invité) lié à la réservation active en chambre — une conversation par guest.
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function messages()
    {
        return $this->hasMany(HotelMessage::class, 'conversation_id');
    }
}
