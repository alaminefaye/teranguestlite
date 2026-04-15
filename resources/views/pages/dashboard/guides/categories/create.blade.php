@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ route('dashboard.guide-categories.index') }}" class="hover:text-brand-500">Guides & Infos</a>
            <span>/</span>
            <span>Créer</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Nouvelle catégorie</h1>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('dashboard.guide-categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('name')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                    <select name="category_type"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                        <option value="">—</option>
                        <option value="equipment_guide" {{ old('category_type') === 'equipment_guide' ? 'selected' : '' }}>Guide équipements</option>
                        <option value="useful_numbers" {{ old('category_type') === 'useful_numbers' ? 'selected' : '' }}>Numéros utiles</option>
                        <option value="other" {{ old('category_type') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('category_type')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    @error('order')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    @error('image')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" class="rounded border-gray-300 dark:border-gray-700" checked>
                        Actif
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard.guide-categories.index') }}"
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

