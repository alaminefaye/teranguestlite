@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Produits / Stock</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestion des articles et seuils d'alerte</p>
    </div>
    <a href="{{ route('dashboard.stock-products.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Nouveau produit</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total produits</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Actifs</p>
        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['active'] }}</p>
    </div>
    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-sm text-warning-600 dark:text-warning-400">En alerte</p>
        <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['in_alert'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Valeur stock (FCFA)</p>
        <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['total_value'], 0, ',', ' ') }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres</p>
    <form method="GET" action="{{ route('dashboard.stock-products.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche (nom, SKU, code-barres)</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catégorie</label>
            <select name="category" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[180px]">
                <option value="">Toutes</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Alerte</label>
            <select name="alert" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[120px]">
                <option value="">Tous</option>
                <option value="yes" {{ request('alert') === 'yes' ? 'selected' : '' }}>En alerte</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
            <select name="is_active" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[100px]">
                <option value="">Tous</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request()->hasAny(['search', 'category', 'alert', 'is_active']))
            <a href="{{ route('dashboard.stock-products.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    @if($products->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Produit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Catégorie</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">SKU</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Seuil min</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Valeur</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($products as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $p->name }}
                                @if($p->isBelowMin())
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400">Alerte</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($p->quantity_current <= 0)
                                    <span class="text-error-600 font-medium">{{ number_format($p->quantity_current, 0, ',', ' ') }}</span>
                                @elseif($p->isBelowMin())
                                    <span class="text-warning-600">{{ number_format($p->quantity_current, 0, ',', ' ') }}</span>
                                @else
                                    {{ number_format($p->quantity_current, 0, ',', ' ') }}
                                @endif
                                {{ $p->unit_label }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ number_format($p->quantity_min, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                @if($p->unit_cost)
                                    {{ number_format($p->quantity_current * $p->unit_cost, 0, ',', ' ') }} F
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-buttons
                                        :showRoute="route('dashboard.stock-products.show', $p)"
                                        :editRoute="route('dashboard.stock-products.edit', $p)"
                                        :canDelete="false"
                                    />
                                    <a href="{{ route('dashboard.stock-movements.create', ['product_id' => $p->id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400 dark:hover:bg-success-500/20 transition-colors" title="Mouvement">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">{{ $products->links() }}</div>
    @else
        <p class="p-8 text-center text-gray-500 dark:text-gray-400">Aucun produit. <a href="{{ route('dashboard.stock-products.create') }}" class="text-brand-500 hover:underline">Créer un produit</a></p>
    @endif
</div>
@endsection
