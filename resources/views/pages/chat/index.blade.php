@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Chat en Direct</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Communiquez en temps réel avec l'équipe</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="unreadCount" class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400" style="display: none;">
                0 messages non lus
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Sidebar - Rooms et Utilisateurs -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Rooms -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Salons</h3>
                <div class="space-y-1">
                    @foreach($rooms as $roomKey => $roomName)
                        <a href="{{ route('chat.index', ['room' => $roomKey]) }}" 
                           class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors {{ $room === $roomKey ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            {{ $roomName }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Utilisateurs en ligne (pour chat privé) -->
            @if(!$recipientId)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Utilisateurs</h3>
                    <div class="space-y-1 max-h-64 overflow-y-auto">
                        @foreach($users as $user)
                            <a href="{{ route('chat.index', ['recipient_id' => $user->id]) }}" 
                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                    <span class="text-xs font-semibold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Zone de chat -->
        <div class="lg:col-span-3 flex flex-col">
            <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 flex-1 flex flex-col">
                <!-- Header du chat -->
                <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        @if($recipientId)
                            Conversation avec {{ $users->firstWhere('id', $recipientId)?->name ?? 'Utilisateur' }}
                        @else
                            {{ $rooms[$room] ?? 'Général' }}
                        @endif
                    </h3>
                </div>

                <!-- Messages -->
                <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[400px] max-h-[600px]">
                    @foreach($messages as $message)
                        <div class="flex items-start gap-3 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <span class="text-xs font-semibold">{{ strtoupper(substr($message->user->name, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1 {{ $message->user_id === auth()->id() ? 'text-right' : '' }}">
                                <div class="mb-1 flex items-center gap-2 {{ $message->user_id === auth()->id() ? 'justify-end' : '' }}">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $message->user->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $message->created_at->format('H:i') }}</span>
                                </div>
                                <div class="inline-block rounded-lg px-4 py-2 text-sm {{ $message->user_id === auth()->id() ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white/90' }}">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Formulaire d'envoi -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                    <form id="chatForm" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="room" value="{{ $room }}">
                        @if($recipientId)
                            <input type="hidden" name="recipient_id" value="{{ $recipientId }}">
                        @endif
                        <input type="text" 
                               id="messageInput"
                               name="message" 
                               placeholder="Tapez votre message..."
                               required
                               class="flex-1 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Envoyer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let lastMessageId = {{ $messages->last()?->id ?? 0 }};
let pollingInterval;

// Envoyer un message
document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    try {
        const response = await fetch('{{ route("chat.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            addMessage(data.message);
            scrollToBottom();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
});

// Ajouter un message à l'affichage
function addMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    const isOwnMessage = message.user_id === {{ auth()->id() }};
    const userName = message.user.name;
    const userInitials = userName.substring(0, 2).toUpperCase();
    const time = new Date(message.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    
    const messageHtml = `
        <div class="flex items-start gap-3 ${isOwnMessage ? 'flex-row-reverse' : ''}">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <span class="text-xs font-semibold">${userInitials}</span>
            </div>
            <div class="flex-1 ${isOwnMessage ? 'text-right' : ''}">
                <div class="mb-1 flex items-center gap-2 ${isOwnMessage ? 'justify-end' : ''}">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">${userName}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">${time}</span>
                </div>
                <div class="inline-block rounded-lg px-4 py-2 text-sm ${isOwnMessage ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white/90'}">
                    ${message.message}
                </div>
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    lastMessageId = message.id;
    scrollToBottom();
}

// Récupérer les nouveaux messages
async function fetchNewMessages() {
    try {
        const params = new URLSearchParams({
            room: '{{ $room }}',
            last_message_id: lastMessageId,
            @if($recipientId)
            recipient_id: {{ $recipientId }},
            @endif
        });
        
        const response = await fetch(`{{ route('chat.get-messages') }}?${params}`, {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(message => {
                addMessage(message);
            });
            lastMessageId = data.last_message_id;
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer le nombre de messages non lus
async function updateUnreadCount() {
    try {
        const response = await fetch('{{ route("chat.unread-count") }}', {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        const unreadCountEl = document.getElementById('unreadCount');
        
        if (data.count > 0) {
            unreadCountEl.textContent = `${data.count} message(s) non lu(s)`;
            unreadCountEl.style.display = 'inline-flex';
        } else {
            unreadCountEl.style.display = 'none';
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Scroll vers le bas
function scrollToBottom() {
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Initialiser le polling
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    
    // Polling toutes les 3 secondes
    pollingInterval = setInterval(() => {
        fetchNewMessages();
        updateUnreadCount();
    }, 3000);
    
    // Mettre à jour le compteur au chargement
    updateUnreadCount();
});

// Nettoyer l'intervalle quand on quitte la page
window.addEventListener('beforeunload', function() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>
@endsection
