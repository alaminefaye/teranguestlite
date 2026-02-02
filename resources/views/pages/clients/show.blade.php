@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $client->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails et historique du client</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations du client -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informations du Client</h3>
                    <a href="{{ route('recharges.create', ['client_id' => $client->id]) }}" 
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Recharger le compte
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nom complet</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $client->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $client->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Téléphone</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $client->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Solde du compte</p>
                        <p class="mt-1 text-lg font-semibold {{ ($client->balance ?? 0) > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}">
                            {{ number_format($client->balance ?? 0, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Inscrit le</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $client->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Historique des Réservations -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Historique des Réservations</h3>
                @if($bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookings as $booking)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                Réf: {{ $booking->booking_reference }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                                {{ $booking->status === 'confirmed' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 
                                                   ($booking->status === 'pending' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400' : 
                                                   'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $booking->schedule->route->departureStation->name ?? 'N/A' }} → 
                                            {{ $booking->schedule->route->arrivalStation->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ \Carbon\Carbon::parse($booking->schedule->schedule_date)->format('d/m/Y') }} à {{ $booking->schedule->departure_time }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Siège: {{ $booking->seat_number ?? 'N/A' }} | 
                                            Prix: {{ number_format($booking->total_price, 0, ',', ' ') }} FCFA
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $booking->created_at->format('d/m/Y') }}
                                        </p>
                                        @if($booking->ticket)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 mt-1">
                                                Billet généré
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($bookings->hasPages())
                        <div class="mt-4">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucune réservation</p>
                @endif
            </div>

            <!-- Historique des Transactions -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Historique des Transactions</h3>
                @if($transactions->count() > 0)
                    <div class="space-y-4">
                        @foreach($transactions as $transaction)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                {{ $transaction->type === 'recharge' ? 'Recharge' : ($transaction->type === 'payment' ? 'Paiement' : 'Remboursement') }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                                {{ $transaction->status === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 
                                                   ($transaction->status === 'pending' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400' : 
                                                   'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $transaction->description ?? 'Transaction' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Réf: {{ $transaction->transaction_reference }}
                                            @if($transaction->payment_method)
                                                | Méthode: {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold {{ $transaction->type === 'recharge' ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}">
                                            {{ $transaction->type === 'recharge' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Solde: {{ number_format($transaction->balance_after, 0, ',', ' ') }} FCFA
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($transactions->hasPages())
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucune transaction</p>
                @endif
            </div>

            <!-- Historique des Locations -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Historique des Locations</h3>
                @if($rentals->count() > 0)
                    <div class="space-y-4">
                        @foreach($rentals as $rental)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                {{ $rental->vehicle->name ?? 'N/A' }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                                {{ $rental->status === 'confirmed' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 
                                                   ($rental->status === 'pending' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400' : 
                                                   'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                                {{ ucfirst($rental->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Du {{ \Carbon\Carbon::parse($rental->start_date)->format('d/m/Y') }} au 
                                            {{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Prix: {{ number_format($rental->total_price, 0, ',', ' ') }} FCFA
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $rental->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($rentals->hasPages())
                        <div class="mt-4">
                            {{ $rentals->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucune location</p>
                @endif
            </div>
        </div>

        <!-- Sidebar - Statistiques -->
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Statistiques</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Réservations</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_bookings'] }}</p>
                        <div class="mt-2 flex gap-2 text-xs">
                            <span class="text-success-600">Confirmées: {{ $stats['confirmed_bookings'] }}</span>
                            <span class="text-warning-600">En attente: {{ $stats['pending_bookings'] }}</span>
                            <span class="text-gray-600">Annulées: {{ $stats['cancelled_bookings'] }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Locations</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_rentals'] }}</p>
                        <p class="mt-1 text-xs text-success-600">Actives: {{ $stats['active_rentals'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Dépensé</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($stats['total_spent'], 0, ',', ' ') }} FCFA
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <p>Réservations: {{ number_format($stats['total_spent_bookings'], 0, ',', ' ') }} FCFA</p>
                            <p>Locations: {{ number_format($stats['total_spent_rentals'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
