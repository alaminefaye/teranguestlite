@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Restaurants & Bars</h1>
        <a href="{{ route('dashboard.restaurants.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nouveau restaurant
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>

    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-sm text-success-600 dark:text-success-400">Affichés</p>
        <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ $stats['active'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Ouverts</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['open'] }}</p>
    </div>

    <div class="rounded-lg border border-error-200 bg-error-50 p-4 dark:border-error-800 dark:bg-error-900/20">
        <p class="text-sm text-error-600 dark:text-error-400">Fermés</p>
        <p class="text-2xl font-semibold text-error-700 dark:text-error-300">{{ $stats['closed'] }}</p>
    </div>

    <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
        <p class="text-sm text-brand-600 dark:text-brand-400">Restaurants</p>
        <p class="text-2xl font-semibold text-brand-700 dark:text-brand-300">{{ $stats['restaurants'] }}</p>
    </div>

    <div class="rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-900/20">
        <p class="text-sm text-primary-600 dark:text-primary-400">Bars</p>
        <p class="text-2xl font-semibold text-primary-700 dark:text-primary-300">{{ $stats['bars'] }}</p>
    </div>
</div>

<!-- Filtres avancés -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.restaurants.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
            <select name="type" id="type" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous les types</option>
                <option value="restaurant" {{ request('type') === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                <option value="bar" {{ request('type') === 'bar' ? 'selected' : '' }}>Bar</option>
                <option value="cafe" {{ request('type') === 'cafe' ? 'selected' : '' }}>Café</option>
                <option value="pool_bar" {{ request('type') === 'pool_bar' ? 'selected' : '' }}>Bar Piscine</option>
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
            <select name="status" id="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous les statuts</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                <option value="coming_soon" {{ request('status') === 'coming_soon' ? 'selected' : '' }}>Bientôt</option>
            </select>
        </div>

        <div>
            <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Affichage</label>
            <select name="is_active" id="is_active" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Affichés</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Masqués</option>
            </select>
        </div>

        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nom..."
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'type', 'status', 'is_active']))
                <a href="{{ route('dashboard.restaurants.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Liste -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($restaurants as $restaurant)
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden {{ !$restaurant->is_active ? 'opacity-70' : '' }}">
            <!-- Image -->
            @if($restaurant->image)
                <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700">
                    <img src="{{ asset('storage/' . $restaurant->image) }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="aspect-video bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900 dark:to-brand-800 flex items-center justify-center">
                    <svg class="w-16 h-16 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            @endif

            <!-- Content -->
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $restaurant->name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $restaurant->type_label }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(!$restaurant->is_active)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10">Masqué</span>
                        @endif
                        @php
                            $statusColors = [
                                'open' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                'closed' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                'coming_soon' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$restaurant->status] ?? 'bg-gray-50 text-gray-600' }}">
                            {{ $restaurant->status_label }}
                        </span>
                    </div>
                </div>

                @if($restaurant->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $restaurant->description }}</p>
                @endif

                <!-- Info -->
                <div class="space-y-2 mb-3 text-sm text-gray-600 dark:text-gray-400">
                    @if($restaurant->location)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $restaurant->location }}</span>
                        </div>
                    @endif

                    @if($restaurant->capacity)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>{{ $restaurant->capacity }} places</span>
                        </div>
                    @endif

                    @if($restaurant->today_hours)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $restaurant->today_hours }}</span>
                        </div>
                    @endif
                </div>

                <!-- Features -->
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($restaurant->has_terrace)
                        <span class="inline-flex items-center bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded">
                            🌿 Terrasse
                        </span>
                    @endif
                    @if($restaurant->has_wifi)
                        <span class="inline-flex items-center bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded">
                            📶 WiFi
                        </span>
                    @endif
                    @if($restaurant->has_live_music)
                        <span class="inline-flex items-center bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded">
                            🎵 Live Music
                        </span>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2 flex-wrap">
                    <form action="{{ route('dashboard.restaurants.toggle', $restaurant) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs border rounded {{ $restaurant->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }}">
                            {{ $restaurant->is_active ? 'Masquer' : 'Afficher' }}
                        </button>
                    </form>
                    <x-action-buttons
                        :showRoute="route('dashboard.restaurants.show', $restaurant)"
                        :editRoute="route('dashboard.restaurants.edit', $restaurant)"
                        :canDelete="false"
                    />
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">Aucun restaurant trouvé</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($restaurants->hasPages())
    <div class="mt-6">
        {{ $restaurants->links() }}
    </div>
@endif
@endsection
