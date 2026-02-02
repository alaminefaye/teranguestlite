@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.show', $rental) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Vérification du Paiement</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Référence: {{ $payment->payment_reference }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Statut du Paiement</h3>
                
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'bg-warning-50 text-warning-600', 'icon' => '⏳', 'label' => 'En attente'],
                        'processing' => ['color' => 'bg-blue-50 text-blue-600', 'icon' => '🔄', 'label' => 'En traitement'],
                        'completed' => ['color' => 'bg-success-50 text-success-600', 'icon' => '✅', 'label' => 'Complété'],
                        'failed' => ['color' => 'bg-error-50 text-error-600', 'icon' => '❌', 'label' => 'Échoué'],
                        'cancelled' => ['color' => 'bg-gray-50 text-gray-600', 'icon' => '🚫', 'label' => 'Annulé'],
                        'refunded' => ['color' => 'bg-purple-50 text-purple-600', 'icon' => '↩️', 'label' => 'Remboursé'],
                    ];
                    $config = $statusConfig[$payment->status] ?? $statusConfig['pending'];
                @endphp

                <div class="space-y-4">
                    <div class="rounded-lg {{ $config['color'] }} p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $config['icon'] }}</span>
                            <div>
                                <div class="font-semibold">{{ $config['label'] }}</div>
                                <div class="text-sm opacity-75">Statut actuel du paiement</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Référence</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->payment_reference }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Montant</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Méthode</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                @php
                                    $methods = [
                                        'orange_money' => 'Orange Money',
                                        'free_money' => 'Free Money',
                                        'wave' => 'Wave',
                                        'cash' => 'Espèces',
                                        'card' => 'Carte bancaire',
                                    ];
                                @endphp
                                {{ $methods[$payment->method] ?? $payment->method }}
                            </span>
                        </div>
                        @if($payment->transaction_id)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">ID Transaction</span>
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->transaction_id }}</span>
                            </div>
                        @endif
                        @if($payment->phone_number)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Téléphone</span>
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->phone_number }}</span>
                            </div>
                        @endif
                        @if($payment->paid_at)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Date de paiement</span>
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Date de création</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    @if($payment->notes)
                        <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes</div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $payment->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations de la Location</h3>
                <div class="space-y-2">
                    <div class="text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Véhicule:</span>
                        <span class="ml-2 font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->name }}</span>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Période:</span>
                        <span class="ml-2 font-medium text-gray-800 dark:text-white/90">
                            {{ $rental->start_date->format('d/m/Y') }} - {{ $rental->end_date->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <a href="{{ route('rentals.show', $rental) }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Retour à la location
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
