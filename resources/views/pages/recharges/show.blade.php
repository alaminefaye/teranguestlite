@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('recharges.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Détails de la Recharge</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Référence: {{ $recharge->transaction_reference }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations de la recharge -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations de la Recharge</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Référence</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $recharge->transaction_reference }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Montant</p>
                        <p class="mt-1 text-lg font-semibold text-success-600 dark:text-success-400">
                            +{{ number_format($recharge->amount, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Solde avant</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ number_format($recharge->balance_before, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Solde après</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ number_format($recharge->balance_after, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Méthode de paiement</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $recharge->payment_method ? ucfirst(str_replace('_', ' ', $recharge->payment_method)) : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Statut</p>
                        <p class="mt-1">
                            @if($recharge->status === 'completed')
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                    Complétée
                                </span>
                            @elseif($recharge->status === 'pending')
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                                    En attente
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                    Échouée
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($recharge->description)
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Description</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $recharge->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Informations client -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Client</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <span class="text-sm font-semibold">{{ strtoupper(substr($recharge->user->name, 0, 2)) }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $recharge->user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $recharge->user->email }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Solde actuel</p>
                    <p class="mt-1 text-lg font-semibold {{ ($recharge->user->balance ?? 0) > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}">
                        {{ number_format($recharge->user->balance ?? 0, 0, ',', ' ') }} FCFA
                    </p>
                </div>
            </div>

            <!-- Informations -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <p><strong>Date:</strong> {{ $recharge->created_at->format('d/m/Y H:i') }}</p>
                    @if($recharge->completed_at)
                        <p><strong>Complétée le:</strong> {{ $recharge->completed_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
