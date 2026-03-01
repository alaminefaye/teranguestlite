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
