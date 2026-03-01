@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.laundry-requests.index') }}" class="hover:text-brand-500">Demandes Blanchisserie</a>
        <span>/</span>
        <a href="{{ route('dashboard.laundry-requests.show', $request) }}" class="hover:text-brand-500">Détail</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier la demande {{ $request->request_number }}</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.laundry-requests.update', $request) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="pending" {{ old('status', $request->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="in_progress" {{ old('status', $request->status) === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ old('status', $request->status) === 'completed' ? 'selected' : '' }}>Terminée</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="pickup_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prise en charge</label>
                <input type="datetime-local" name="pickup_time" id="pickup_time" value="{{ old('pickup_time', $request->pickup_time ? $request->pickup_time->format('Y-m-d\TH:i') : '') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('pickup_time')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="delivery_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Livraison prévue</label>
                <input type="datetime-local" name="delivery_time" id="delivery_time" value="{{ old('delivery_time', $request->delivery_time ? $request->delivery_time->format('Y-m-d\TH:i') : '') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('delivery_time')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="special_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instructions particulières</label>
                <textarea name="special_instructions" id="special_instructions" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('special_instructions', $request->special_instructions) }}</textarea>
                @error('special_instructions')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer</button>
            <a href="{{ route('dashboard.laundry-requests.show', $request) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
