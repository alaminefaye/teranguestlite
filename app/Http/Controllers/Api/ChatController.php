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

        $messageType = $request->input('message_type', 'text');

        $rules = [
            'message_type' => ['nullable', 'string', 'in:text,image,audio'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:3600'],
        ];

        if ($messageType === 'text' || $messageType === null) {
            $rules['content'] = ['required', 'string', 'max:2000'];
            $rules['file'] = ['nullable', 'file'];
        } else {
            $rules['content'] = ['nullable', 'string', 'max:2000'];
            $rules['file'] = [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/wav,audio/ogg',
            ];
        }

        $validated = $request->validate($rules);

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

        $type = $validated['message_type'] ?? 'text';
        $metadata = null;
        $content = $validated['content'] ?? null;

        if ($type === 'image' || $type === 'audio') {
            $uploadedFile = $request->file('file');

            if ($uploadedFile) {
                $path = $uploadedFile->store('hotel-chat', 'public');
                $url = asset('storage/' . $path);

                $metadata = [
                    'url' => $url,
                    'path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getClientMimeType(),
                    'size' => $uploadedFile->getSize(),
                ];

                if ($type === 'audio' && isset($validated['duration'])) {
                    $metadata['duration'] = (int) $validated['duration'];
                }
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => 'guest',
            'message_type' => $type,
            'content' => $content,
            'metadata' => $metadata,
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

    public function staffConversations(Request $request)
    {
        $user = $request->user();

        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

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

        $data = $conversations->map(function (HotelConversation $conversation) {
            $guestName = $conversation->user?->name ?: 'Client chambre';
            $roomLabel = null;

            if ($conversation->room?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->room->room_number;
            } elseif ($conversation->user?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->user->room_number;
            }

            $lastMessage = $conversation->messages()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();

            return [
                'id' => $conversation->id,
                'guest_name' => $guestName,
                'room_label' => $roomLabel,
                'status' => $conversation->status,
                'last_message_at' => $conversation->last_message_at?->toISOString(),
                'last_message_preview' => $lastMessage?->content,
                'unread_count' => (int) ($conversation->unread_count ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $data,
                'meta' => [
                    'current_page' => $conversations->currentPage(),
                    'total' => $conversations->total(),
                ],
            ],
        ], 200);
    }

    public function staffConversationMessages(Request $request, HotelConversation $conversation)
    {
        $user = $request->user();

        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

        if ($conversation->enterprise_id !== $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation introuvable pour cet établissement',
            ], 404);
        }

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_type', 'guest')
            ->update(['read_at' => now()]);

        $conversation->load(['user', 'room']);

        $messages = $conversation->messages()
            ->orderBy('created_at')
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

        $guestName = $conversation->user?->name ?: 'Client chambre';
        $roomLabel = null;

        if ($conversation->room?->room_number) {
            $roomLabel = 'Chambre ' . $conversation->room->room_number;
        } elseif ($conversation->user?->room_number) {
            $roomLabel = 'Chambre ' . $conversation->user->room_number;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'guest_name' => $guestName,
                'room_label' => $roomLabel,
                'messages' => $messages,
            ],
        ], 200);
    }

    public function staffReply(Request $request, HotelConversation $conversation)
    {
        $user = $request->user();

        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au staff de l’hôtel',
            ], 403);
        }

        if ($conversation->enterprise_id !== $user->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation introuvable pour cet établissement',
            ], 404);
        }

        $messageType = $request->input('message_type', 'text');

        $rules = [
            'message_type' => ['nullable', 'string', 'in:text,image,audio'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:3600'],
        ];

        if ($messageType === 'text' || $messageType === null) {
            $rules['content'] = ['required', 'string', 'max:2000'];
            $rules['file'] = ['nullable', 'file'];
        } else {
            $rules['content'] = ['nullable', 'string', 'max:2000'];
            $rules['file'] = [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/wav,audio/ogg',
            ];
        }

        $validated = $request->validate($rules);

        $type = $validated['message_type'] ?? 'text';
        $metadata = null;
        $content = $validated['content'] ?? null;

        if ($type === 'image' || $type === 'audio') {
            $uploadedFile = $request->file('file');

            if ($uploadedFile) {
                $path = $uploadedFile->store('hotel-chat', 'public');
                $url = asset('storage/' . $path);

                $metadata = [
                    'url' => $url,
                    'path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getClientMimeType(),
                    'size' => $uploadedFile->getSize(),
                ];

                if ($type === 'audio' && isset($validated['duration'])) {
                    $metadata['duration'] = (int) $validated['duration'];
                }
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => 'staff',
            'message_type' => $type,
            'content' => $content,
            'metadata' => $metadata,
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
