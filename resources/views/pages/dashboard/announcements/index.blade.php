@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Annonces (Super Admin)</h1>
        <a href="{{ route('admin.announcements.create') }}"
            class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+
            Nouvelle annonce</a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
            <p class="text-sm text-success-600 dark:text-success-400">Actives</p>
            <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
            <p class="text-sm text-brand-600 dark:text-brand-400">Total vues</p>
            <p class="text-2xl font-semibold text-brand-700 dark:text-brand-300">{{ number_format($stats['total_views']) }}
            </p>
        </div>
    </div>

    {{-- Liste --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($announcements as $announcement)
            <div
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$announcement->is_active ? 'opacity-70' : '' }}">

                {{-- Aperçu affiche --}}
                @if($announcement->poster_path)
                    <img src="{{ asset('storage/' . $announcement->poster_path) }}" alt="{{ $announcement->title }}"
                        class="h-32 w-full object-cover rounded-lg mb-3 border border-gray-200 dark:border-gray-700">
                @elseif($announcement->video_path)
                    <div
                        class="h-32 rounded-lg bg-gray-800 dark:bg-gray-700 flex items-center justify-center mb-3 text-gray-400 text-xs gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Vidéo uniquement
                    </div>
                @else
                    <div
                        class="h-32 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3 text-gray-400 dark:text-gray-500 text-xs">
                        Pas de média</div>
                @endif

                <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex-1 min-w-0">
                        @if(!$announcement->is_active)<span
                        class="text-xs text-amber-600 dark:text-amber-400 font-medium">Inactive</span>@endif
                        <h3 class="font-semibold text-gray-800 dark:text-white/90 truncate">
                            {{ $announcement->title ?? '(Sans titre)' }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Type :
                            @if($announcement->type === 'both') Affiche + Vidéo
                            @elseif($announcement->type === 'video_only') Vidéo seule
                            @else Affiche seule ({{ $announcement->display_duration_minutes ?? 1 }} min)
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Ciblage --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    @if($announcement->target_all_enterprises)
                        <span
                            class="inline-flex items-center rounded-full bg-brand-50 dark:bg-brand-900/30 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-300">Toutes
                            les entreprises</span>
                    @else
                        {{ $announcement->targetEnterprises->count() }} entreprise(s) ciblée(s)
                    @endif
                </p>

                {{-- Vues --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    👁 {{ number_format($announcement->view_count) }} vue(s)
                </p>

                <div class="flex items-center gap-2">
                    <x-action-buttons :showRoute="route('admin.announcements.show', $announcement)"
                        :editRoute="route('admin.announcements.edit', $announcement)"
                        :deleteRoute="route('admin.announcements.destroy', $announcement)"
                        deleteMessage="Supprimer cette annonce ?" />
                    <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-2 py-1 text-xs {{ $announcement->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">
                            {{ $announcement->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-600 dark:text-gray-400 mb-4">Aucune annonce super admin.</p>
                <a href="{{ route('admin.announcements.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer une
                    annonce</a>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-6">{{ $announcements->links() }}</div>
    @endif
@endsection