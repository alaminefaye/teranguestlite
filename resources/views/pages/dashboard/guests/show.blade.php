@extends('layouts.app')

@section('content')
{{-- Fil d'Ariane --}}
<nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
    <a href="{{ route('dashboard.guests.index') }}" class="hover:text-brand-500 transition-colors">Clients (invités)</a>
    <span aria-hidden="true">/</span>
    <span class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->name }}</span>
</nav>

@if(session('success'))
    <div class="mb-6 rounded-xl bg-success-50 dark:bg-success-500/10 border border-success-200 dark:border-success-500/20 p-4 text-success-700 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-xl bg-error-50 dark:bg-error-500/10 border border-error-200 dark:border-error-500/20 p-4 text-error-700 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

{{-- En-tête client --}}
<div class="mb-8 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-brand-500/10 via-brand-500/5 to-transparent dark:from-brand-500/20 dark:via-brand-500/10 px-6 py-5 sm:px-8 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 sm:h-16 sm:w-16 shrink-0 items-center justify-center rounded-xl bg-brand-500/20 dark:bg-brand-500/30 text-brand-600 dark:text-brand-400">
                    <svg class="h-8 w-8 sm:h-9 sm:w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white/95">{{ $guest->name }}</h1>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Fiche client · {{ $orders->count() }} commande(s)</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.guests.edit', $guest) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-colors text-sm font-medium shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
                <form action="{{ route('dashboard.guests.regenerate-code', $guest) }}" method="POST" class="inline" onsubmit="return confirm('Générer un nouveau code ? L\'ancien ne fonctionnera plus.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-brand-500 text-brand-600 dark:text-brand-400 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors text-sm font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Régénérer le code
                    </button>
                </form>
                <form action="{{ route('dashboard.guests.destroy', $guest) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce client ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-error-300 dark:border-error-600 text-error-600 dark:text-error-400 rounded-lg hover:bg-error-50 dark:hover:bg-error-500/10 transition-colors text-sm font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Cartes d'info (Code tablette + Contact) --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 dark:bg-brand-500/20">
                <svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Code tablette</h2>
        </div>
        <p class="text-2xl font-mono font-bold text-brand-600 dark:text-brand-400 tracking-wider">{{ $guest->access_code }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Utilisé sur la tablette en chambre pour valider commandes et réservations.</p>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10 dark:bg-blue-500/20">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Contact</h2>
        </div>
        <p class="text-gray-800 dark:text-white/90">{{ $guest->email ?? '—' }}</p>
        <p class="text-gray-800 dark:text-white/90 mt-1">{{ $guest->phone ?? '—' }}</p>
    </div>
</div>

{{-- Identité --}}
@if($guest->gender || $guest->date_of_birth || $guest->nationality)
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Identité</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        @if($guest->gender)
        <div><dt class="text-gray-500 dark:text-gray-400">Genre</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->gender === 'male' ? 'Homme' : ($guest->gender === 'female' ? 'Femme' : 'Autre') }}</dd></div>
        @endif
        @if($guest->date_of_birth)
        <div><dt class="text-gray-500 dark:text-gray-400">Date de naissance</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->date_of_birth->format('d/m/Y') }}</dd></div>
        @endif
        @if($guest->nationality)
        <div><dt class="text-gray-500 dark:text-gray-400">Nationalité</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->nationality }}</dd></div>
        @endif
    </dl>
</div>
@endif

{{-- Pièce d'identité --}}
@if($guest->id_document_type || $guest->id_document_number || $guest->id_document_photo)
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Pièce d'identité</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        @if($guest->id_document_type)<div><dt class="text-gray-500 dark:text-gray-400">Type</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->id_document_type }}</dd></div>@endif
        @if($guest->id_document_number)<div><dt class="text-gray-500 dark:text-gray-400">N°</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->id_document_number }}</dd></div>@endif
        @if($guest->id_document_place_of_issue)<div><dt class="text-gray-500 dark:text-gray-400">Lieu de délivrance</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->id_document_place_of_issue }}</dd></div>@endif
        @if($guest->id_document_issued_at)<div><dt class="text-gray-500 dark:text-gray-400">Date de délivrance</dt><dd class="text-gray-800 dark:text-white/90 font-medium">{{ $guest->id_document_issued_at->format('d/m/Y') }}</dd></div>@endif
    </dl>
    @if($guest->id_document_photo)
    <p class="mt-4"><a href="{{ asset('storage/'.$guest->id_document_photo) }}" target="_blank" class="inline-flex items-center gap-2 text-brand-600 dark:text-brand-400 hover:underline text-sm font-medium">Voir la photo / scan de la pièce →</a></p>
    @endif
</div>
@endif

{{-- Adresse --}}
@if($guest->address || $guest->city || $guest->country)
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Adresse</h2>
    <p class="text-gray-800 dark:text-white/90">@if($guest->address){{ $guest->address }}<br>@endif @if($guest->city || $guest->country){{ trim(implode(', ', array_filter([$guest->city, $guest->country]))) ?: '—' }}@endif</p>
</div>
@endif

@if($guest->notes)
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">Notes</h2>
    <p class="text-gray-700 dark:text-gray-300">{{ $guest->notes }}</p>
</div>
@endif

{{-- Historique des commandes et transactions --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Historique des commandes et transactions
            </h2>
            @if($orders->count() > 0)
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $orders->count() }} commande(s)</span>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        @if($orders->count() > 0)
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">N° commande</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Chambre</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Paiement</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 font-mono font-medium text-gray-800 dark:text-white/90">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $order->type_name }}</td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $order->room?->room_number ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800 dark:text-white/90">{{ $order->formatted_total }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $order->payment_method_name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusBg = [
                                    'pending' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400',
                                    'confirmed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'preparing' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                    'ready' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'delivering' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                    'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBg[$order->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300' }}">{{ $order->status_name }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('dashboard.orders.show', $order) }}" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="mt-3 text-gray-500 dark:text-gray-400">Aucune commande pour ce client.</p>
                <a href="{{ route('dashboard.orders.index') }}" class="mt-3 inline-flex items-center text-brand-600 dark:text-brand-400 hover:underline text-sm font-medium">Voir toutes les commandes</a>
            </div>
        @endif
    </div>
</div>

{{-- Réservations + Retour --}}
<div class="flex flex-wrap items-center gap-4">
    <a href="{{ route('dashboard.reservations.index') }}?guest_id={{ $guest->id }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Réservations ({{ $guest->reservations_count }})
    </a>
    <a href="{{ route('dashboard.guests.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour à la liste
    </a>
</div>
@endsection
