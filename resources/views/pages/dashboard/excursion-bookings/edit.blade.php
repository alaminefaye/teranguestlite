@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.excursion-bookings.index') }}" class="hover:text-brand-500">Réservations Excursions</a>
        <span>/</span>
        <a href="{{ route('dashboard.excursion-bookings.show', $booking) }}" class="hover:text-brand-500">Détail</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier la réservation excursion</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.excursion-bookings.update', $booking) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="booking_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date <span class="text-error-500">*</span></label>
                <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date', $booking->booking_date?->format('Y-m-d')) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('booking_date')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="number_of_adults" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre d'adultes <span class="text-error-500">*</span></label>
                <input type="number" name="number_of_adults" id="number_of_adults" value="{{ old('number_of_adults', $booking->number_of_adults ?? 0) }}" required min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('number_of_adults')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="number_of_children" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre d'enfants <span class="text-error-500">*</span></label>
                <input type="number" name="number_of_children" id="number_of_children" value="{{ old('number_of_children', $booking->number_of_children ?? 0) }}" required min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('number_of_children')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="special_requests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Demandes particulières</label>
                <textarea name="special_requests" id="special_requests" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('special_requests', $booking->special_requests) }}</textarea>
                @error('special_requests')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="pending" {{ old('status', $booking->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ old('status', $booking->status) === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer</button>
            <a href="{{ route('dashboard.excursion-bookings.show', $booking) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
