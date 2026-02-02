@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('reservations.show', $payment->booking) }}" 
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

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Statut du Paiement</h3>
                
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'bg-warning-50 text-warning-600', 'icon' => '⏳', 'label' => 'En attente'],
                        'processing' => ['color' => 'bg-blue-light-50 text-blue-light-600', 'icon' => '🔄', 'label' => 'En traitement'],
                        'completed' => ['color' => 'bg-success-50 text-success-600', 'icon' => '✅', 'label' => 'Complété'],
                        'failed' => ['color' => 'bg-error-50 text-error-600', 'icon' => '❌', 'label' => 'Échoué'],
                        'cancelled' => ['color' => 'bg-gray-50 text-gray-600', 'icon' => '🚫', 'label' => 'Annulé'],
                        'refunded' => ['color' => 'bg-gray-50 text-gray-600', 'icon' => '↩️', 'label' => 'Remboursé'],
                    ];
                    $currentStatus = $statusConfig[$payment->status] ?? $statusConfig['pending'];
                @endphp

                <div class="mb-6 text-center">
                    <div class="mb-2 text-6xl">{{ $currentStatus['icon'] }}</div>
                    <span class="inline-flex items-center rounded-full px-4 py-2 text-base font-medium {{ $currentStatus['color'] }}">
                        {{ $currentStatus['label'] }}
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Méthode de paiement</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            @php
                                $methodLabels = [
                                    'orange_money' => 'Orange Money',
                                    'free_money' => 'Free Money',
                                    'wave' => 'Wave',
                                    'cash' => 'Espèces',
                                    'card' => 'Carte bancaire',
                                ];
                            @endphp
                            {{ $methodLabels[$payment->method] ?? $payment->method }}
                        </span>
                    </div>

                    @if($payment->phone_number)
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Numéro de téléphone</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->phone_number }}</span>
                        </div>
                    @endif

                    @if($payment->transaction_id)
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">ID de transaction</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->transaction_id }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Montant</span>
                        <span class="text-lg font-bold text-brand-500">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Référence de paiement</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->payment_reference }}</span>
                    </div>

                    @if($payment->paid_at)
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Date de paiement</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif

                    @if($payment->notes)
                        <div class="border-b border-gray-200 pb-3 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Notes</span>
                            <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $payment->notes }}</p>
                        </div>
                    @endif
                </div>

                @if($payment->status === 'pending' && auth()->check() && auth()->user()->role === 'admin')
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <form action="{{ route('payments.cancel', $payment) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce paiement ?');">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                Annuler le paiement
                            </button>
                        </form>
                        <form action="{{ route('payments.confirm', $payment) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                Confirmer le paiement
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informations de la réservation -->
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Réservation</h3>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->booking->booking_reference }}</p>
                    @if($payment->booking->schedule && $payment->booking->schedule->route)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $payment->booking->schedule->route->departureStation->name ?? 'N/A' }} → 
                            {{ $payment->booking->schedule->route->arrivalStation->name ?? 'N/A' }}
                        </p>
                    @endif
                    <a href="{{ route('reservations.show', $payment->booking) }}" 
                       class="mt-3 inline-flex items-center text-sm text-brand-500 hover:text-brand-600">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
