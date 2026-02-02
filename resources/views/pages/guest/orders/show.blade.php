@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guest.orders.index') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium text-sm mb-2 inline-block">
                ← Retour à mes commandes
            </a>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Commande #{{ $order->order_number }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $order->created_at->format('d F Y à H:i') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Status Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Statut de votre commande</h3>
        
        @php
            $statuses = [
                'pending' => ['name' => 'En attente', 'icon' => '⏱️', 'color' => 'warning'],
                'confirmed' => ['name' => 'Confirmée', 'icon' => '✓', 'color' => 'brand'],
                'preparing' => ['name' => 'Préparation', 'icon' => '👨‍🍳', 'color' => 'primary'],
                'ready' => ['name' => 'Prête', 'icon' => '✅', 'color' => 'success'],
                'delivering' => ['name' => 'En livraison', 'icon' => '🚚', 'color' => 'info'],
                'delivered' => ['name' => 'Livrée', 'icon' => '🎉', 'color' => 'success'],
            ];
            
            $currentStatusIndex = array_search($order->status, array_keys($statuses));
            $isCancelled = $order->status === 'cancelled';
        @endphp

        @if($isCancelled)
            <div class="text-center py-4">
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                    🚫 Commande annulée
                </span>
            </div>
        @else
            <div class="flex items-center justify-between mb-4">
                @foreach($statuses as $key => $status)
                    @php
                        $statusIndex = array_search($key, array_keys($statuses));
                        $isActive = $statusIndex === $currentStatusIndex;
                        $isCompleted = $statusIndex < $currentStatusIndex;
                    @endphp
                    
                    <div class="flex flex-col items-center {{ $loop->last ? '' : 'flex-1' }}">
                        <div class="relative flex items-center justify-center w-12 h-12 rounded-full 
                            {{ $isActive ? 'bg-' . $status['color'] . '-500 text-white animate-pulse' : '' }}
                            {{ $isCompleted ? 'bg-success-500 text-white' : '' }}
                            {{ !$isActive && !$isCompleted ? 'bg-gray-200 dark:bg-gray-700 text-gray-500' : '' }}">
                            <span class="text-lg">{{ $status['icon'] }}</span>
                        </div>
                        <p class="mt-2 text-xs text-center text-gray-600 dark:text-gray-400">{{ $status['name'] }}</p>
                    </div>
                    
                    @if(!$loop->last)
                        <div class="flex-1 h-1 mx-2 
                            {{ $isCompleted ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Temps estimé -->
            @if(in_array($order->status, ['confirmed', 'preparing']))
                <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                    Temps estimé : <span class="font-semibold">20-30 minutes</span>
                </div>
            @endif
        @endif
    </div>

    <!-- Articles commandés -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Articles ({{ $order->orderItems->count() }})</h3>
        
        <div class="space-y-3">
            @foreach($order->orderItems as $item)
                <div class="border-l-4 border-brand-500 pl-4 py-2">
                    <div class="flex justify-between items-start">
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
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">Instructions spéciales</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ $order->special_instructions }}</p>
        </div>
    @endif>

    <!-- Actions -->
    <div class="space-y-3">
        @if($order->status === 'delivered')
            <form action="{{ route('guest.orders.reorder', $order) }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-lg hover:bg-brand-600 active:scale-95 transition-transform font-semibold">
                    Commander à nouveau
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
