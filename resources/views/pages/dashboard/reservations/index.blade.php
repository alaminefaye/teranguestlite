@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Réservations</h1>
        <p class="text-gray-600 dark:text-gray-400">Gérer les réservations de l'hôtel</p>
    </div>
    <a href="{{ route('dashboard.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nouvelle réservation
    </a>
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
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">En attente</p>
        <p class="text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $stats['pending'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Confirmées</p>
        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['confirmed'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Actives</p>
        <p class="text-2xl font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $stats['active'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Check-ins</p>
        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $stats['today_checkins'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Check-outs</p>
        <p class="text-2xl font-semibold text-gray-600 dark:text-gray-400">{{ $stats['today_checkouts'] }}</p>
    </div>
</div>

<!-- Filtres avancés -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <form method="GET" action="{{ route('dashboard.reservations.index') }}" class="space-y-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence ou nom client..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                <select name="status" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Check-in</option>
                    <option value="checked_out" {{ request('status') === 'checked_out' ? 'selected' : '' }}>Check-out</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Chambre</label>
                <select name="room_id" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Toutes</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>Ch. {{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Client</label>
                <select name="guest_id" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Tous</option>
                    @foreach($guests as $g)
                        <option value="{{ $g->id }}" {{ request('guest_id') == $g->id ? 'selected' : '' }}>{{ Str::limit($g->name, 25) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in à partir du</label>
                <input type="date" name="check_in_from" value="{{ request('check_in_from') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in jusqu'au</label>
                <input type="date" name="check_in_to" value="{{ request('check_in_to') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Trier par</label>
                <select name="sort" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="check_in_desc" {{ request('sort') === 'check_in_desc' ? 'selected' : '' }}>Check-in (récent → ancien)</option>
                    <option value="check_in_asc" {{ request('sort') === 'check_in_asc' ? 'selected' : '' }}>Check-in (ancien → récent)</option>
                    <option value="check_out_desc" {{ request('sort') === 'check_out_desc' ? 'selected' : '' }}>Check-out (récent)</option>
                    <option value="check_out_asc" {{ request('sort') === 'check_out_asc' ? 'selected' : '' }}>Check-out (ancien)</option>
                    <option value="total_price_desc" {{ request('sort') === 'total_price_desc' ? 'selected' : '' }}>Prix (décroissant)</option>
                    <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>Date création (récent)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'status', 'room_id', 'guest_id', 'check_in_from', 'check_in_to']) || request('sort', 'check_in_desc') !== 'check_in_desc')
                    <a href="{{ route('dashboard.reservations.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Liste des réservations -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    @if($reservations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Chambre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Check-in</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Check-out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Nuits</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($reservations as $reservation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $reservation->reservation_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->guest ? $reservation->guest->name : ($reservation->user?->name ?? '—') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->room->room_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->check_in->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->check_out->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->nights_count }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</td>
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
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$reservation->status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ $reservation->status_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <x-action-buttons 
                                    :showRoute="route('dashboard.reservations.show', $reservation)"
                                    :invoiceRoute="$reservation->status === 'checked_out' ? route('dashboard.reservations.invoice', $reservation) : null"
                                />
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Aucune réservation</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Commencez par créer votre première réservation.</p>
            <div class="mt-6">
                <a href="{{ route('dashboard.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Créer une réservation
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
