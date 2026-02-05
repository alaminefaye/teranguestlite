@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.spa-services.index') }}" class="hover:text-brand-500">Services Spa</a>
        <span>/</span>
        <a href="{{ route('dashboard.spa-services.show', $service) }}" class="hover:text-brand-500">{{ $service->name }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier {{ $service->name }}</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.spa-services.update', $service) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie <span class="text-error-500">*</span></label>
                <select name="category" id="category" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="massage" {{ old('category', $service->category) == 'massage' ? 'selected' : '' }}>Massage</option>
                    <option value="facial" {{ old('category', $service->category) == 'facial' ? 'selected' : '' }}>Soin du visage</option>
                    <option value="body_treatment" {{ old('category', $service->category) == 'body_treatment' ? 'selected' : '' }}>Soin du corps</option>
                    <option value="wellness" {{ old('category', $service->category) == 'wellness' ? 'selected' : '' }}>Bien-être</option>
                </select>
                @error('category')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée (minutes) <span class="text-error-500">*</span></label>
                <input type="number" name="duration" id="duration" value="{{ old('duration', $service->duration) }}" required min="1"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('duration')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix (FCFA) <span class="text-error-500">*</span></label>
                <input type="number" name="price" id="price" value="{{ old('price', $service->price) }}" required min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="available" {{ old('status', $service->status) == 'available' ? 'selected' : '' }}>Disponible</option>
                    <option value="unavailable" {{ old('status', $service->status) == 'unavailable' ? 'selected' : '' }}>Indisponible</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $service->display_order) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="is_featured" class="ml-2 text-sm text-gray-700 dark:text-gray-300">En vedette (Populaire)</label>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $service->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            @if($service->image)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image actuelle</label>
                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
            </div>
            @endif

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $service->image ? 'Changer l\'image' : 'Image / Icône' }}</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommandé : 800×600 px ou carré, max 30 Mo. Affichée sur le web et l'app mobile.</p>
                @error('image')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Mettre à jour</button>
            <a href="{{ route('dashboard.spa-services.show', $service) }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
