@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.guests.index') }}" class="hover:text-brand-500">Clients</a>
        <span>/</span>
        <span>Créer</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer un client</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Un code à 6 chiffres sera généré automatiquement pour la tablette en chambre.</p>
    <form action="{{ route('dashboard.guests.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                @error('name')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                @error('email')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                @error('phone')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer</button>
            <a href="{{ route('dashboard.guests.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md">Annuler</a>
        </div>
    </form>
</div>
@endsection
