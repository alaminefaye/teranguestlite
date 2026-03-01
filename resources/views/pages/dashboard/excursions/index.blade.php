@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Excursions</h1>
    <a href="{{ route('dashboard.excursions.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Créer une excursion</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-sm text-success-600 dark:text-success-400">Disponibles</p>
        <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ $stats['available'] }}</p>
    </div>
    <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
        <p class="text-sm text-brand-600 dark:text-brand-400">En vedette</p>
        <p class="text-2xl font-semibold text-brand-700 dark:text-brand-300">{{ $stats['featured'] }}</p>
    </div>
    <div class="rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-900/20">
        <p class="text-sm text-primary-600 dark:text-primary-400">Culturelles</p>
        <p class="text-2xl font-semibold text-primary-700 dark:text-primary-300">{{ $stats['cultural'] }}</p>
    </div>
</div>

<!-- Filtres avancés -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.excursions.index') }}" class="space-y-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom de l'excursion..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                <select name="type" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[160px]">
                    <option value="">Tous</option>
                    <option value="cultural" {{ request('type') === 'cultural' ? 'selected' : '' }}>Culturelle</option>
                    <option value="adventure" {{ request('type') === 'adventure' ? 'selected' : '' }}>Aventure</option>
                    <option value="relaxation" {{ request('type') === 'relaxation' ? 'selected' : '' }}>Détente</option>
                    <option value="city_tour" {{ request('type') === 'city_tour' ? 'selected' : '' }}>Tour de ville</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                <select name="status" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[140px]">
                    <option value="">Tous</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                    <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Indisponible</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
            @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('dashboard.excursions.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
            @endif
        </div>
    </form>
</div>

<!-- Liste -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($excursions as $excursion)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$excursion->is_active ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if(!$excursion->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masquée</span>@endif
                    @if($excursion->image)
                        <img src="{{ asset('storage/' . $excursion->image) }}" alt="{{ $excursion->name }}" class="h-20 w-full object-cover rounded-lg mb-2 border border-gray-200 dark:border-gray-700">
                    @else
                        <div class="h-20 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-2 text-gray-400 dark:text-gray-500 text-xs">Pas d'image</div>
                    @endif
                    @if($excursion->is_featured)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 dark:text-brand-400 mb-1">★ En vedette</span>
                    @endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $excursion->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $excursion->type_label }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div>
                    <p class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $excursion->formatted_price_adult }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $excursion->duration_hours }}h @if($excursion->departure_time) · {{ $excursion->departure_time }} @endif</p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard.excursions.show', $excursion) }}" class="inline-flex items-center px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Voir</a>
                    <a href="{{ route('dashboard.excursions.edit', $excursion) }}" class="inline-flex items-center px-2 py-1 text-xs bg-brand-500 text-white rounded hover:bg-brand-600">Modifier</a>
                    <form action="{{ route('dashboard.excursions.toggle', $excursion) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs {{ $excursion->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">{{ $excursion->is_active ? 'Masquer' : 'Afficher' }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Aucune excursion trouvée.</p>
            <a href="{{ route('dashboard.excursions.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer une excursion</a>
        </div>
    @endforelse
</div>

@if($excursions->hasPages())
<div class="mt-6">
    {{ $excursions->links() }}
</div>
@endif
@endsection
