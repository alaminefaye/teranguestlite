@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard.hotel-chat.index') }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">
                Conversation invité
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $conversation->user?->name ?? 'Client' }}
                @if($conversation->room?->room_number || $conversation->user?->room_number)
                    · Chambre {{ $conversation->room?->room_number ?? $conversation->user?->room_number }}
                @endif
            </p>
        </div>
    </div>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-4 flex items-center justify-between">
        <span class="text-xs text-gray-500 dark:text-gray-400">
            Dernier message le
            @if($conversation->last_message_at)
                {{ $conversation->last_message_at->format('d/m/Y H:i') }}
            @else
                N/A
            @endif
        </span>
    </div>

    <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
        @forelse($messages as $message)
            @php
                $isGuest = $message->sender_type === 'guest';
                $bubbleClasses = $isGuest
                    ? 'bg-brand-50 text-gray-800 dark:bg-brand-900/40 dark:text-white/90'
                    : 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900';
            @endphp
            <div class="flex {{ $isGuest ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-xl rounded-2xl px-4 py-2 text-sm {{ $bubbleClasses }}">
                    @if($message->content)
                        <div>{{ $message->content }}</div>
                    @endif
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $message->created_at?->format('d/m H:i') }}
                        @if(!$isGuest && $message->read_at)
                            · Vu
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Aucun message dans cette conversation.
            </div>
        @endforelse
    </div>
</div>
@endsection

