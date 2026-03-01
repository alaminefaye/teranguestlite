@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Facturation / Notes de chambre</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Vue centralisée des séjours et de l’état des factures (type Opera, Oracle). Réglez les notes de chambre depuis la fiche réservation.</p>
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
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Séjours (confirmés / actifs / sortis)</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_reservations'] }}</p>
    </div>
    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
        <p class="text-sm text-warning-600 dark:text-warning-400">Avec solde à régler</p>
        <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['with_balance'] }}</p>
    </div>
</div>

<!-- Filtres -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.billing.index') }}" class="space-y-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence ou nom client..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                <select name="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="">Tous les statuts</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>En séjour</option>
                    <option value="checked_out" {{ request('status') === 'checked_out' ? 'selected' : '' }}>Check-out</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Chambre</label>
                <select name="room_id" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[120px]">
                    <option value="">Toutes</option>
                    @foreach($rooms ?? [] as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in (du)</label>
                <input type="date" name="check_in_from" value="{{ request('check_in_from') }}" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in (au)</label>
                <input type="date" name="check_in_to" value="{{ request('check_in_to') }}" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'room_id', 'check_in_from', 'check_in_to']))
                <a href="{{ route('dashboard.billing.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
            @endif
        </div>
    </form>
</div>

<!-- Liste facturation -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    @if(count($rows) > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Chambre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Séjour</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">À régler</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Réglé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($rows as $row)
                        @php $r = $row->reservation; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $r->reservation_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $r->guest ? $r->guest->name : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $r->room ? $r->room->room_number : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $r->check_in->format('d/m/Y') }} → {{ $r->check_out->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                                        'confirmed' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                        'checked_in' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                                        'checked_out' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                        'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$r->status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ $r->status_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium {{ $row->has_balance ? 'text-warning-600 dark:text-warning-400' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ number_format($row->total_due, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">
                                {{ number_format($row->total_paid, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('dashboard.reservations.show', $r) }}" class="text-brand-600 dark:text-brand-400 hover:underline text-sm font-medium">
                                    Voir résa / Régler
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $reservations->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Aucune réservation</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Aucun séjour à afficher pour la facturation. Créez des réservations ou ajustez les filtres.</p>
            <div class="mt-6">
                <a href="{{ route('dashboard.reservations.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    Voir les réservations
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
