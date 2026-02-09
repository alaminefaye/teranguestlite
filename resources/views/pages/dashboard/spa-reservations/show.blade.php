@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.spa-reservations.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux réservations Spa</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Réservation Spa</h1>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Résumé réservation -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Résumé</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Service</dt><dd class="font-medium">{{ $reservation->spaService?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date</dt><dd class="font-medium">{{ $reservation->reservation_date?->format('d/m/Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Heure</dt><dd class="font-medium">{{ $reservation->reservation_time ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Prix</dt><dd class="font-medium">{{ $reservation->formatted_price }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                <dd>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium @if($reservation->status === 'confirmed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 @elseif($reservation->status === 'cancelled') bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400 @else bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 @endif">{{ $reservation->status }}</span>
                </dd>
            </div>
            @if($reservation->special_requests)
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <dt class="text-gray-500 dark:text-gray-400 mb-1">Demandes spéciales</dt>
                <dd class="text-gray-800 dark:text-white/90 whitespace-pre-wrap">{{ $reservation->special_requests }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Client (invité) lié au code -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Client</h2>
        @if($reservation->guest)
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nom</dt><dd class="font-medium">{{ $reservation->guest->name }}</dd></div>
            @if($reservation->guest->email)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd>{{ $reservation->guest->email }}</dd></div>@endif
            @if($reservation->guest->phone)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Téléphone</dt><dd>{{ $reservation->guest->phone }}</dd></div>@endif
            @if($reservation->room)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $reservation->room->room_number }}</dd></div>@endif
            @if($reservation->guest->nationality)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nationalité</dt><dd>{{ $reservation->guest->nationality }}</dd></div>@endif
        </dl>
        @else
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            @if($reservation->room)Client Chambre {{ $reservation->room->room_number }}@else—@endif
        </p>
        @endif
    </div>
</div>
@endsection
