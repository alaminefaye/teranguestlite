@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.menu-items.index') }}" class="hover:text-brand-500">Articles de menu</a>
        <span>/</span>
        <span>Créer un article</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer un nouvel article</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.menu-items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Catégorie -->
            <div class="md:col-span-2">
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Catégorie <span class="text-error-500">*</span>
                </label>
                <select name="category_id" id="category_id" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner une catégorie</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', request('category_id')) == $cat->id ? 'selected' : '' }}>
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
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="0.01"
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
                <input type="number" name="preparation_time" id="preparation_time" value="{{ old('preparation_time') }}" min="0"
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
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Lien stock (mise à jour à chaque commande livrée) -->
            <div class="md:col-span-2 rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800/50">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Stock (optionnel)</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Si vous liez un produit stock, le stock sera automatiquement déduit à chaque livraison de commande contenant cet article.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stock_product_id" class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Produit stock</label>
                        <select name="stock_product_id" id="stock_product_id" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                            <option value="">Aucun (pas de déduction stock)</option>
                            @foreach($stockProducts ?? [] as $sp)
                                <option value="{{ $sp->id }}" {{ old('stock_product_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }} ({{ $sp->category?->name ?? '' }}) — {{ number_format($sp->quantity_current, 0, ',', ' ') }} {{ $sp->unit_label }}</option>
                            @endforeach
                        </select>
                        @error('stock_product_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="stock_quantity_per_portion" class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Quantité consommée par vente</label>
                        <input type="number" name="stock_quantity_per_portion" id="stock_quantity_per_portion" value="{{ old('stock_quantity_per_portion', 1) }}" min="0.001" step="any" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90" placeholder="1">
                        <p class="mt-1 text-xs text-gray-500">Ex: 1 = 1 unité par article vendu, 2 = 2 unités (ex. carafe = 2 bouteilles)</p>
                        @error('stock_quantity_per_portion')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="flex items-center gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                    <label for="is_available" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Disponible</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
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
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image -->
            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Image
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
                <input type="text" id="ingredients_input" placeholder="Ex: Tomates, Oignons, Riz"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <input type="hidden" name="ingredients[]" id="ingredients_hidden">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tapez les ingrédients séparés par des virgules</p>
            </div>

            <!-- Allergènes -->
            <div>
                <label for="allergens_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Allergènes (séparés par des virgules)
                </label>
                <input type="text" id="allergens_input" placeholder="Ex: Gluten, Lactose, Arachides"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <input type="hidden" name="allergens[]" id="allergens_hidden">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tapez les allergènes séparés par des virgules</p>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Créer l'article
            </button>
            <a href="{{ route('dashboard.menu-items.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
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
