@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Gestion des stocks</h1>
        <p class="text-gray-600 dark:text-gray-400">Vue d'ensemble, alertes seuils et derniers mouvements</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.stock-products.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Produits</a>
        <a href="{{ route('dashboard.stock-movements.create') }}" class="inline-flex items-center px-4 py-2 bg-success-600 text-white rounded-md hover:bg-success-700">Nouveau mouvement</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Catégories</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['categories_count'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Produits</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['products_count'] }}</p>
    </div>
    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-sm text-warning-600 dark:text-warning-400">Alertes (seuil)</p>
        <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['products_in_alert'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Valeur stock (FCFA)</p>
        <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['total_value'], 0, ',', ' ') }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Mouvements aujourd'hui</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['movements_today'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Alertes seuils -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Alertes stock (sous seuil minimum)</h2>
            @if($alerts->count() > 0)
                <a href="{{ route('dashboard.stock-products.index', ['alert' => 'yes']) }}" class="text-sm text-brand-500 hover:underline">Voir tout</a>
            @endif
        </div>
        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
            @if($alerts->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-500 dark:text-gray-400">Produit</th>
                            <th class="px-4 py-2 text-left text-gray-500 dark:text-gray-400">Catégorie</th>
                            <th class="px-4 py-2 text-right text-gray-500 dark:text-gray-400">Actuel</th>
                            <th class="px-4 py-2 text-right text-gray-500 dark:text-gray-400">Seuil min</th>
                            <th class="px-4 py-2 w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($alerts as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2 font-medium text-gray-800 dark:text-white/90">{{ $p->name }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $p->category?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <span class="{{ $p->quantity_current <= 0 ? 'text-error-600 font-semibold' : 'text-warning-600' }}">
                                        {{ number_format($p->quantity_current, 0, ',', ' ') }} {{ $p->unit_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($p->quantity_min, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('dashboard.stock-movements.create', ['product_id' => $p->id]) }}" class="text-brand-500 hover:underline text-xs">Entrée</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="p-6 text-center text-gray-500 dark:text-gray-400">Aucune alerte. Tous les stocks sont au-dessus du seuil minimum.</p>
            @endif
        </div>
    </div>

    <!-- Derniers mouvements -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Derniers mouvements</h2>
            <a href="{{ route('dashboard.stock-movements.index') }}" class="text-sm text-brand-500 hover:underline">Tous</a>
        </div>
        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
            @if($recentMovements->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-4 py-2 text-left text-gray-500 dark:text-gray-400">Produit</th>
                            <th class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-4 py-2 text-right text-gray-500 dark:text-gray-400">Quantité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentMovements as $m)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 font-medium text-gray-800 dark:text-white/90">{{ $m->product?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($m->type === 'in')
                                        <span class="text-success-600">Entrée</span>
                                    @elseif($m->type === 'out')
                                        <span class="text-error-600">Sortie</span>
                                    @else
                                        <span class="text-gray-600">Ajust.</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">{{ $m->type === 'out' ? '-' : '' }}{{ number_format($m->quantity, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="p-6 text-center text-gray-500 dark:text-gray-400">Aucun mouvement récent.</p>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-4">
    <a href="{{ route('dashboard.stock-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Catégories de stock</a>
    <a href="{{ route('dashboard.stock-products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Produits</a>
    <a href="{{ route('dashboard.stock-movements.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Mouvements</a>
</div>
@endsection
