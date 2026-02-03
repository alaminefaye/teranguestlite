@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Mes Réservations</h1>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-4">
    @forelse($reservations as $reservation)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $reservation->restaurant->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->reservation_date->format('d/m/Y') }} à {{ substr($reservation->reservation_time, 0, 5) }}</p>
                </div>
                @php
                    $colors = ['pending' => 'bg-warning-50 text-warning-600', 'confirmed' => 'bg-success-50 text-success-600', 'cancelled' => 'bg-error-50 text-error-600', 'completed' => 'bg-gray-100 text-gray-600'];
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colors[$reservation->status] ?? 'bg-gray-50 text-gray-600' }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->number_of_guests }} personne(s)</p>
        </div>
    @empty
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400">Aucune réservation</p>
            <a href="{{ route('guest.restaurants.index') }}" class="mt-4 inline-block text-brand-600 dark:text-brand-400">Réserver une table</a>
        </div>
    @endforelse
</div>
@endsection
