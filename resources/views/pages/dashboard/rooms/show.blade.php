@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.rooms.index') }}" class="hover:text-brand-500">Chambres</a>
        <span>/</span>
        <span>Chambre {{ $room->room_number }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Chambre {{ $room->room_number }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $roomTypeLabel ?? $room->type ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.rooms.edit', $room) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            <form action="{{ route('dashboard.rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette chambre ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-error-500 text-white rounded-md hover:bg-error-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informations principales -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Détails de la chambre -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations de la chambre</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Numéro</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $room->room_number }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Étage</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $room->floor ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $roomTypeLabel ?? $room->type ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                    @php
                        $statusColors = [
                            'available' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                            'occupied' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                            'maintenance' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                            'reserved' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$room->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ $room->status_name }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Prix par nuit</label>
                    <p class="text-gray-800 dark:text-white/90">{{ number_format($room->price_per_night, 0, ',', ' ') }} FCFA</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Capacité</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $room->capacity }} personnes</p>
                </div>

                @if($room->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $room->description }}</p>
                </div>
                @endif

                @if($room->amenities && count($room->amenities) > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Équipements</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $amenitiesLabels = [
                                'wifi' => 'Wi-Fi',
                                'tv' => 'Télévision',
                                'minibar' => 'Minibar',
                                'ac' => 'Climatisation',
                                'safe' => 'Coffre-fort',
                                'balcony' => 'Balcon',
                                'bathtub' => 'Baignoire',
                                'shower' => 'Douche',
                                'hairdryer' => 'Sèche-cheveux',
                                'phone' => 'Téléphone',
                                'ironing' => 'Fer à repasser',
                                'desk' => 'Bureau',
                            ];
                        @endphp
                        @foreach($room->amenities as $amenity)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                {{ $amenitiesLabels[$amenity] ?? $amenity }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Réservations -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Réservations récentes</h3>
            
            @if($room->reservations->count() > 0)
                <div class="space-y-4">
                    @foreach($room->reservations as $reservation)
                        <div class="border-l-4 pl-4 py-2 {{ $reservation->status === 'confirmed' ? 'border-success-500' : ($reservation->status === 'pending' ? 'border-warning-500' : 'border-gray-300 dark:border-gray-700') }}">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $reservation->reservation_number }}</p>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $reservation->status_color }}-50 text-{{ $reservation->status_color }}-600 dark:bg-{{ $reservation->status_color }}-500/10 dark:text-{{ $reservation->status_color }}-400">
                                    {{ $reservation->status_name }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ optional($reservation->user)->name ?? optional($reservation->guest)->name ?? 'Client' }} • 
                                {{ $reservation->check_in->format('d/m/Y') }} - {{ $reservation->check_out->format('d/m/Y') }} 
                                ({{ $reservation->nights_count }} nuit{{ $reservation->nights_count > 1 ? 's' : '' }})
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">Aucune réservation pour cette chambre</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Image -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Image</h3>
            @if($room->image)
                <img src="{{ asset('storage/' . $room->image) }}" alt="Chambre {{ $room->room_number }}" class="w-full rounded-lg">
            @else
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aucune image</p>
                </div>
            @endif
        </div>

        <!-- Statistiques -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Statistiques</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total réservations</span>
                    <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_reservations'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">À venir</span>
                    <span class="text-lg font-semibold text-success-600 dark:text-success-400">{{ $stats['upcoming_reservations'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Terminées</span>
                    <span class="text-lg font-semibold text-gray-600 dark:text-gray-400">{{ $stats['completed_reservations'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
