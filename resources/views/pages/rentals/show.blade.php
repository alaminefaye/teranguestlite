@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Location #{{ $rental->id }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails de la location</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations principales -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations de la Location</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Véhicule</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Plaque d'immatriculation</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->plate_number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Type</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ ucfirst($rental->vehicle->type) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Date de début</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->start_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Date de fin</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->end_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Durée</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $rental->start_date->diffInDays($rental->end_date) + 1 }} jour(s)
                        </span>
                    </div>
                    @if($rental->vehicle->chauffeur)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Chauffeur assigné</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->chauffeur->name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Kilométrage actuel du véhicule</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ number_format($rental->vehicle->current_mileage ?? 0, 0, ',', ' ') }} km
                        </span>
                    </div>
                </div>
            </div>

            <!-- Suivi Kilométrique -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Suivi Kilométrique</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Kilométrage de début</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            @if($rental->start_mileage)
                                {{ number_format($rental->start_mileage, 0, ',', ' ') }} km
                            @else
                                <span class="text-warning-600">Non enregistré</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Kilométrage de fin</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            @if($rental->end_mileage)
                                {{ number_format($rental->end_mileage, 0, ',', ' ') }} km
                            @else
                                <span class="text-warning-600">Non enregistré</span>
                            @endif
                        </span>
                    </div>
                    @if($rental->total_mileage)
                        <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-800">
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">Total parcouru</span>
                            <span class="text-base font-bold text-brand-500">
                                {{ number_format($rental->total_mileage, 0, ',', ' ') }} km
                            </span>
                        </div>
                    @endif
                </div>
                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'chauffeur']))
                    <div class="mt-4 flex gap-2">
                        @if(!$rental->start_mileage)
                            <a href="{{ route('rentals.mileage.start', $rental) }}" 
                               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enregistrer début
                            </a>
                        @endif
                        @if($rental->start_mileage && !$rental->end_mileage)
                            <a href="{{ route('rentals.mileage.end', $rental) }}" 
                               class="inline-flex items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-success-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enregistrer fin
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- État des Lieux -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">État des Lieux</h3>
                
                <!-- État de début -->
                <div class="mb-6">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Au début de la location</h4>
                    @if($rental->start_photos && count($rental->start_photos) > 0)
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            @foreach($rental->start_photos as $photo)
                                <div class="relative group">
                                    <img src="{{ Storage::url($photo) }}" 
                                         alt="Photo état début" 
                                         class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <a href="{{ Storage::url($photo) }}" 
                                       target="_blank"
                                       class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Aucune photo enregistrée</p>
                    @endif
                    @if($rental->start_condition_notes)
                        <div class="mb-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $rental->start_condition_notes }}</p>
                        </div>
                    @endif
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'chauffeur']))
                        <a href="{{ route('rentals.condition.start', $rental) }}" 
                           class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ $rental->start_photos ? 'Modifier' : 'Enregistrer' }}
                        </a>
                    @endif
                </div>

                <!-- État de fin -->
                <div>
                    <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">À la fin de la location</h4>
                    @if($rental->end_photos && count($rental->end_photos) > 0)
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            @foreach($rental->end_photos as $photo)
                                <div class="relative group">
                                    <img src="{{ Storage::url($photo) }}" 
                                         alt="Photo état fin" 
                                         class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <a href="{{ Storage::url($photo) }}" 
                                       target="_blank"
                                       class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Aucune photo enregistrée</p>
                    @endif
                    @if($rental->end_condition_notes)
                        <div class="mb-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $rental->end_condition_notes }}</p>
                        </div>
                    @endif
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'chauffeur']))
                        <a href="{{ route('rentals.condition.end', $rental) }}" 
                           class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ $rental->end_photos ? 'Modifier' : 'Enregistrer' }}
                        </a>
                    @endif
                </div>
            </div>

            @if($rental->notes)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-2 text-sm font-semibold text-gray-800 dark:text-white/90">Notes</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $rental->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Informations client et statut -->
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Client</h3>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->user->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $rental->user->email }}</p>
                    @if($rental->user->phone)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $rental->user->phone }}</p>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Prix</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Montant total</span>
                        <span class="text-lg font-bold text-brand-500">{{ number_format($rental->total_price, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Prix par jour</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            @php
                                $dailyRates = [
                                    'bus' => 10000,
                                    'navette' => 8000,
                                    'minibus' => 6000,
                                ];
                                $dailyRate = $dailyRates[$rental->vehicle->type] ?? 5000;
                            @endphp
                            {{ number_format($dailyRate, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Statut</h3>
                @php
                    $statusColors = [
                        'pending' => 'bg-warning-50 text-warning-600',
                        'active' => 'bg-success-50 text-success-600',
                        'completed' => 'bg-gray-50 text-gray-600',
                        'cancelled' => 'bg-error-50 text-error-600',
                    ];
                    $statusLabels = [
                        'pending' => 'En attente',
                        'active' => 'Active',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ];
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$rental->status] ?? 'bg-gray-50 text-gray-600' }}">
                    {{ $statusLabels[$rental->status] ?? $rental->status }}
                </span>
            </div>

            <!-- Paiement -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Paiement</h3>
                @php
                    $paymentStatus = $rental->payment_status;
                @endphp
                <div class="space-y-3">
                    @if($paymentStatus === 'paid')
                        <div class="rounded-lg bg-success-50 p-3 text-sm text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Paiement effectué</span>
                            </div>
                        </div>
                        @if($rental->latestPayment)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Référence: {{ $rental->latestPayment->payment_reference }}
                            </div>
                        @endif
                    @else
                        <div class="rounded-lg bg-warning-50 p-3 text-sm text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Paiement en attente</span>
                            </div>
                        </div>
                        @if(auth()->check() && (auth()->user()->role === 'admin' || $rental->user_id === auth()->id()))
                            <a href="{{ route('rentals.payment.show', $rental) }}" 
                               class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Payer maintenant
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('rentals.contract.download', $rental) }}" 
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Télécharger le contrat PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
