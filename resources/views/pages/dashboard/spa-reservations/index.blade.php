@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Réservations Spa</h1>
    <p class="text-gray-600 dark:text-gray-400">Voir les réservations de soins spa et bien-être</p>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Confirmées</p>
        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['confirmed'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Aujourd'hui</p>
        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $stats['today'] }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.spa-reservations.index') }}" class="space-y-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Service ou client..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <div class="min-w-[160px]">
                <x-form.date-picker name="date_from" label="Date (du)" placeholder="Choisir une date" :defaultDate="request('date_from')" />
            </div>
            <div class="min-w-[160px]">
                <x-form.date-picker name="date_to" label="Date (au)" placeholder="Choisir une date" :defaultDate="request('date_to')" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Service spa</label>
                <select name="spa_service_id" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[180px]">
                    <option value="">Tous</option>
                    @foreach($spaServices ?? [] as $s)
                        <option value="{{ $s->id }}" {{ request('spa_service_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                <select name="status" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Tous les statuts</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
        </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'spa_service_id', 'date_from', 'date_to', 'date']))
                <a href="{{ route('dashboard.spa-reservations.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
            @endif
        </div>
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Heure</th>
                    <th class="px-4 py-3">Service</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3 text-right">Prix</th>
                    <th class="px-4 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reservations as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3">{{ $r->reservation_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $r->reservation_time }}</td>
                        <td class="px-4 py-3 font-medium">{{ $r->spaService?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $r->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $r->formatted_price }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium @if($r->status === 'confirmed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 @elseif($r->status === 'cancelled') bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400 @else bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 @endif">{{ $r->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune réservation spa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $reservations->links() }}</div>
    @endif
</div>
@endsection
