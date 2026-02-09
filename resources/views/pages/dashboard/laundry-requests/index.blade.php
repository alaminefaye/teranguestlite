@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Demandes Blanchisserie</h1>
    <p class="text-gray-600 dark:text-gray-400">Voir les demandes de blanchisserie</p>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">En attente</p>
        <p class="text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $stats['pending'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Aujourd'hui</p>
        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $stats['today'] }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <form method="GET" action="{{ route('dashboard.laundry-requests.index') }}" class="flex flex-wrap gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° demande ou client..." class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[200px]">
        <select name="status" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Tous les statuts</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminée</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
    </form>
</div>

<div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">N° Demande</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Chambre</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-mono font-medium">{{ $req->request_number }}</td>
                        <td class="px-4 py-3">{{ $req->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $req->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $req->room?->room_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $req->formatted_total_price }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium @if($req->status === 'pending') bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 @elseif($req->status === 'completed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 @else bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400 @endif">{{ $req->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune demande blanchisserie</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
