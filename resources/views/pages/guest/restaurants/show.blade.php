@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <a href="{{ route('guest.restaurants.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">
        ← Retour aux restaurants
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $restaurant->name }}</h1>
    <p class="text-gray-600 dark:text-gray-400">{{ $restaurant->type_label }}</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Info Restaurant -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    @if($restaurant->description)
        <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $restaurant->description }}</p>
    @endif
    <div class="grid grid-cols-2 gap-4 text-sm">
        @if($restaurant->location)
            <div><span class="text-gray-500">Emplacement:</span> <span class="text-gray-900 dark:text-white">{{ $restaurant->location }}</span></div>
        @endif
        @if($restaurant->capacity)
            <div><span class="text-gray-500">Capacité:</span> <span class="text-gray-900 dark:text-white">{{ $restaurant->capacity }} places</span></div>
        @endif
        @if($restaurant->phone)
            <div><span class="text-gray-500">Téléphone:</span> <span class="text-gray-900 dark:text-white">{{ $restaurant->phone }}</span></div>
        @endif
        @if($restaurant->today_hours)
            <div><span class="text-gray-500">Horaires:</span> <span class="text-gray-900 dark:text-white">{{ $restaurant->today_hours }}</span></div>
        @endif
    </div>
</div>

<!-- Formulaire Réservation -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Réserver une table</h2>
    <form action="{{ route('guest.restaurants.reserve', $restaurant) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" name="reservation_date" min="{{ date('Y-m-d') }}" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-gray-800 dark:text-white/90">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Heure</label>
                <input type="time" name="reservation_time" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-gray-800 dark:text-white/90">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de personnes</label>
                <input type="number" name="number_of_guests" min="1" max="20" value="2" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-gray-800 dark:text-white/90">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Demandes spéciales (optionnel)</label>
                <textarea name="special_requests" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-gray-800 dark:text-white/90"></textarea>
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium">
                Confirmer la réservation
            </button>
        </div>
    </form>
</div>
@endsection
