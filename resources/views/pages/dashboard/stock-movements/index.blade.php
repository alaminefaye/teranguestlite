@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Mouvements de stock</h1>
        <p class="text-gray-600 dark:text-gray-400">Entrées, sorties et ajustements</p>
    </div>
    <a href="{{ route('dashboard.stock-movements.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Nouveau mouvement</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres</p>
    <form method="GET" action="{{ route('dashboard.stock-movements.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Produit</label>
            <select name="product_id" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <option value="">Tous</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku ?? $p->id }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
            <select name="type" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[120px]">
                <option value="">Tous</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Entrée</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Sortie</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajustement</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Du</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Au</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request()->hasAny(['product_id', 'type', 'date_from', 'date_to']))
            <a href="{{ route('dashboard.stock-movements.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Produit</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quantité</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($movements as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">{{ $m->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($m->type === 'in')
                                    <span class="text-success-600">Entrée</span>
                                @elseif($m->type === 'out')
                                    <span class="text-error-600">Sortie</span>
                                @else
                                    <span class="text-gray-600">Ajust.</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right {{ $m->type === 'out' ? 'text-error-600' : 'text-success-600' }}">{{ $m->type === 'out' ? '-' : ($m->type === 'adjustment' && $m->quantity < 0 ? '-' : '') }}{{ number_format(abs((float)$m->quantity), 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $m->user?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">{{ $movements->links() }}</div>
    @else
        <p class="p-8 text-center text-gray-500 dark:text-gray-400">Aucun mouvement. <a href="{{ route('dashboard.stock-movements.create') }}" class="text-brand-500 hover:underline">Enregistrer un mouvement</a></p>
    @endif
</div>
@endsection
