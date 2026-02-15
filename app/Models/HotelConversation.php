<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HotelMessage;

class HotelConversation extends Model
{
    protected $fillable = [
        'enterprise_id',
        'user_id',
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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function messages()
    {
        return $this->hasMany(HotelMessage::class, 'conversation_id');
    }
}
