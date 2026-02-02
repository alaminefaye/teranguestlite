@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Gestion des Billets</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Consultez et gérez tous les billets de voyage</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-success-50 p-4 text-sm text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-error-50 p-4 text-sm text-error-600 dark:bg-error-500/10 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form method="GET" action="{{ route('tickets.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Numéro de billet, référence, nom..."
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Statut</label>
                    <select name="status" 
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Tous les statuts</option>
                        <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valide</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Utilisé</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expiré</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Date de départ (début)</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Rechercher
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-end">
                <a href="{{ route('tickets.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Numéro de billet</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Passager</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Trajet</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Date & Heure</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Siège</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-b border-gray-100 dark:border-gray-800 table-row-hover">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $ticket->ticket_number }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Ref: {{ $ticket->booking->booking_reference }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->booking->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->booking->user->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-800 dark:text-white/90">
                                    {{ $ticket->booking->schedule->route->departureStation->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    → {{ $ticket->booking->schedule->route->arrivalStation->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-800 dark:text-white/90">
                                    {{ \Carbon\Carbon::parse($ticket->booking->schedule->schedule_date)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ticket->booking->schedule->departure_time }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $ticket->booking->seat_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($ticket->status === 'valid')
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                        Valide
                                    </span>
                                @elseif($ticket->status === 'used')
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        Utilisé
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                        Expiré
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('tickets.show', $ticket->booking) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5"
                                       title="Voir le billet">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('tickets.download', $ticket->booking) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5"
                                       title="Télécharger">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                    @can('mark tickets used')
                                        @if($ticket->status === 'valid')
                                            <form action="{{ route('tickets.mark-used', $ticket) }}" method="POST" 
                                                  onsubmit="return confirm('Marquer ce billet comme utilisé ?');"
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-white/5"
                                                        title="Marquer comme utilisé">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucun billet trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
