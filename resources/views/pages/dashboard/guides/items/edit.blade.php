@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ route('dashboard.guide-categories.index') }}" class="hover:text-brand-500">Guides & Infos</a>
            <span>/</span>
            <a href="{{ route('dashboard.guide-items.index', ['category' => $item->guide_category_id]) }}" class="hover:text-brand-500">Éléments</a>
            <span>/</span>
            <span>Modifier</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier {{ $item->title }}</h1>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('dashboard.guide-items.update', $item) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Catégorie <span class="text-error-500">*</span>
                    </label>
                    <select name="guide_category_id" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('guide_category_id', $item->guide_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Titre <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $item->title) }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">{{ old('description', $item->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $item->phone) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre</label>
                    <input type="number" name="order" value="{{ old('order', $item->order) }}" min="0"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $item->address) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $item->latitude) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $item->longitude) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                    @if($item->image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($item->image) }}" alt="Image"
                                class="h-24 w-auto object-cover rounded-md border border-gray-200 dark:border-gray-700">
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-3">
                            <input type="checkbox" name="remove_image" class="rounded border-gray-300 dark:border-gray-700">
                            Retirer l'image
                        </label>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" class="rounded border-gray-300 dark:border-gray-700"
                            {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        Actif
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard.guide-items.index', ['category' => $item->guide_category_id]) }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Annuler
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
@endsection

