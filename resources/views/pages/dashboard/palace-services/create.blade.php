@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.palace-services.index') }}" class="hover:text-brand-500">Services Palace</a>
        <span>/</span>
        <span>Créer un service</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer un service palace</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.palace-services.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie <span class="text-error-500">*</span></label>
                <select name="category" id="category" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="concierge" {{ old('category') == 'concierge' ? 'selected' : '' }}>Conciergerie</option>
                    <option value="transport" {{ old('category') == 'transport' ? 'selected' : '' }}>Transport</option>
                    <option value="vip" {{ old('category') == 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="butler" {{ old('category') == 'butler' ? 'selected' : '' }}>Butler</option>
                </select>
                @error('category')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix (FCFA)</label>
                <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="price_on_request" id="price_on_request" value="1" {{ old('price_on_request') ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="price_on_request" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Prix sur demande</label>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Disponible</option>
                    <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>Indisponible</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_premium" id="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="is_premium" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Premium</label>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image / Icône</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommandé : 800×600 px ou carré, max 30 Mo.</p>
                @error('image')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Créer</button>
            <a href="{{ route('dashboard.palace-services.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
