<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class HotelMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message_type',
        'content',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(HotelConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
