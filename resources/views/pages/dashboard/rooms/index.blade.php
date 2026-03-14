@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Chambres</h1>
        <p class="text-gray-600 dark:text-gray-400">Gérer les chambres de l'hôtel</p>
    </div>
    <a href="{{ route('dashboard.rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nouvelle chambre
    </a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9v10a2 2 0 002 2h14a2 2 0 002-2V9M3 9l9-7 9 7M3 9v10h6V9h6v10h6V9"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Disponibles</p>
                <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['available'] }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Occupées</p>
                <p class="text-2xl font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $stats['occupied'] }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/10">
                <svg class="h-6 w-6 text-blue-light-600 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Maintenance</p>
                <p class="text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $stats['maintenance'] }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/10">
                <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <form method="GET" action="{{ route('dashboard.rooms.index') }}" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par numéro..."
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div class="min-w-[150px]">
            <select name="type" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous les types</option>
                <option value="single" {{ request('type') === 'single' ? 'selected' : '' }}>Simple</option>
                <option value="double" {{ request('type') === 'double' ? 'selected' : '' }}>Double</option>
                <option value="suite" {{ request('type') === 'suite' ? 'selected' : '' }}>Suite</option>
                <option value="deluxe" {{ request('type') === 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                <option value="presidential" {{ request('type') === 'presidential' ? 'selected' : '' }}>Présidentielle</option>
            </select>
        </div>
        <div class="min-w-[150px]">
            <select name="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous les statuts</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupée</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Réservée</option>
            </select>
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Trier par</label>
            <select name="sort" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="room_number_asc" {{ request('sort', 'room_number_asc') === 'room_number_asc' ? 'selected' : '' }}>Numéro (A→Z)</option>
                <option value="room_number_desc" {{ request('sort') === 'room_number_desc' ? 'selected' : '' }}>Numéro (Z→A)</option>
                <option value="type_asc" {{ request('sort') === 'type_asc' ? 'selected' : '' }}>Type</option>
                <option value="status_asc" {{ request('sort') === 'status_asc' ? 'selected' : '' }}>Statut</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
            Filtrer
        </button>
        @if(request()->hasAny(['search', 'type', 'status', 'sort']))
            <a href="{{ route('dashboard.rooms.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Réinitialiser
            </a>
        @endif
    </form>
</div>

<!-- Liste des chambres -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    @if($rooms->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Numéro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Étage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Capacité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Prix/nuit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Accès tablette</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($rooms as $room)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $room->room_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ ($typeLabels ?? [])[$room->type ?? ''] ?? $room->type ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $room->floor ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $room->capacity ?? 0 }} pers.</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format((float) ($room->price_per_night ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                        'occupied' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                                        'maintenance' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                                        'reserved' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$room->status ?? ''] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ $room->status_name ?? ucfirst($room->status ?? '—') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($room->tabletAccessUser)
                                    <a href="{{ route('dashboard.tablet-accesses.edit', $room->tabletAccessUser->id) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">Gérer l'accès</a>
                                @else
                                    <a href="{{ route('dashboard.tablet-accesses.create') }}?room_id={{ $room->id }}" class="text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">Relier un accès</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-action-buttons 
                                    :showRoute="route('dashboard.rooms.show', $room)"
                                    :editRoute="route('dashboard.rooms.edit', $room)"
                                    :deleteRoute="route('dashboard.rooms.destroy', $room)"
                                    deleteMessage="Êtes-vous sûr de vouloir supprimer cette chambre ?"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $rooms->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9v10a2 2 0 002 2h14a2 2 0 002-2V9M3 9l9-7 9 7M3 9v10h6V9h6v10h6V9"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Aucune chambre</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Commencez par créer votre première chambre.</p>
            <div class="mt-6">
                <a href="{{ route('dashboard.rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Créer une chambre
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
