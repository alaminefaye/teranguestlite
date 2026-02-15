@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.leisure-categories.index') }}" class="hover:text-brand-500">Bien-être, Sport & Loisirs</a>
        <span>/</span>
        <a href="{{ route('dashboard.leisure-categories.subcategories.index', $parent) }}" class="hover:text-brand-500">{{ $parent->name }}</a>
        <span>/</span>
        <span>Nouvelle activité</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Ajouter une activité</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.leisure-categories.subcategories.store', $parent) }}" method="POST">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="ex. Golf & Tennis, Spa & Wellness..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (optionnel)</label>
                <textarea name="description" id="description" rows="2" placeholder="Courte description pour l'app"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type (écran ouvert dans l'app) <span class="text-error-500">*</span></label>
                <select name="type" id="type" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="golf" {{ old('type') == 'golf' ? 'selected' : '' }}>Golf</option>
                    <option value="tennis" {{ old('type') == 'tennis' ? 'selected' : '' }}>Tennis</option>
                    <option value="fitness" {{ old('type') == 'fitness' ? 'selected' : '' }}>Sport & Fitness</option>
                    <option value="spa" {{ old('type') == 'spa' ? 'selected' : '' }}>Spa & Wellness</option>
                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('type')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">Créer</button>
            <a href="{{ route('dashboard.leisure-categories.subcategories.index', $parent) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Annuler</a>
        </div>
    </form>
</div>
@endsection
