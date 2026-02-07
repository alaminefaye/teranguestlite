@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.orders.index') }}" class="hover:text-brand-500">Commandes</a>
        <span>/</span>
        <span>{{ $order->order_number }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Commande #{{ $order->order_number }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $order->type_name }} - {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if(in_array($order->status, ['pending', 'confirmed']))
                <a href="{{ route('dashboard.orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
            @endif
        </div>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informations principales -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Workflow de statuts -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Statut de la commande</h3>
            
            @php
                $statuses = [
                    'pending' => ['name' => 'En attente', 'icon' => '⏱️', 'color' => 'warning', 'route' => null],
                    'confirmed' => ['name' => 'Confirmée', 'icon' => '✓', 'color' => 'brand', 'route' => 'dashboard.orders.confirm'],
                    'preparing' => ['name' => 'Préparation', 'icon' => '👨‍🍳', 'color' => 'primary', 'route' => 'dashboard.orders.prepare'],
                    'ready' => ['name' => 'Prête', 'icon' => '✅', 'color' => 'success', 'route' => 'dashboard.orders.ready'],
                    'delivering' => ['name' => 'Livraison', 'icon' => '🚚', 'color' => 'info', 'route' => 'dashboard.orders.deliver'],
                    'delivered' => ['name' => 'Livrée', 'icon' => '🎉', 'color' => 'success', 'route' => 'dashboard.orders.complete'],
                ];
                $statusKeys = array_keys($statuses);
                $currentStatusIndex = array_search($order->status, $statusKeys);
                $isCancelled = $order->status === 'cancelled';
            @endphp

            @if($isCancelled)
                <div class="text-center py-4">
                    <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                        🚫 Commande annulée
                    </span>
                </div>
            @else
                <div class="flex items-center justify-between gap-1 mb-2">
                    @foreach($statuses as $key => $status)
                        @php
                            $statusIndex = array_search($key, $statusKeys);
                            $isActive = $statusIndex === $currentStatusIndex;
                            $isCompleted = $statusIndex < $currentStatusIndex;
                            $isNextStep = $currentStatusIndex !== false && $statusIndex === $currentStatusIndex + 1;
                            $showButton = $status['route'] && $isNextStep;
                        @endphp
                        <div class="flex flex-col items-center flex-1 min-w-0">
                            <div class="relative flex items-center justify-center w-12 h-12 rounded-full shrink-0
                                {{ $isActive ? 'bg-' . $status['color'] . '-500 text-white ring-2 ring-offset-2 ring-' . $status['color'] . '-300' : '' }}
                                {{ $isCompleted ? 'bg-success-500 text-white' : '' }}
                                {{ !$isActive && !$isCompleted ? 'bg-gray-200 dark:bg-gray-700 text-gray-500' : '' }}">
                                <span class="text-lg">{{ $status['icon'] }}</span>
                            </div>
                            <p class="mt-2 text-xs text-center text-gray-600 dark:text-gray-400 font-medium">{{ $status['name'] }}</p>
                            @if($showButton)
                                <form action="{{ route($status['route'], $order) }}" method="POST" class="mt-2 w-full max-w-[120px]">
                                    @csrf
                                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-medium rounded-md bg-brand-500 text-white hover:bg-brand-600 transition-colors">
                                        Passer à cette étape
                                    </button>
                                </form>
                            @elseif($isCompleted)
                                <p class="mt-2 text-xs text-success-600 dark:text-success-400">✓ Fait</p>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-1 mx-0.5 min-w-[8px] self-start mt-6
                                {{ $isCompleted ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <!-- Bouton Annuler -->
            @if(!$isCancelled && !in_array($order->status, ['delivered']))
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <form action="{{ route('dashboard.orders.cancel', $order) }}" method="POST" class="inline"
                        onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-error-500 text-white rounded-md hover:bg-error-600 text-sm font-medium">
                            🚫 Annuler la commande
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Articles commandés -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Articles ({{ $order->orderItems->count() }})</h3>
            
            <div class="space-y-3">
                @foreach($order->orderItems as $item)
                    <div class="border-l-4 border-brand-500 pl-4 py-2">
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $item->item_name }}</p>
                                @if($item->item_description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->item_description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item->quantity }} × {{ $item->formatted_unit_price }}</p>
                                <p class="font-semibold text-gray-800 dark:text-white/90">{{ $item->formatted_total_price }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Totaux -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Sous-total:</span>
                    <span class="text-gray-800 dark:text-white/90">{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">TVA (18%):</span>
                    <span class="text-gray-800 dark:text-white/90">{{ number_format($order->tax, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($order->delivery_fee > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Frais de livraison:</span>
                        <span class="text-gray-800 dark:text-white/90">{{ number_format($order->delivery_fee, 0, ',', ' ') }} FCFA</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-semibold pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-800 dark:text-white/90">Total:</span>
                    <span class="text-brand-600 dark:text-brand-400">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        <!-- Instructions spéciales -->
        @if($order->special_instructions)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">Instructions spéciales</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $order->special_instructions }}</p>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Informations -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations</h3>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Client</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $order->user?->name ?? $order->guest?->name ?? '—' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->user?->email ?? $order->guest?->email ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Chambre</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $order->room?->room_number ?? '—' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->room?->type_name ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $order->type_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Date de création</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>

                @if($order->confirmed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Confirmée le</label>
                        <p class="text-gray-800 dark:text-white/90">{{ $order->confirmed_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif

                @if($order->delivered_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Livrée le</label>
                        <p class="text-gray-800 dark:text-white/90">{{ $order->delivered_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Actions</h3>
            <div class="space-y-3">
                @if(in_array($order->status, ['pending', 'confirmed']))
                    <a href="{{ route('dashboard.orders.edit', $order) }}" class="block w-full px-4 py-2 text-center bg-brand-500 text-white rounded-md hover:bg-brand-600">
                        Modifier la commande
                    </a>
                @endif
                <a href="{{ route('dashboard.orders.index') }}" class="block w-full px-4 py-2 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Retour à la liste
                </a>
                @if(in_array($order->status, ['pending', 'cancelled']))
                    <form action="{{ route('dashboard.orders.destroy', $order) }}" method="POST" 
                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full px-4 py-2 bg-error-500 text-white rounded-md hover:bg-error-600">
                            Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
