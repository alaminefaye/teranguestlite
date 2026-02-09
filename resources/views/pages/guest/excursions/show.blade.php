@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <a href="{{ route('guest.excursions.index') }}" class="text-brand-600 text-sm mb-2 inline-block">← Retour</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $excursion->name }}</h1>
</div>

<!-- Info Excursion -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $excursion->description }}</p>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Prix Adulte:</span> <span class="font-semibold text-brand-600">{{ $excursion->formatted_price_adult }}</span></div>
        @if($excursion->price_child)
            <div><span class="text-gray-500">Prix Enfant:</span> <span class="font-semibold">{{ number_format($excursion->price_child, 0, ',', ' ') }} FCFA</span></div>
        @endif
        <div><span class="text-gray-500">Durée:</span> <span class="text-gray-900 dark:text-white">{{ $excursion->duration_hours }} heures</span></div>
        @if($excursion->departure_time)
            <div><span class="text-gray-500">Départ:</span> <span class="text-gray-900 dark:text-white">{{ substr($excursion->departure_time, 0, 5) }}</span></div>
        @endif
    </div>
</div>

<!-- Formulaire Réservation -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Réserver</h2>
    <form action="{{ route('guest.excursions.book', $excursion) }}" method="POST" x-data="{ adults: 1, children: 0, priceAdult: {{ $excursion->price_adult }}, priceChild: {{ $excursion->price_child ?? 0 }}, get total() { return (this.adults * this.priceAdult) + (this.children * this.priceChild); } }">
        @csrf
        <div class="space-y-4">
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code client</label>
                <input type="text" name="client_code" value="{{ old('client_code') }}" maxlength="20" placeholder="Ex: 123456 (reçu à l'enregistrement)" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">À remplir si votre compte n'a pas de séjour actif lié à la chambre. Sinon laissez vide.</p>
                @error('client_code')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" name="booking_date" min="{{ date('Y-m-d') }}" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre d'adultes</label>
                <input type="number" name="number_of_adults" x-model="adults" min="1" value="1" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre d'enfants</label>
                <input type="number" name="number_of_children" x-model="children" min="0" value="0" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div class="bg-brand-50 dark:bg-brand-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-700 dark:text-gray-300">Total estimé: <span class="font-bold text-brand-600 dark:text-brand-400 text-lg" x-text="total.toLocaleString('fr-FR') + ' FCFA'"></span></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Demandes spéciales (optionnel)</label>
                <textarea name="special_requests" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3"></textarea>
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium">Confirmer la réservation</button>
        </div>
    </form>
</div>
@endsection
