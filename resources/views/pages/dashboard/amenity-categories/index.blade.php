@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Amenities & Conciergerie</h1>
    <a href="{{ route('dashboard.amenity-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Ajouter une catégorie</a>
</div>

<p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Les catégories et articles définis ici apparaissent dans l’app mobile (Services en chambre → Amenities & Conciergerie). Les clients choisissent des articles et quantités.</p>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Filtres avancés -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.amenity-categories.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom de la catégorie..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request()->hasAny(['search']))
            <a href="{{ route('dashboard.amenity-categories.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $category)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$category->is_active ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if(!$category->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masquée</span>@endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $category->items_count }} article(s)</p>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <a href="{{ route('dashboard.amenity-categories.items.index', $category) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-brand-500 text-white rounded hover:bg-brand-600">Gérer les articles</a>
                <x-action-buttons
                    :editRoute="route('dashboard.amenity-categories.edit', $category)"
                    :canDelete="false"
                />
                <form action="{{ route('dashboard.amenity-categories.toggle', $category) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-2 py-1 text-xs {{ $category->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">
                        {{ $category->is_active ? 'Masquer' : 'Afficher' }}
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Aucune catégorie. Créez-en une pour que les clients puissent commander des articles (toiletries, oreillers, etc.).</p>
            <a href="{{ route('dashboard.amenity-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Ajouter une catégorie</a>
        </div>
    @endforelse
</div>

@if($categories->hasPages())
<div class="mt-6">
    {{ $categories->links() }}
</div>
@endif
@endsection
