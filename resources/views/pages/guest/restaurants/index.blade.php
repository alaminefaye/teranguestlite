@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Restaurants & Bars</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Réservez votre table</p>
</div>

<div class="grid grid-cols-1 gap-4">
    @forelse($restaurants as $restaurant)
        <a href="{{ route('guest.restaurants.show', $restaurant) }}" class="tablet-card rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex gap-4">
                @if($restaurant->image)
                    <div class="w-24 h-24 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                        <img src="{{ asset('storage/' . $restaurant->image) }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-24 h-24 rounded-lg bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900 dark:to-brand-800 flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                @endif
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $restaurant->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $restaurant->type_label }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $restaurant->status === 'open' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400' }}">
                            {{ $restaurant->status_label }}
                        </span>
                    </div>
                    @if($restaurant->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">{{ $restaurant->description }}</p>
                    @endif
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($restaurant->location)
                            <span>📍 {{ $restaurant->location }}</span>
                        @endif
                        @if($restaurant->capacity)
                            <span>👥 {{ $restaurant->capacity }} places</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400">Aucun restaurant disponible</p>
        </div>
    @endforelse
</div>
@endsection
