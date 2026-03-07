@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ route('admin.guide-categories.index') }}" class="hover:text-brand-500">Guides & Infos</a>
            <span>/</span>
            <a href="{{ route('admin.guide-items.index', ['category' => $guideItem->guide_category_id]) }}"
                class="hover:text-brand-500">Éléments</a>
            <span>/</span>
            <span>Modifier</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier: {{ $guideItem->title }}</h1>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.guide-items.update', $guideItem) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Titre --}}
                <div class="md:col-span-1">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Titre
                        *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $guideItem->title) }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('title')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Catégorie --}}
                <div class="md:col-span-1">
                    <label for="guide_category_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie *</label>
                    <select name="guide_category_id" id="guide_category_id" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('guide_category_id', $guideItem->guide_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('guide_category_id')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $guideItem->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div>
                    <label for="phone"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $guideItem->phone) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('phone')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Adresse --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse /
                        Lieu</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $guideItem->address) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('address')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Latitude --}}
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitude
                        (GPS)</label>
                    <input type="number" step="any" name="latitude" id="latitude"
                        value="{{ old('latitude', $guideItem->latitude) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('latitude')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Longitude --}}
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitude
                        (GPS)</label>
                    <input type="number" step="any" name="longitude" id="longitude"
                        value="{{ old('longitude', $guideItem->longitude) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('longitude')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Image --}}
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image
                        (optionnelle)</label>
                    @if($guideItem->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $guideItem->image) }}" alt="Image"
                                class="h-16 w-16 object-cover rounded-md">
                            <div class="mt-1">
                                <label class="inline-flex items-center text-sm text-error-600">
                                    <input type="checkbox" name="remove_image" value="1"
                                        class="mr-2 rounded border-gray-300 text-error-600 focus:ring-error-500">
                                    Supprimer l'image actuelle
                                </label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nouvelle image (remplacera l'ancienne si
                        présente).</p>
                    @error('image')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Ordre --}}
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre
                        d'affichage</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $guideItem->order) }}" min="0"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                {{-- Actif --}}
                <div class="flex items-center gap-2 md:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $guideItem->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Élément actif</label>
                </div>

            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Mettre
                    à jour</button>
                <a href="{{ route('admin.guide-items.index', ['category' => $guideItem->guide_category_id]) }}"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
            </div>
        </form>
    </div>
@endsection