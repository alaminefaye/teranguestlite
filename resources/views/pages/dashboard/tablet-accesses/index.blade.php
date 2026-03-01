@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Accès tablettes</h1>
            <p class="text-gray-600 dark:text-gray-400">Comptes « Client Chambre XXX » pour identifier chaque chambre sur la
                tablette</p>
        </div>
        <a href="{{ route('dashboard.tablet-accesses.create') }}"
            class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvel accès tablette
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
    @if(session('info'))
        <div
            class="mb-6 rounded-lg bg-blue-light-50 p-4 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400">
            {{ session('info') }}
        </div>
    @endif

    <div class="mb-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 inline-block">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total accès tablettes</p>
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $total }}</p>
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
        <form method="GET" action="{{ route('dashboard.tablet-accesses.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email ou chambre..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
            @if(request()->hasAny(['search']))
                <a href="{{ route('dashboard.tablet-accesses.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
            @endif
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        @if($guests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Nom
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                                Chambre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Code
                                Client (Web)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($guests as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                    @if($user->room)
                                        <a href="{{ route('dashboard.rooms.show', $user->room) }}"
                                            class="text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $user->room->room_number }}</a>
                                    @else
                                        {{ $user->room_number ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->client_code)
                                        <div class="flex items-center gap-3">
                                            <div class="bg-white p-1 rounded-sm shadow-sm border border-gray-100">
                                                {!! QrCode::size(50)->generate(url('/client?code=' . $user->client_code)) !!}
                                            </div>
                                            <div>
                                                <span
                                                    class="text-sm font-bold text-gray-900 dark:text-white block">{{ $user->client_code }}</span>
                                                <a href="{{ url('/client?code=' . $user->client_code) }}" target="_blank"
                                                    class="text-xs text-brand-600 hover:underline">Ouvrir Web App</a>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Non généré</span>
                                    @endif
                                    <form action="{{ route('dashboard.tablet-accesses.regenerate-client-code', $user->id) }}"
                                        method="POST" class="mt-2 inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="text-xs text-brand-600 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Générer Code
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <x-action-buttons :editRoute="route('dashboard.tablet-accesses.edit', $user->id)"
                                        :deleteRoute="route('dashboard.tablet-accesses.destroy', $user->id)"
                                        deleteMessage="Supprimer cet accès tablette ?" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($guests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $guests->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Aucun accès tablette</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Créez un compte par chambre pour que la tablette identifie la
                    chambre (connexion avec email / mot de passe).</p>
                <div class="mt-6">
                    <a href="{{ route('dashboard.tablet-accesses.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Nouvel
                        accès tablette</a>
                </div>
            </div>
        @endif
    </div>
@endsection