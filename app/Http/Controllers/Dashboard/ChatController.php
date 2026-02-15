<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HotelConversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $enterpriseId = $user->enterprise_id;

        $conversations = HotelConversation::where('enterprise_id', $enterpriseId)
            ->with(['user', 'room'])
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->whereNull('read_at')
                        ->where('sender_type', 'guest');
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('pages.dashboard.hotel-chat.index', [
            'title' => 'Messages invités',
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, HotelConversation $conversation): View
    {
        $user = $request->user();

        if ($conversation->enterprise_id !== $user->enterprise_id) {
            abort(403);
        }

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_type', 'guest')
            ->update(['read_at' => now()]);

        $conversation->load(['user', 'room']);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return view('pages.dashboard.hotel-chat.show', [
            'title' => 'Conversation invité',
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }
}

