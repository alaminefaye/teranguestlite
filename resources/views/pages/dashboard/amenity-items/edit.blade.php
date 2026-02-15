@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.amenity-categories.index') }}" class="hover:text-brand-500">Amenities & Conciergerie</a>
        <span>/</span>
        <a href="{{ route('dashboard.amenity-categories.items.index', $category) }}" class="hover:text-brand-500">{{ $category->name }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier l'article</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.amenity-categories.items.update', [$category, $item]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom de l'article <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $item->display_order) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">Enregistrer</button>
            <a href="{{ route('dashboard.amenity-categories.items.index', $category) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Annuler</a>
        </div>
    </form>
</div>
@endsection
