@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.stock.index') }}" class="hover:text-brand-500">Stocks</a>
        <span>/</span>
        <a href="{{ route('dashboard.stock-products.index') }}" class="hover:text-brand-500">Produits</a>
        <span>/</span>
        <span>Créer</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Nouveau produit</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.stock-products.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="stock_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie <span class="text-error-500">*</span></label>
                <select name="stock_category_id" id="stock_category_id" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="">Choisir une catégorie</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('stock_category_id', $preselectedCategoryId ?? null) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('stock_category_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('name')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SKU / Code</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('sku')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code-barres</label>
                <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('barcode')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unité <span class="text-error-500">*</span></label>
                <select name="unit" id="unit" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    @foreach(\App\Models\StockProduct::UNITS as $value => $label)
                        <option value="{{ $value }}" {{ old('unit', 'piece') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('unit')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emplacement</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}" placeholder="Entrepôt, rayon..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('location')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quantity_current" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock initial</label>
                <input type="number" name="quantity_current" id="quantity_current" value="{{ old('quantity_current', 0) }}" min="0" step="any" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('quantity_current')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quantity_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seuil minimum (alerte)</label>
                <input type="number" name="quantity_min" id="quantity_min" value="{{ old('quantity_min', 0) }}" min="0" step="any" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('quantity_min')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quantity_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seuil maximum (optionnel)</label>
                <input type="number" name="quantity_max" id="quantity_max" value="{{ old('quantity_max') }}" min="0" step="any" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('quantity_max')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unit_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coût unitaire (FCFA)</label>
                <input type="number" name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('unit_cost')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Produit actif</span>
                </label>
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer</button>
            <a href="{{ route('dashboard.stock-products.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
