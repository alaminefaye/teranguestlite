@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.excursions.index') }}" class="hover:text-brand-500">Excursions</a>
        <span>/</span>
        <a href="{{ route('dashboard.excursions.show', $excursion) }}" class="hover:text-brand-500">{{ $excursion->name }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier {{ $excursion->name }}</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.excursions.update', $excursion) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $excursion->name) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span class="text-error-500">*</span></label>
                <select name="type" id="type" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="cultural" {{ old('type', $excursion->type) == 'cultural' ? 'selected' : '' }}>Culturel</option>
                    <option value="adventure" {{ old('type', $excursion->type) == 'adventure' ? 'selected' : '' }}>Aventure</option>
                    <option value="relaxation" {{ old('type', $excursion->type) == 'relaxation' ? 'selected' : '' }}>Détente</option>
                    <option value="city_tour" {{ old('type', $excursion->type) == 'city_tour' ? 'selected' : '' }}>Tour de ville</option>
                </select>
                @error('type')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="duration_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée (heures) <span class="text-error-500">*</span></label>
                <input type="number" name="duration_hours" id="duration_hours" value="{{ old('duration_hours', $excursion->duration_hours) }}" required min="1"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('duration_hours')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="departure_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Heure de départ</label>
                <input type="text" name="departure_time" id="departure_time" value="{{ old('departure_time', $excursion->departure_time) }}" placeholder="ex: 09:00"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('departure_time')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price_adult" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix adulte (FCFA) <span class="text-error-500">*</span></label>
                <input type="number" name="price_adult" id="price_adult" value="{{ old('price_adult', $excursion->price_adult) }}" required min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price_adult')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price_child" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix enfant (FCFA)</label>
                <input type="number" name="price_child" id="price_child" value="{{ old('price_child', $excursion->price_child) }}" min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price_child')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="min_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants min <span class="text-error-500">*</span></label>
                <input type="number" name="min_participants" id="min_participants" value="{{ old('min_participants', $excursion->min_participants) }}" required min="1"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('min_participants')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="max_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants max</label>
                <input type="number" name="max_participants" id="max_participants" value="{{ old('max_participants', $excursion->max_participants) }}" min="1"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('max_participants')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-error-500">*</span></label>
                <select name="status" id="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="available" {{ old('status', $excursion->status) == 'available' ? 'selected' : '' }}>Disponible</option>
                    <option value="unavailable" {{ old('status', $excursion->status) == 'unavailable' ? 'selected' : '' }}>Indisponible</option>
                    <option value="seasonal" {{ old('status', $excursion->status) == 'seasonal' ? 'selected' : '' }}>Saisonnier</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $excursion->display_order) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $excursion->is_featured) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="is_featured" class="ml-2 text-sm text-gray-700 dark:text-gray-300">En vedette</label>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $excursion->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="included" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Inclus (un élément par ligne)</label>
                <textarea name="included" id="included" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('included', is_array($excursion->included) ? implode("\n", $excursion->included) : '') }}</textarea>
                @error('included')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="not_included" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Non inclus (un élément par ligne)</label>
                <textarea name="not_included" id="not_included" rows="2" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('not_included', is_array($excursion->not_included) ? implode("\n", $excursion->not_included) : '') }}</textarea>
                @error('not_included')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            @if($excursion->image)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image actuelle</label>
                <img src="{{ asset('storage/' . $excursion->image) }}" alt="{{ $excursion->name }}" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
            </div>
            @endif

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $excursion->image ? 'Changer l\'image' : 'Image' }}</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommandé : 800×600 px, max 30 Mo.</p>
                @error('image')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Mettre à jour</button>
            <a href="{{ route('dashboard.excursions.show', $excursion) }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
