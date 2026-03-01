@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard.stock.index') }}" class="hover:text-brand-500">Stocks</a>
            <span>/</span>
            <a href="{{ route('dashboard.stock-products.index') }}" class="hover:text-brand-500">Produits</a>
            <span>/</span>
            <span>{{ $product->name }}</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $product->name }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.stock-movements.create', ['product_id' => $product->id]) }}" class="inline-flex items-center px-4 py-2 bg-success-600 text-white rounded-md hover:bg-success-700">Nouveau mouvement</a>
        <a href="{{ route('dashboard.stock-products.edit', $product) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Modifier</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Stock actuel</p>
        <p class="text-2xl font-semibold {{ $product->isBelowMin() ? 'text-warning-600' : 'text-gray-800 dark:text-white/90' }}">{{ number_format($product->quantity_current, 0, ',', ' ') }} {{ $product->unit_label }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Seuil minimum</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($product->quantity_min, 0, ',', ' ') }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Catégorie</p>
        <p class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $product->category?->name ?? '—' }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Coût unitaire</p>
        <p class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $product->unit_cost ? number_format($product->unit_cost, 0, ',', ' ') . ' FCFA' : '—' }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Valeur stock</p>
        <p class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $product->unit_cost ? number_format($product->quantity_current * $product->unit_cost, 0, ',', ' ') . ' FCFA' : '—' }}</p>
    </div>
</div>

@if($product->isBelowMin())
    <div class="mb-6 rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-warning-700 dark:text-warning-300 font-medium">Alerte : le stock est en dessous du seuil minimum. Pensez à réapprovisionner.</p>
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Historique des mouvements</h2>
        <a href="{{ route('dashboard.stock-movements.create', ['product_id' => $product->id]) }}" class="text-sm text-brand-500 hover:underline">+ Mouvement</a>
    </div>
    @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">Quantité</th>
                        <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400">Par</th>
                        <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($movements as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $m->type_label }}</td>
                            <td class="px-4 py-3 text-right {{ $m->type === 'out' ? 'text-error-600' : 'text-success-600' }}">{{ $m->type === 'out' ? '-' : '+' }}{{ number_format($m->quantity, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $m->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $m->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="p-6 text-center text-gray-500 dark:text-gray-400">Aucun mouvement. <a href="{{ route('dashboard.stock-movements.create', ['product_id' => $product->id]) }}" class="text-brand-500 hover:underline">Enregistrer une entrée ou sortie</a></p>
    @endif
</div>
@endsection
