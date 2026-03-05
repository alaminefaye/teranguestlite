@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Dashboard</a>
        <span>/</span>
        <span>Nos établissements</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Nos établissements</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Autres sites du groupe affichés dans l'app (Hotel Infos & Sécurité).</p>
        </div>
        <a href="{{ route('dashboard.establishments.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">+ Ajouter un établissement</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<form method="GET" action="{{ route('dashboard.establishments.index') }}" class="mb-6">
    <div class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou lieu..."
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request('search'))
            <a href="{{ route('dashboard.establishments.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </div>
</form>

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
        @forelse($establishments as $establishment)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden {{ !$establishment->is_active ? 'opacity-70' : '' }}">
                @if(!$establishment->is_active)
                    <span class="block text-xs text-amber-600 dark:text-amber-400 font-medium p-2">Masqué</span>
                @endif
                @if($establishment->cover_photo_url)
                    <a href="{{ route('dashboard.establishments.show', $establishment) }}" class="block aspect-[16/10] bg-gray-100 dark:bg-gray-800">
                        <img src="{{ $establishment->cover_photo_url }}" alt="{{ $establishment->name }}" class="w-full h-full object-cover">
                    </a>
                @else
                    <a href="{{ route('dashboard.establishments.show', $establishment) }}" class="block aspect-[16/10] bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <span class="text-sm">Pas de photo</span>
                    </a>
                @endif
                <div class="p-3">
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $establishment->name }}</h3>
                    @if($establishment->location)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $establishment->location }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <a href="{{ route('dashboard.establishments.photos.index', $establishment) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-brand-500 text-white rounded hover:bg-brand-600">Galerie</a>
                        <a href="{{ route('dashboard.establishments.edit', $establishment) }}" class="inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Modifier</a>
                        <form action="{{ route('dashboard.establishments.destroy', $establishment) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet établissement et toutes ses photos ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-error-600 dark:text-error-400 hover:underline">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                Aucun établissement. Ajoutez-en un pour les afficher dans l'app (section Hotel Infos & Sécurité).
            </div>
        @endforelse
    </div>
    @if($establishments->hasPages())
        <div class="px-6 pb-6">
            {{ $establishments->links() }}
        </div>
    @endif
</div>
@endsection
