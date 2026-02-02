@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('reservations.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">
                    Réservation #{{ $booking && $booking->booking_reference ? $booking->booking_reference : 'N/A' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails de la réservation</p>
            </div>
        </div>
    </div>

    <!-- Toutes les boxes regroupées en haut en 2 rangées -->
    <div class="space-y-4">
        <!-- Première rangée : Informations du Trajet + Client -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Informations du Trajet - 2 colonnes -->
            <div class="md:col-span-2 rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Informations du Trajet</h3>
                @if($booking && $booking->schedule)
                    @if($booking->schedule->route)
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-2">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Départ</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">{{ $booking->schedule->route->departureStation->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Arrivée</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">{{ $booking->schedule->route->arrivalStation->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Date</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">
                                        {{ $booking->schedule->schedule_date ? $booking->schedule->schedule_date->format('d/m/Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Heure de départ</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">{{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Véhicule</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">{{ $booking->schedule->vehicle->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Siège(s)</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">
                                        @if($booking->seats && $booking->seats->count() > 0)
                                            {{ implode(', ', $booking->seats->pluck('seat_number')->toArray()) }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $booking->seats->count() }} {{ $booking->seats->count() > 1 ? 'sièges' : 'siège' }})</span>
                                        @else
                                            {{ $booking->seat_number ?? 'N/A' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Trajet supprimé</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Informations du trajet non disponibles</p>
                @endif
            </div>

            <!-- Client - 1 colonne -->
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Client</h3>
                <div class="space-y-1.5">
                    @if($booking && $booking->user)
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->user->email }}</p>
                        @if($booking->user->phone)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->user->phone }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Client supprimé</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Deuxième rangée : Paiement, Statut, Créé par, et Bouton Billet -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Paiement -->
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Paiement</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Montant</span>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90 mt-0.5">
                            {{ $booking && $booking->total_price ? number_format($booking->total_price, 0, ',', ' ') . ' FCFA' : '0 FCFA' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Statut</span>
                        <div class="mt-0.5">
                            @if($booking && $booking->payment_status)
                                @php
                                    $paymentColors = [
                                        'paid' => 'bg-success-50 text-success-600',
                                        'pending' => 'bg-warning-50 text-warning-600',
                                        'failed' => 'bg-error-50 text-error-600',
                                        'refunded' => 'bg-gray-50 text-gray-600',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $paymentColors[$booking->payment_status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">Non défini</span>
                            @endif
                        </div>
                    </div>
                    @if($booking && $booking->payment_method)
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Méthode</span>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90 mt-0.5">{{ $booking->payment_method }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statut de la réservation -->
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Statut</h3>
                @if($booking && $booking->status)
                    @php
                        $statusColors = [
                            'pending' => 'bg-warning-50 text-warning-600',
                            'confirmed' => 'bg-success-50 text-success-600',
                            'cancelled' => 'bg-error-50 text-error-600',
                            'completed' => 'bg-gray-50 text-gray-600',
                        ];
                        $statusLabels = [
                            'pending' => 'En attente',
                            'confirmed' => 'Confirmée',
                            'cancelled' => 'Annulée',
                            'completed' => 'Terminée',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$booking->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ $statusLabels[$booking->status] ?? $booking->status }}
                    </span>
                @else
                    <span class="text-xs text-gray-500 dark:text-gray-400">Non défini</span>
                @endif
            </div>

            <!-- Créé par -->
            @if($booking && $booking->createdBy)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Créé par</h3>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->createdBy->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if($booking->createdBy->role === 'admin')
                                Administrateur
                            @elseif($booking->createdBy->role === 'caissier')
                                Caissier
                            @else
                                {{ ucfirst($booking->createdBy->role) }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            <!-- Bouton Payer maintenant si non payé -->
            @if($booking && $booking->id && $booking->payment_status !== 'paid')
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Paiement</h3>
                    <a href="{{ route('payments.show', $booking->id) }}" 
                       class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition-colors">
                        Payer maintenant
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Section Billets - Afficher tous les tickets les uns après les autres -->
    @if($booking && $booking->id && $booking->payment_status === 'paid')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Billets</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($booking->tickets && $booking->tickets->count() > 0)
                            {{ $booking->tickets->count() }} {{ $booking->tickets->count() > 1 ? 'billets' : 'billet' }} disponible(s)
                        @elseif($booking->ticket)
                            1 billet disponible
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('tickets.show-all', $booking->id) }}" 
                       target="_blank"
                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Voir tous les tickets
                    </a>
                    <a href="{{ route('tickets.show-all', $booking->id) }}?print=1" 
                       target="_blank"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer tous les tickets
                    </a>
                </div>
            </div>

            @if($booking->tickets && $booking->tickets->count() > 0)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($booking->tickets as $ticket)
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Siège {{ $ticket->seat_number ?? 'N/A' }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">N° {{ $ticket->ticket_number }}</p>
                                </div>
                                @if($ticket->status === 'valid')
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                        Valide
                                    </span>
                                @elseif($ticket->status === 'used')
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                                        Utilisé
                                    </span>
                                @endif
                            </div>
                            
                            @if($ticket->qr_code)
                                <div class="mb-4 flex justify-center">
                                    <img src="{{ asset('storage/' . $ticket->qr_code) }}" 
                                         alt="QR Code" 
                                         class="h-32 w-32 object-contain"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'128\' height=\'128\'%3E%3Crect width=\'128\' height=\'128\' fill=\'%23f3f4f6\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'Arial\' font-size=\'12\'%3EQR Code%3C/text%3E%3C/svg%3E';">
                                </div>
                            @endif

                            <div class="flex gap-2">
                                <a href="{{ route('tickets.show', $booking->id) }}?ticket={{ $ticket->id }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Voir
                                </a>
                                <a href="{{ route('tickets.download', $booking->id) }}?ticket={{ $ticket->id }}&print=1" 
                                   target="_blank"
                                   class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Imprimer
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($booking->ticket)
                <!-- Ancien système : un seul ticket -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Siège {{ $booking->ticket->seat_number ?? $booking->seat_number ?? 'N/A' }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">N° {{ $booking->ticket->ticket_number }}</p>
                        </div>
                        @if($booking->ticket->status === 'valid')
                            <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                Valide
                            </span>
                        @endif
                    </div>
                    
                    @if($booking->ticket->qr_code)
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('storage/' . $booking->ticket->qr_code) }}" 
                                 alt="QR Code" 
                                 class="h-32 w-32 object-contain"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'128\' height=\'128\'%3E%3Crect width=\'128\' height=\'128\' fill=\'%23f3f4f6\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'Arial\' font-size=\'12\'%3EQR Code%3C/text%3E%3C/svg%3E';">
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('tickets.show', $booking->id) }}" 
                           class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Voir
                        </a>
                        <a href="{{ route('tickets.download', $booking->id) }}?print=1" 
                           target="_blank"
                           class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Imprimer
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
