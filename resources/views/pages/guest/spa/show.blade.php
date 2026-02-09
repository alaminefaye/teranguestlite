@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <a href="{{ route('guest.spa.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $service->name }}</h1>
</div>

<!-- Info Service -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $service->description }}</p>
    <div class="flex items-center gap-6">
        <div><span class="text-gray-500">Prix:</span> <span class="text-lg font-semibold text-brand-600 dark:text-brand-400">{{ $service->formatted_price }}</span></div>
        <div><span class="text-gray-500">Durée:</span> <span class="text-gray-900 dark:text-white">{{ $service->duration_text }}</span></div>
    </div>
</div>

<!-- Formulaire Réservation -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Réserver</h2>
    <form action="{{ route('guest.spa.reserve', $service) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" name="reservation_date" min="{{ date('Y-m-d') }}" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Heure</label>
                <input type="time" name="reservation_time" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Demandes spéciales (optionnel)</label>
                <textarea name="special_requests" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3"></textarea>
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium">Confirmer</button>
        </div>
    </form>
</div>
@endsection
