@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Commandes</h1>
        <a href="{{ route('dashboard.orders.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nouvelle commande
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>

    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-xs text-warning-600 dark:text-warning-400">En attente</p>
        <p class="text-xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['pending'] }}</p>
    </div>

    <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
        <p class="text-xs text-brand-600 dark:text-brand-400">Confirmées</p>
        <p class="text-xl font-semibold text-brand-700 dark:text-brand-300">{{ $stats['confirmed'] }}</p>
    </div>

    <div class="rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-900/20">
        <p class="text-xs text-primary-600 dark:text-primary-400">Préparation</p>
        <p class="text-xl font-semibold text-primary-700 dark:text-primary-300">{{ $stats['preparing'] }}</p>
    </div>

    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-xs text-success-600 dark:text-success-400">Prêtes</p>
        <p class="text-xl font-semibold text-success-700 dark:text-success-300">{{ $stats['ready'] }}</p>
    </div>

    <div class="rounded-lg border border-info-200 bg-info-50 p-4 dark:border-info-800 dark:bg-info-900/20">
        <p class="text-xs text-info-600 dark:text-info-400">Livraison</p>
        <p class="text-xl font-semibold text-info-700 dark:text-info-300">{{ $stats['delivering'] }}</p>
    </div>

    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-xs text-success-600 dark:text-success-400">Livrées</p>
        <p class="text-xl font-semibold text-success-700 dark:text-success-300">{{ $stats['delivered'] }}</p>
    </div>

    <div class="rounded-lg border border-error-200 bg-error-50 p-4 dark:border-error-800 dark:bg-error-900/20">
        <p class="text-xs text-error-600 dark:text-error-400">Annulées</p>
        <p class="text-xl font-semibold text-error-700 dark:text-error-300">{{ $stats['cancelled'] }}</p>
    </div>
</div>

<!-- Filtres avancés -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <form method="GET" action="{{ route('dashboard.orders.index') }}" class="space-y-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° commande ou nom client..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                <select name="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Préparation</option>
                    <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Prête</option>
                    <option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>Livraison</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrée</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                <select name="type" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Tous</option>
                    <option value="room_service" {{ request('type') === 'room_service' ? 'selected' : '' }}>Room Service</option>
                    <option value="restaurant" {{ request('type') === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="bar" {{ request('type') === 'bar' ? 'selected' : '' }}>Bar</option>
                    <option value="spa" {{ request('type') === 'spa' ? 'selected' : '' }}>Spa</option>
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Paiement</label>
                <select name="payment_method" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Tous</option>
                    <option value="room_bill" {{ request('payment_method') === 'room_bill' ? 'selected' : '' }}>Note chambre</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Espèce</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Carte</option>
                    <option value="wave" {{ request('payment_method') === 'wave' ? 'selected' : '' }}>Wave</option>
                    <option value="orange_money" {{ request('payment_method') === 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Chambre</label>
                <select name="room_id" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Toutes</option>
                    @foreach($rooms ?? [] as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>Ch. {{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <x-form.date-picker name="date_from" label="Du" placeholder="Choisir une date" :defaultDate="request('date_from')" />
            </div>
            <div class="min-w-[160px]">
                <x-form.date-picker name="date_to" label="Au" placeholder="Choisir une date" :defaultDate="request('date_to')" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">Filtrer</button>
                @if(request()->hasAny(['search', 'status', 'type', 'payment_method', 'room_id', 'date_from', 'date_to']))
                    <a href="{{ route('dashboard.orders.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Réinitialiser</a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Liste des commandes -->
<div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">N° Commande</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Chambre</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('dashboard.orders.show', $order) }}" class="font-medium text-brand-600 dark:text-brand-400 hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white/90">
                            {{ $order->type_name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white/90">
                            {{ $order->user?->name ?? $order->guest?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white/90">
                            {{ $order->room?->room_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $order->formatted_total }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                                    'confirmed' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
                                    'preparing' => 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400',
                                    'ready' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                    'delivering' => 'bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400',
                                    'delivered' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                    'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-50 text-gray-600' }}">
                                {{ $order->status_name }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-action-buttons 
                                :showRoute="route('dashboard.orders.show', $order)"
                                :editRoute="in_array($order->status, ['pending', 'confirmed']) ? route('dashboard.orders.edit', $order) : null"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Aucune commande trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($orders->hasPages())
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endif
@endsection
