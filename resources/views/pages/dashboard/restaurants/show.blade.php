@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ route('dashboard.restaurants.index') }}" class="hover:text-brand-500">Restaurants & Bars</a>
            <span>/</span>
            <span>{{ $restaurant->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $restaurant->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Type : {{ ucfirst(str_replace('_', ' ', $restaurant->type)) }} -
                    Ajouté le {{ $restaurant->created_at->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.restaurants.edit', $restaurant) }}"
                    class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Détails de l'établissement</h3>

                @if($restaurant->image)
                    <div class="mb-6">
                        <img src="{{ Storage::url($restaurant->image) }}" alt="{{ $restaurant->name }}"
                            class="w-full h-64 object-cover rounded-lg">
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                        <p class="text-gray-800 dark:text-white/90">{{ $restaurant->description ?: 'Aucune description' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Capacité</label>
                        <p class="text-gray-800 dark:text-white/90">
                            {{ $restaurant->capacity ? $restaurant->capacity . ' places' : 'Non précisé' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Emplacement</label>
                        <p class="text-gray-800 dark:text-white/90">{{ $restaurant->location ?: 'Non précisé' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Horaires</label>
                        @if(is_array($restaurant->opening_hours) && count($restaurant->opening_hours) > 0)
                            <ul class="text-sm text-gray-800 dark:text-white/90 space-y-1">
                                @foreach($restaurant->opening_hours as $day => $hours)
                                    <li><span class="capitalize">{{ $day }}</span> : {{ $hours['open'] ?? 'N/A' }} -
                                        {{ $hours['close'] ?? 'N/A' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-800 dark:text-white/90">Non défini</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Caractéristiques -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Caractéristiques</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center {{ $restaurant->has_terrace ? 'bg-success-100 text-success-600' : 'bg-gray-100 text-gray-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Terrasse</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center {{ $restaurant->has_wifi ? 'bg-success-100 text-success-600' : 'bg-gray-100 text-gray-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">WiFi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center {{ $restaurant->has_live_music ? 'bg-success-100 text-success-600' : 'bg-gray-100 text-gray-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Live Music</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center {{ $restaurant->accepts_reservations ? 'bg-success-100 text-success-600' : 'bg-gray-100 text-gray-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Réservations</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informations & Contact -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                        <span
                            class="inline-flex rounded-full bg-{{ $restaurant->status === 'open' ? 'success' : ($restaurant->status === 'closed' ? 'error' : 'warning') }}-100 px-3 py-1 text-sm font-medium text-{{ $restaurant->status === 'open' ? 'success' : ($restaurant->status === 'closed' ? 'error' : 'warning') }}-800">
                            {{ $restaurant->status === 'open' ? 'Ouvert' : ($restaurant->status === 'closed' ? 'Fermé' : 'Bientôt') }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Contact</label>
                        <p class="text-gray-800 dark:text-white/90 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $restaurant->phone ?: 'Non renseigné' }}
                        </p>
                        <p class="text-gray-800 dark:text-white/90 flex items-center gap-2 mt-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $restaurant->email ?: 'Non renseigné' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Ordre
                            d'affichage</label>
                        <p class="text-gray-800 dark:text-white/90">{{ $restaurant->display_order }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('dashboard.restaurants.edit', $restaurant) }}"
                        class="block w-full px-4 py-2 text-center bg-brand-500 text-white rounded-md hover:bg-brand-600">
                        Modifier l'établissement
                    </a>

                    <form action="{{ route('dashboard.restaurants.destroy', $restaurant) }}" method="POST"
                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet établissement ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="block w-full px-4 py-2 border border-error-300 text-error-600 rounded-md hover:bg-error-50 dark:border-error-800 dark:text-error-500 dark:hover:bg-error-900/20 text-center font-medium">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection