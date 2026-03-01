@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.stock.index') }}" class="hover:text-brand-500">Stocks</a>
        <span>/</span>
        <a href="{{ route('dashboard.stock-movements.index') }}" class="hover:text-brand-500">Mouvements</a>
        <span>/</span>
        <span>Nouveau</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Nouveau mouvement de stock</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.stock-movements.store') }}" method="POST" id="movement-form">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="stock_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Produit <span class="text-error-500">*</span></label>
                <select name="stock_product_id" id="stock_product_id" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="">Choisir un produit</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-unit="{{ $p->unit_label }}" data-stock="{{ $p->quantity_current }}" {{ old('stock_product_id', $preselectedProductId) == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->category?->name ?? '' }}) — Stock: {{ number_format($p->quantity_current, 0, ',', ' ') }} {{ $p->unit_label }}</option>
                    @endforeach
                </select>
                @error('stock_product_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span class="text-error-500">*</span></label>
                <select name="type" id="type" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Entrée (réception)</option>
                    <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Sortie (consommation)</option>
                    <option value="adjustment" {{ old('type') === 'adjustment' ? 'selected' : '' }}>Ajustement (inventaire)</option>
                </select>
                @error('type')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div id="adjustment-direction-wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sens de l'ajustement</label>
                <select name="adjustment_direction" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="add" {{ old('adjustment_direction', 'add') === 'add' ? 'selected' : '' }}>Ajouter au stock</option>
                    <option value="subtract" {{ old('adjustment_direction') === 'subtract' ? 'selected' : '' }}>Retirer du stock</option>
                </select>
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantité <span class="text-error-500">*</span></label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="0.001" step="any" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unité : <span id="unit-label">—</span></p>
                @error('quantity')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unit_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coût unitaire (FCFA, optionnel)</label>
                <input type="number" name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('unit_cost')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90" placeholder="Référence livraison, motif ajustement...">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer</button>
            <a href="{{ route('dashboard.stock-movements.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('type');
    var productSelect = document.getElementById('stock_product_id');
    var adjustmentWrap = document.getElementById('adjustment-direction-wrap');
    var unitLabel = document.getElementById('unit-label');

    function updateUnitLabel() {
        var opt = productSelect.options[productSelect.selectedIndex];
        if (opt && opt.value) {
            unitLabel.textContent = opt.getAttribute('data-unit') || '—';
        } else {
            unitLabel.textContent = '—';
        }
    }

    function toggleAdjustment() {
        if (typeSelect.value === 'adjustment') {
            adjustmentWrap.classList.remove('hidden');
        } else {
            adjustmentWrap.classList.add('hidden');
        }
    }

    typeSelect.addEventListener('change', toggleAdjustment);
    productSelect.addEventListener('change', updateUnitLabel);
    toggleAdjustment();
    updateUnitLabel();
});
</script>
@endsection
