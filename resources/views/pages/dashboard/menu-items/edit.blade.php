@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.menu-items.index') }}" class="hover:text-brand-500">Articles de menu</a>
        <span>/</span>
        <a href="{{ route('dashboard.menu-items.show', $item) }}" class="hover:text-brand-500">{{ $item->name }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier {{ $item->name }}</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.menu-items.update', $item) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Catégorie -->
            <div class="md:col-span-2">
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Catégorie <span class="text-error-500">*</span>
                </label>
                <select name="category_id" id="category_id" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->type_name }})
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nom -->
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom <span class="text-error-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prix -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Prix (FCFA) <span class="text-error-500">*</span>
                </label>
                <input type="number" name="price" id="price" value="{{ old('price', $item->price) }}" required min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Temps de préparation -->
            <div>
                <label for="preparation_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Temps de préparation (minutes)
                </label>
                <input type="number" name="preparation_time" id="preparation_time" value="{{ old('preparation_time', $item->preparation_time) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('preparation_time')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ordre d'affichage -->
            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ordre d'affichage
                </label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $item->display_order) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options -->
            <div class="flex items-center gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available', $item->is_available) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                    <label for="is_available" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Disponible</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $item->is_featured) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                    <label for="is_featured" class="ml-2 text-sm text-gray-700 dark:text-gray-300">En vedette</label>
                </div>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $item->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image actuelle -->
            @if($item->image)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image actuelle</label>
                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
            </div>
            @endif

            <!-- Nouvelle image -->
            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $item->image ? 'Changer l\'image' : 'Image' }}
                </label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('image')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ingrédients -->
            <div>
                <label for="ingredients_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ingrédients (séparés par des virgules)
                </label>
                <input type="text" id="ingredients_input" value="{{ is_array($item->ingredients) ? implode(', ', $item->ingredients) : '' }}" placeholder="Ex: Tomates, Oignons, Riz"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <input type="hidden" name="ingredients[]" id="ingredients_hidden" value="{{ json_encode($item->ingredients ?? []) }}">
            </div>

            <!-- Allergènes -->
            <div>
                <label for="allergens_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Allergènes (séparés par des virgules)
                </label>
                <input type="text" id="allergens_input" value="{{ is_array($item->allergens) ? implode(', ', $item->allergens) : '' }}" placeholder="Ex: Gluten, Lactose, Arachides"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <input type="hidden" name="allergens[]" id="allergens_hidden" value="{{ json_encode($item->allergens ?? []) }}">
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Mettre à jour
            </button>
            <a href="{{ route('dashboard.menu-items.show', $item) }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
    // Gestion des ingrédients
    document.getElementById('ingredients_input').addEventListener('blur', function() {
        const values = this.value.split(',').map(v => v.trim()).filter(v => v);
        document.getElementById('ingredients_hidden').value = JSON.stringify(values);
    });

    // Gestion des allergènes
    document.getElementById('allergens_input').addEventListener('blur', function() {
        const values = this.value.split(',').map(v => v.trim()).filter(v => v);
        document.getElementById('allergens_hidden').value = JSON.stringify(values);
    });
</script>
@endsection
