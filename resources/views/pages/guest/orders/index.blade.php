@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Mes Commandes</h2>
        <p class="text-gray-600 dark:text-gray-400">Suivez l'état de vos commandes</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-warning-50 dark:bg-warning-900/20 rounded-xl p-4 shadow-sm border border-warning-200 dark:border-warning-800">
            <p class="text-sm text-warning-600 dark:text-warning-400">En attente</p>
            <p class="text-2xl font-bold text-warning-700 dark:text-warning-300">{{ $stats['pending'] }}</p>
        </div>

        <div class="bg-brand-50 dark:bg-brand-900/20 rounded-xl p-4 shadow-sm border border-brand-200 dark:border-brand-800">
            <p class="text-sm text-brand-600 dark:text-brand-400">En cours</p>
            <p class="text-2xl font-bold text-brand-700 dark:text-brand-300">{{ $stats['in_progress'] }}</p>
        </div>

        <div class="bg-success-50 dark:bg-success-900/20 rounded-xl p-4 shadow-sm border border-success-200 dark:border-success-800">
            <p class="text-sm text-success-600 dark:text-success-400">Livrées</p>
            <p class="text-2xl font-bold text-success-700 dark:text-success-300">{{ $stats['delivered'] }}</p>
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <a href="{{ route('guest.orders.show', $order) }}" class="tablet-card block bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white/90">Commande #{{ $order->order_number }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
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
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ $order->status_name }}
                    </span>
                </div>

                <!-- Items Preview -->
                <div class="mb-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $order->orderItems->count() }} article(s)</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($order->orderItems->take(3) as $item)
                            <span class="inline-flex items-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded">
                                {{ $item->quantity }}x {{ $item->item_name }}
                            </span>
                        @endforeach
                        @if($order->orderItems->count() > 3)
                            <span class="inline-flex items-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded">
                                +{{ $order->orderItems->count() - 3 }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total</span>
                    <span class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $order->formatted_total }}</span>
                </div>
            </a>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Vous n'avez pas encore passé de commande</p>
                <a href="{{ route('guest.room-service.index') }}" class="inline-flex items-center px-6 py-3 bg-brand-500 text-white rounded-lg hover:bg-brand-600">
                    Commander maintenant
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
