@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-brand-500">Réservations</a>
        <span>/</span>
        <span>{{ $reservation->reservation_number }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $reservation->reservation_number }}</h1>
            <p class="text-gray-600 dark:text-gray-400">Réservation pour {{ $reservation->guest ? $reservation->guest->name : ($reservation->user?->name ?? '—') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($reservation->status === 'confirmed')
                <form action="{{ route('dashboard.reservations.checkin', $reservation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-light-500 text-white rounded-md hover:bg-blue-light-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Check-in
                    </button>
                </form>
            @endif

            @if($reservation->status === 'checked_in')
                <form action="{{ route('dashboard.reservations.checkout', $reservation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-success-500 text-white rounded-md hover:bg-success-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Check-out
                    </button>
                </form>
            @endif

            @if(in_array($reservation->status, ['pending', 'confirmed']))
                <a href="{{ route('dashboard.reservations.edit', $reservation) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
            @endif

            @if($reservation->status !== 'checked_out' && $reservation->status !== 'cancelled')
                <form action="{{ route('dashboard.reservations.cancel', $reservation) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-error-500 text-white rounded-md hover:bg-error-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Annuler
                    </button>
                </form>
            @endif
        </div>
    </div>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informations principales -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Détails de la réservation -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Détails de la réservation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Numéro de réservation</label>
                    <p class="text-gray-800 dark:text-white/90 font-mono">{{ $reservation->reservation_number }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                    @php
                        $statusColors = [
                            'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                            'confirmed' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                            'checked_in' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                            'checked_out' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$reservation->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ $reservation->status_name }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Client</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->user->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $reservation->user->email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Chambre</label>
                    <a href="{{ route('dashboard.rooms.show', $reservation->room) }}" class="text-brand-600 dark:text-brand-400 hover:underline">
                        Chambre {{ $reservation->room->room_number }} ({{ $reservation->room->type_name }})
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->check_in->format('d/m/Y') }}</p>
                    @if($reservation->checked_in_at)
                        <p class="text-xs text-gray-500 dark:text-gray-400">Effectué le {{ $reservation->checked_in_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Check-out</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->check_out->format('d/m/Y') }}</p>
                    @if($reservation->checked_out_at)
                        <p class="text-xs text-gray-500 dark:text-gray-400">Effectué le {{ $reservation->checked_out_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre de nuits</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->nights_count }} nuit{{ $reservation->nights_count > 1 ? 's' : '' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre de personnes</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->guests_count }} personne{{ $reservation->guests_count > 1 ? 's' : '' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Prix total</label>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</p>
                </div>

                @if($reservation->special_requests)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Demandes spéciales</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->special_requests }}</p>
                </div>
                @endif

                @if($reservation->notes)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Notes internes</label>
                    <p class="text-gray-800 dark:text-white/90 bg-gray-50 dark:bg-gray-800 p-3 rounded-md">{{ $reservation->notes }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Créée le</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Dernière mise à jour</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Timeline -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Timeline</h3>
            <div class="space-y-4">
                <!-- Créée -->
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Créée</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Confirmée -->
                @if($reservation->status !== 'pending')
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success-100 dark:bg-success-500/10">
                        <svg class="h-4 w-4 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Confirmée</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif

                <!-- Check-in -->
                @if($reservation->checked_in_at)
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-light-100 dark:bg-blue-light-500/10">
                        <svg class="h-4 w-4 text-blue-light-600 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Check-in effectué</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->checked_in_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif

                <!-- Check-out -->
                @if($reservation->checked_out_at)
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Check-out effectué</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->checked_out_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif

                <!-- Annulée -->
                @if($reservation->status === 'cancelled')
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-error-100 dark:bg-error-500/10">
                        <svg class="h-4 w-4 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Annulée</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informations chambre -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations chambre</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Numéro</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->room->room_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->room->type_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Capacité</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $reservation->room->capacity }} personnes</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Prix/nuit</label>
                    <p class="text-gray-800 dark:text-white/90">{{ number_format($reservation->room->price_per_night, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Résumé -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Résumé</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Prix par nuit</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($reservation->room->price_per_night, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Nombre de nuits</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $reservation->nights_count }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-base font-semibold text-gray-800 dark:text-white/90">Total</span>
                    <span class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
