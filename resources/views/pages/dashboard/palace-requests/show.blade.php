@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.palace-requests.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux demandes</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Demande {{ $request->request_number }}</h1>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- ══ Informations du client ══════════════════════════════════ --}}
    @php
        $guest = $request->guest;
        $guestRoom = $request->room;
        // Réservation active de ce client pour cette chambre
        $guestStay = $guest?->reservations()
            ->where('room_id', $request->room_id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->orderByDesc('check_in')
            ->first();
    @endphp

    @if($guest)
    <div class="rounded-lg border-2 border-brand-500/40 bg-brand-50 p-6 dark:bg-brand-500/10 dark:border-brand-400/30 lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-500 text-white font-bold text-lg shrink-0">
                {{ mb_substr($guest->name ?? '?', 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Informations du client</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Personne qui a effectué cette demande</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-3 lg:grid-cols-4">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Nom complet</dt>
                <dd class="font-semibold text-gray-900 dark:text-white/90">{{ $guest->name ?? '—' }}</dd>
            </div>
            @if($request->room)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Chambre</dt>
                <dd class="font-semibold text-gray-900 dark:text-white/90">
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Chambre {{ $request->room->room_number }}
                    </span>
                </dd>
            </div>
            @endif
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Code client</dt>
                <dd class="font-mono font-bold text-brand-600 dark:text-brand-400 text-base">{{ $guest->access_code ?? '—' }}</dd>
            </div>
            @if($guest->phone)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Téléphone</dt>
                <dd class="font-medium text-gray-900 dark:text-white/90">
                    <a href="tel:{{ $guest->phone }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $guest->phone }}</a>
                </dd>
            </div>
            @endif
            @if($guest->email)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Email</dt>
                <dd class="font-medium text-gray-900 dark:text-white/90 truncate">
                    <a href="mailto:{{ $guest->email }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $guest->email }}</a>
                </dd>
            </div>
            @endif
            @if($guest->nationality)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Nationalité</dt>
                <dd class="font-medium text-gray-900 dark:text-white/90">{{ $guest->nationality }}</dd>
            </div>
            @endif
            @if($guestStay)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Séjour</dt>
                <dd class="font-medium text-gray-900 dark:text-white/90">
                    {{ $guestStay->check_in?->format('d/m/Y') }} → {{ $guestStay->check_out?->format('d/m/Y') }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Statut séjour</dt>
                <dd>
                    @if($guestStay->status === 'checked_in')
                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span> En séjour
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-blue-light-50 px-2 py-0.5 text-xs font-medium text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400">Confirmé</span>
                    @endif
                </dd>
            </div>
            @endif
        </div>

        {{-- Lien vers la fiche client --}}
        @if(Route::has('dashboard.clients.show'))
        <div class="mt-4 pt-4 border-t border-brand-200 dark:border-brand-700/40">
            <a href="{{ route('dashboard.clients.show', ['client' => $guest->id]) }}"
               class="inline-flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 hover:underline font-medium">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Voir la fiche complète du client
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Infos générales -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Résumé</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Service</dt><dd class="font-medium">{{ $request->palaceService?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Client</dt><dd class="font-medium">{{ $request->user?->name ?? '—' }}</dd></div>
            @if($request->room)
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $request->room->room_number ?? '—' }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Demandé pour</dt><dd class="font-medium">{{ $request->requested_for?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Prix estimé</dt><dd class="font-medium">{{ $request->formatted_estimated_price }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                <dd>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                        @if($request->status === 'pending') bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400
                        @elseif($request->status === 'confirmed' || $request->status === 'in_progress') bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400
                        @elseif($request->status === 'completed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400
                        @elseif($request->status === 'cancelled') bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400
                        @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                        {{ $request->status_label }}
                    </span>
                </dd>
            </div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Créée le</dt><dd>{{ $request->created_at?->format('d/m/Y H:i') }}</dd></div>
        </dl>
        @if($request->status !== 'cancelled')
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('dashboard.palace-requests.edit', $request) }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-500 text-white hover:bg-brand-600">Modifier</a>
            <form action="{{ route('dashboard.palace-requests.cancel', $request) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer l\'annulation de cette demande ?')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-warning-500 text-white hover:bg-warning-600">Annuler la demande</button>
            </form>
        </div>
        @endif
    </div>

    <!-- Description + Détails véhicule (metadata) -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Détails de la demande</h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap mb-4">{{ $request->description ?: '—' }}</p>

        @php $meta = $request->metadata; @endphp
        @if(is_array($meta) && !empty($meta['vehicle_request_type']))
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="font-medium text-gray-900 dark:text-white/90 mb-2">
                @if(($meta['vehicle_request_type'] ?? '') === 'taxi')
                    Détails Taxi
                @else
                    Détails Location véhicule
                @endif
            </h3>
            <dl class="space-y-2 text-sm">
                @if(($meta['vehicle_request_type'] ?? '') === 'taxi')
                    @if(!empty($meta['pickup_address']))<div><dt class="text-gray-500 dark:text-gray-400">Prise en charge</dt><dd class="font-medium">{{ $meta['pickup_address'] }}</dd></div>@endif
                    @if(isset($meta['pickup_lat']) && isset($meta['pickup_lng']))<div><dt class="text-gray-500 dark:text-gray-400">Coordonnées départ</dt><dd class="font-mono text-xs">{{ $meta['pickup_lat'] }}, {{ $meta['pickup_lng'] }}</dd></div>@endif
                    @if(!empty($meta['destination_address']))<div><dt class="text-gray-500 dark:text-gray-400">Destination</dt><dd class="font-medium">{{ $meta['destination_address'] }}</dd></div>@endif
                    @if(isset($meta['distance_km']) && (float)$meta['distance_km'] > 0)<div><dt class="text-gray-500 dark:text-gray-400">Distance</dt><dd class="font-medium">{{ round((float)$meta['distance_km'], 1) }} km</dd></div>@endif
                @else
                    @if(!empty($meta['vehicle_id']))
                        @php $vehicle = \App\Models\Vehicle::withoutGlobalScope('enterprise')->where('id', $meta['vehicle_id'])->where('enterprise_id', $request->enterprise_id)->first(); @endphp
                        @if($vehicle)<div><dt class="text-gray-500 dark:text-gray-400">Véhicule choisi</dt><dd class="font-medium">{{ $vehicle->name }} ({{ $vehicle->type_label }}, {{ $vehicle->number_of_seats }} pl.)</dd></div>@endif
                    @endif
                    @if(empty($meta['vehicle_id']))
                        @if(!empty($meta['number_of_seats']))<div><dt class="text-gray-500 dark:text-gray-400">Nombre de places</dt><dd class="font-medium">{{ $meta['number_of_seats'] }}</dd></div>@endif
                        @if(!empty($meta['vehicle_type']))<div><dt class="text-gray-500 dark:text-gray-400">Type de véhicule</dt><dd class="font-medium">{{ $meta['vehicle_type'] }}</dd></div>@endif
                    @endif
                    @if(!empty($meta['rental_days']))<div><dt class="text-gray-500 dark:text-gray-400">Nombre de jours</dt><dd class="font-medium">{{ $meta['rental_days'] }}</dd></div>@endif
                    @if(!empty($meta['rental_duration_hours']))<div><dt class="text-gray-500 dark:text-gray-400">Durée (heures)</dt><dd class="font-medium">{{ $meta['rental_duration_hours'] }} h</dd></div>@endif
                @endif
            </dl>
        </div>
        @endif

        @if(is_array($meta) && (isset($meta['tour_type']) || isset($meta['guests_count'])) && empty($meta['vehicle_request_type']))
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="font-medium text-gray-900 dark:text-white/90 mb-2">Visite guidée personnalisée</h3>
            <dl class="space-y-2 text-sm">
                @if(!empty($meta['tour_type']))
                    @php
                        $tourLabels = ['cultural' => 'Culturel', 'gastronomic' => 'Gastronomique', 'historical' => 'Historique'];
                    @endphp
                    <div><dt class="text-gray-500 dark:text-gray-400">Type de circuit</dt><dd class="font-medium">{{ $tourLabels[$meta['tour_type']] ?? $meta['tour_type'] }}</dd></div>
                @endif
                @if(!empty($meta['guests_count']))<div><dt class="text-gray-500 dark:text-gray-400">Nombre de personnes</dt><dd class="font-medium">{{ $meta['guests_count'] }}</dd></div>@endif
            </dl>
        </div>
        @endif
    </div>
</div>
@endsection
