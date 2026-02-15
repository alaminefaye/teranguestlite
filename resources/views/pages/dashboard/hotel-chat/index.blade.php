@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Messages invités</h1>
        <p class="text-gray-600 dark:text-gray-400">Discussions envoyées depuis les tablettes en chambre</p>
    </div>
</div>

@if($conversations->isEmpty())
    <div class="rounded-lg border border-gray-200 bg-white p-6 text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        Aucun message reçu pour le moment.
    </div>
@else
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="space-y-3">
            @foreach($conversations as $conversation)
                @php
                    $guestName = $conversation->user?->name ?? 'Client chambre';
                    $roomLabel = $conversation->room?->room_number
                        ? 'Chambre '.$conversation->room->room_number
                        : ($conversation->user?->room_number ? 'Chambre '.$conversation->user->room_number : null);
                    $last = $conversation->last_message_at;
                    $unread = (int) ($conversation->unread_count ?? 0);
                @endphp
                <a href="{{ route('dashboard.hotel-chat.show', $conversation) }}"
                   class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $guestName }}</span>
                        @if($roomLabel)
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $roomLabel }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if($last)
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $last->format('d/m H:i') }}</span>
                        @endif
                        @if($unread > 0)
                            <span class="inline-flex min-w-[2rem] justify-center rounded-full bg-error-500 px-2 py-1 text-xs font-semibold text-white">
                                {{ $unread > 99 ? '99+' : $unread }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $conversations->links() }}
        </div>
    </div>
@endif
@endsection

