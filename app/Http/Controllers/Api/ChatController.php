<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotelConversation;
use App\Models\HotelMessage;
use App\Models\Room;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $conversation->load(['user', 'messages.sender', 'messages.replyTo.sender']);

        $guestName = $conversation->user?->name ?: 'Client chambre';

        $messages = $conversation->messages()
            ->with('replyTo.sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (HotelMessage $m) => $this->formatMessageForApi($m, $guestName))
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
                // Images + audios autorisés, basés sur les extensions
                'mimes:jpeg,png,webp,heic,mp3,mp4,m4a,aac,wav,ogg',
            ];
        }

        $rules['reply_to_id'] = ['nullable', 'integer', 'exists:hotel_messages,id'];
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
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        $this->notifyStaffNewMessage($conversation, $message);

        $message->load('replyTo.sender');
        $guestName = $conversation->user?->name ?: 'Client chambre';

        return response()->json([
            'success' => true,
            'data' => $this->formatMessageForApi($message, $guestName),
        ], 201);
    }

    /**
     * Notifier uniquement le staff (jamais le client expéditeur).
     * Appelé quand un GUEST envoie un message → FCM envoyé aux seuls admin/staff de l'établissement.
     */
    /**
     * Formater un message pour l’API (reply_to, deleted).
     */
    protected function formatMessageForApi(HotelMessage $message, string $guestName): array
    {
        $senderName = null;
        if ($message->sender_type === 'guest') {
            $senderName = $guestName;
        } elseif ($message->sender_type === 'staff') {
            $senderName = $message->sender?->name ?: 'Staff hôtel';
        }

        $replyToArray = null;
        if ($message->reply_to_id && $message->relationLoaded('replyTo') && $message->replyTo) {
            $reply = $message->replyTo;
            $replySenderName = $reply->sender_type === 'guest' ? $guestName : ($reply->sender?->name ?: 'Staff hôtel');
            $replyToArray = [
                'id' => $reply->id,
                'sender_name' => $replySenderName,
                'content' => $reply->deleted_at ? null : $reply->content,
                'deleted' => (bool) $reply->deleted_at,
            ];
        }

        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $senderName,
            'message_type' => $message->message_type,
            'content' => $message->deleted_at ? null : $message->content,
            'metadata' => $message->metadata,
            'created_at' => $message->created_at?->toISOString(),
            'deleted_at' => $message->deleted_at?->toISOString(),
            'reply_to_id' => $message->reply_to_id,
            'reply_to' => $replyToArray,
        ];
    }

    protected function notifyStaffNewMessage(HotelConversation $conversation, HotelMessage $message): void
    {
        try {
            $guestName = $conversation->user?->name ?: 'Client chambre';
            $roomLabel = null;

            if ($conversation->room?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->room->room_number;
            } elseif ($conversation->user?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->user->room_number;
            }

            $preview = $this->buildMessagePreview($message);

            $service = app(FirebaseNotificationService::class);
            $service->sendToStaff(
                $conversation->enterprise_id,
                'Nouveau message client',
                $preview,
                [
                    'type' => 'chat_message',
                    'conversation_id' => (string) $conversation->id,
                    'sender_type' => $message->sender_type,
                    'guest_name' => $guestName,
                    'room_label' => $roomLabel ?? '',
                    'msg_type' => $message->message_type,
                    'message_preview' => $preview,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Chat notification error (staff): ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
            ]);
        }
    }

    /**
     * Notifier uniquement le client de la chambre (jamais le staff expéditeur).
     * Appelé quand un STAFF envoie un message → FCM envoyé au seul user tablette de la chambre.
     */
    protected function notifyGuestNewMessage(HotelConversation $conversation, HotelMessage $message): void
    {
        try {
            $conversation->loadMissing(['user', 'room']);

            $roomId = $conversation->room_id ?? $conversation->user?->room_id;

            if (!$roomId && $conversation->user?->room_number) {
                $room = Room::withoutGlobalScope('enterprise')
                    ->where('enterprise_id', $conversation->enterprise_id)
                    ->where('room_number', $conversation->user->room_number)
                    ->first();
                $roomId = $room?->id;
            }

            if (!$roomId) {
                Log::warning('Chat: cannot notify guest — no room_id on conversation or guest user', [
                    'conversation_id' => $conversation->id,
                    'user_id' => $conversation->user_id,
                ]);
                return;
            }

            if (!$conversation->room_id) {
                $conversation->update(['room_id' => $roomId]);
            }

            $guestName = $conversation->user?->name ?: 'Client chambre';
            $roomLabel = null;

            if ($conversation->room?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->room->room_number;
            } elseif ($conversation->user?->room_number) {
                $roomLabel = 'Chambre ' . $conversation->user->room_number;
            }

            $preview = $this->buildMessagePreview($message);

            $payload = [
                'type' => 'chat_message',
                'conversation_id' => (string) $conversation->id,
                'sender_type' => $message->sender_type,
                'guest_name' => $guestName,
                'room_label' => $roomLabel ?? '',
                'msg_type' => $message->message_type,
                'message_preview' => $preview,
            ];

            $service = app(FirebaseNotificationService::class);
            $sent = false;

            // Priorité : envoyer au client de la conversation (celui qui a ouvert le chat)
            if ($conversation->user) {
                $tokenCount = $conversation->user->fcmTokens()->count();
                Log::info('Chat: notifying guest (conversation user)', [
                    'conversation_id' => $conversation->id,
                    'guest_user_id' => $conversation->user->id,
                    'fcm_tokens_count' => $tokenCount,
                ]);
                $sent = $service->sendToUser(
                    $conversation->user,
                    'Nouveau message du staff',
                    $preview,
                    $payload
                );
                if ($sent) {
                    Log::info('Chat: guest notified (conversation user)', [
                        'conversation_id' => $conversation->id,
                        'user_id' => $conversation->user->id,
                    ]);
                }
            }

            // Sinon essayer via la chambre (user lié à room_id / room_number)
            if (!$sent) {
                $sent = $service->sendToClientOfRoom(
                    $roomId,
                    'Nouveau message du staff',
                    $preview,
                    $payload
                );
                if ($sent) {
                    Log::info('Chat: guest notified via room (fallback)', [
                        'conversation_id' => $conversation->id,
                        'room_id' => $roomId,
                    ]);
                }
            }

            if (!$sent) {
                Log::warning('Chat: could not notify guest (no FCM token for conversation user or room)', [
                    'conversation_id' => $conversation->id,
                    'room_id' => $roomId,
                    'user_id' => $conversation->user_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Chat notification error (guest): ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
            ]);
        }
    }

    protected function buildMessagePreview(HotelMessage $message): string
    {
        if ($message->message_type === 'text') {
            $raw = (string) ($message->content ?? '');
            $trimmed = trim($raw);

            if ($trimmed === '') {
                return 'Nouveau message';
            }

            if (strlen($trimmed) > 80) {
                return substr($trimmed, 0, 80) . '…';
            }

            return $trimmed;
        }

        if ($message->message_type === 'image') {
            return 'Photo envoyée';
        }

        if ($message->message_type === 'audio') {
            return 'Note vocale envoyée';
        }

        return 'Nouveau message';
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
            ->paginate((int) $request->input('per_page', 20));

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

            $preview = null;
            if ($lastMessage) {
                $preview = $lastMessage->deleted_at ? null : $lastMessage->content;
            }

            return [
                'id' => $conversation->id,
                'guest_name' => $guestName,
                'room_label' => $roomLabel,
                'status' => $conversation->status,
                'last_message_at' => $conversation->last_message_at?->toISOString(),
                'last_message_preview' => $preview,
                'unread_count' => (int) ($conversation->unread_count ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $data,
                'meta' => [
                    'current_page' => $conversations->currentPage(),
                    'from' => $conversations->firstItem(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'to' => $conversations->lastItem(),
                    'total' => $conversations->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Supprimer une conversation (staff) — comme « supprimer le chat » sur WhatsApp.
     */
    public function staffDestroyConversation(Request $request, HotelConversation $conversation)
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

        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation supprimée',
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

        $conversation->load(['user', 'room', 'messages.sender', 'messages.replyTo.sender']);

        $guestName = $conversation->user?->name ?: 'Client chambre';

        $messages = $conversation->messages()
            ->with('replyTo.sender')
            ->orderBy('created_at')
            ->get()
            ->map(fn (HotelMessage $m) => $this->formatMessageForApi($m, $guestName))
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
            'reply_to_id' => ['nullable', 'integer', 'exists:hotel_messages,id'],
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
                // Extensions acceptées (plus fiable que mimetypes selon l’envoi client)
                'mimes:jpeg,jpg,png,webp,heic,mp3,m4a,aac,wav,ogg',
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
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        $this->notifyGuestNewMessage($conversation, $message);

        $message->load('replyTo.sender');
        $guestName = $conversation->user?->name ?: 'Client chambre';

        return response()->json([
            'success' => true,
            'data' => $this->formatMessageForApi($message, $guestName),
        ], 201);
    }

    /** Supprimer un message (client) — soft delete, uniquement ses propres messages. */
    public function destroyMessage(Request $request, HotelMessage $message)
    {
        $user = $request->user();
        $conversation = $message->conversation;
        if (!$conversation || $conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Message introuvable'], 404);
        }
        if ($message->sender_type !== 'guest' || (int) $message->sender_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez supprimer que vos messages'], 403);
        }
        $message->update(['deleted_at' => now(), 'content' => null]);
        $message->metadata = null;
        $message->save();
        return response()->json(['success' => true, 'message' => 'Message supprimé'], 200);
    }

    /** Supprimer un message (staff) — soft delete. */
    public function staffDestroyMessage(Request $request, HotelConversation $conversation, HotelMessage $message)
    {
        $user = $request->user();
        if (!method_exists($user, 'isAdmin') || !method_exists($user, 'isStaff')) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        if (!($user->isAdmin() || $user->isStaff())) {
            return response()->json(['success' => false, 'message' => 'Accès réservé au staff'], 403);
        }
        if ($message->conversation_id !== $conversation->id || $conversation->enterprise_id !== $user->enterprise_id) {
            return response()->json(['success' => false, 'message' => 'Message introuvable'], 404);
        }
        $message->update(['deleted_at' => now(), 'content' => null]);
        $message->metadata = null;
        $message->save();
        return response()->json(['success' => true, 'message' => 'Message supprimé'], 200);
    }
}
