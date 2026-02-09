@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.excursion-bookings.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux réservations excursions</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Réservation excursion</h1>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Résumé</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Excursion</dt><dd class="font-medium">{{ $booking->excursion?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date</dt><dd class="font-medium">{{ $booking->booking_date?->format('d/m/Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Adultes</dt><dd>{{ $booking->number_of_adults ?? 0 }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Enfants</dt><dd>{{ $booking->number_of_children ?? 0 }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="font-medium">{{ $booking->formatted_total_price }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                <dd><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium @if($booking->status === 'confirmed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 @elseif($booking->status === 'cancelled') bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400 @else bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 @endif">{{ $booking->status }}</span></dd>
            </div>
            @if($booking->special_requests)
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <dt class="text-gray-500 dark:text-gray-400 mb-1">Demandes spéciales</dt>
                <dd class="text-gray-800 dark:text-white/90 whitespace-pre-wrap">{{ $booking->special_requests }}</dd>
            </div>
            @endif
        </dl>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Client</h2>
        @if($booking->guest)
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nom</dt><dd class="font-medium">{{ $booking->guest->name }}</dd></div>
            @if($booking->guest->email)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd>{{ $booking->guest->email }}</dd></div>@endif
            @if($booking->guest->phone)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Téléphone</dt><dd>{{ $booking->guest->phone }}</dd></div>@endif
            @if($booking->room)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $booking->room->room_number }}</dd></div>@endif
        </dl>
        @else
        <p class="text-gray-500 dark:text-gray-400 text-sm">@if($booking->room)Client Chambre {{ $booking->room->room_number }}@else—@endif</p>
        @endif
    </div>
</div>
@endsection
