@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.palace-requests.index') }}" class="hover:text-brand-500">Demandes Palace</a>
        <span>/</span>
        <a href="{{ route('dashboard.palace-requests.show', $request) }}" class="hover:text-brand-500">{{ $request->request_number }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier la demande {{ $request->request_number }}</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.palace-requests.update', $request) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="pending" {{ old('status', $request->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ old('status', $request->status) === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="in_progress" {{ old('status', $request->status) === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ old('status', $request->status) === 'completed' ? 'selected' : '' }}>Terminée</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="estimated_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix estimé (FCFA)</label>
                <input type="number" name="estimated_price" id="estimated_price" value="{{ old('estimated_price', $request->estimated_price) }}" min="0" step="1"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('estimated_price')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="requested_for" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Demandé pour</label>
                <input type="datetime-local" name="requested_for" id="requested_for" value="{{ old('requested_for', $request->requested_for ? $request->requested_for->format('Y-m-d\TH:i') : '') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('requested_for')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer</button>
            <a href="{{ route('dashboard.palace-requests.show', $request) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
