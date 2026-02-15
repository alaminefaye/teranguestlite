<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotelConversation;
use App\Models\HotelMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversation = HotelConversation::where('user_id', $user->id)
            ->where('enterprise_id', $user->enterprise_id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => null,
                    'messages' => [],
                ],
            ]);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (HotelMessage $message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'message_type' => $message->message_type,
                    'content' => $message->content,
                    'metadata' => $message->metadata,
                    'created_at' => $message->created_at?->toISOString(),
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'messages' => $messages,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = HotelConversation::firstOrCreate(
            [
                'enterprise_id' => $user->enterprise_id,
                'user_id' => $user->id,
            ],
            [
                'room_id' => $user->room_id,
                'status' => 'open',
            ]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => 'guest',
            'message_type' => 'text',
            'content' => $validated['content'],
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_type' => $message->sender_type,
                'message_type' => $message->message_type,
                'content' => $message->content,
                'metadata' => $message->metadata,
                'created_at' => $message->created_at?->toISOString(),
            ],
        ], 201);
    }
}

