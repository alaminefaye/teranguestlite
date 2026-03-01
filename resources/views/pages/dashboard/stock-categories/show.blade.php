@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard.stock.index') }}" class="hover:text-brand-500">Stocks</a>
            <span>/</span>
            <a href="{{ route('dashboard.stock-categories.index') }}" class="hover:text-brand-500">Catégories</a>
            <span>/</span>
            <span>{{ $category->name }}</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $category->name }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.stock-products.create') }}?category={{ $category->id }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Nouveau produit</a>
        <a href="{{ route('dashboard.stock-categories.edit', $category) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Modifier</a>
    </div>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Produits dans cette catégorie</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_products'] }}</p>
    </div>
    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-sm text-warning-600 dark:text-warning-400">En alerte (sous seuil)</p>
        <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['in_alert'] }}</p>
    </div>
</div>

@if($category->description)
    <p class="mb-6 text-gray-600 dark:text-gray-400">{{ $category->description }}</p>
@endif

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Produits</h2>
    </div>
    @if($category->products->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">SKU</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Seuil min</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($category->products as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $p->name }}
                                @if($p->isBelowMin())
                                    <span class="ml-1 text-warning-600 text-xs">(alerte)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($p->quantity_current, 0, ',', ' ') }} {{ $p->unit_label }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($p->quantity_min, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('dashboard.stock-products.show', $p) }}" class="text-brand-500 hover:underline">Voir</a>
                                <a href="{{ route('dashboard.stock-products.edit', $p) }}" class="text-gray-600 hover:underline ml-2">Modifier</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="p-8 text-center text-gray-500 dark:text-gray-400">Aucun produit. <a href="{{ route('dashboard.stock-products.create') }}?category={{ $category->id }}" class="text-brand-500 hover:underline">Ajouter un produit</a></p>
    @endif
</div>
@endsection
