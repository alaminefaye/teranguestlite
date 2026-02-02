@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.show', $rental) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Enregistrer le Kilométrage de Début</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Location #{{ $rental->id }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Véhicule</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Plaque</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->plate_number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Kilométrage actuel</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ number_format($rental->vehicle->current_mileage ?? 0, 0, ',', ' ') }} km
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('rentals.mileage.start.store', $rental) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Kilométrage au début de la location <span class="text-error-500">*</span>
                            </label>
                            <input type="number" 
                                   name="start_mileage" 
                                   value="{{ old('start_mileage', $rental->vehicle->current_mileage ?? 0) }}"
                                   min="0"
                                   step="1"
                                   required
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('start_mileage')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Le kilométrage actuel du véhicule est pré-rempli. Vérifiez et ajustez si nécessaire.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('rentals.show', $rental) }}" 
                               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                Annuler
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
