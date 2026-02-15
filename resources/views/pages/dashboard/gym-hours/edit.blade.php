@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Dashboard</a>
        <span>/</span>
        <span>Horaires salle de sport</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Horaires de la salle de sport</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ces horaires sont affichés dans l’application mobile (Sport & Fitness). Laissez vide pour afficher « Consultez la réception pour les horaires ».</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.gym-hours.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="gym_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Horaires (texte libre)</label>
            <textarea name="gym_hours" id="gym_hours" rows="6" maxlength="2000" placeholder="Ex. Lundi–Vendredi : 6h–22h — Samedi–Dimanche : 7h–20h"
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('gym_hours', $enterprise->gym_hours) }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum 2000 caractères.</p>
            @error('gym_hours')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                Enregistrer
            </button>
            <a href="{{ route('dashboard.index') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Annuler</a>
        </div>
    </form>
</div>
@endsection
