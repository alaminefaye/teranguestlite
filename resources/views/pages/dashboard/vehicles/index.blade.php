@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Véhicules</h1>
    <a href="{{ route('dashboard.vehicles.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Ajouter un véhicule</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

{{-- ===== Mode d'affichage app mobile ===== --}}
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="font-semibold text-gray-800 dark:text-white/90 mb-1">Mode d'affichage dans l'application mobile</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Choisissez comment les clients voient la location de véhicule depuis l'app.
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Catalogue (défaut) --}}
            <form action="{{ route('dashboard.vehicles.rental-mode') }}" method="POST">
                @csrf
                <input type="hidden" name="rental_mode" value="catalogue">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition
                        {{ $rentalMode === 'catalogue'
                            ? 'bg-brand-500 text-white border-brand-500 shadow-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Catalogue
                    @if($rentalMode === 'catalogue')
                        <span class="ml-1 w-2 h-2 rounded-full bg-white inline-block"></span>
                    @endif
                </button>
            </form>

            {{-- Formulaire direct --}}
            <form action="{{ route('dashboard.vehicles.rental-mode') }}" method="POST">
                @csrf
                <input type="hidden" name="rental_mode" value="form">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition
                        {{ $rentalMode === 'form'
                            ? 'bg-brand-500 text-white border-brand-500 shadow-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Formulaire direct
                    @if($rentalMode === 'form')
                        <span class="ml-1 w-2 h-2 rounded-full bg-white inline-block"></span>
                    @endif
                </button>
            </form>
        </div>
    </div>
    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
        Mode actif :
        <span class="font-semibold {{ $rentalMode === 'form' ? 'text-brand-600 dark:text-brand-400' : 'text-success-600 dark:text-success-400' }}">
            {{ $rentalMode === 'form' ? 'Formulaire direct (le client remplit une demande sans choisir de véhicule)' : 'Catalogue (le client choisit parmi les véhicules disponibles)' }}
        </span>
    </p>
</div>
{{-- ===== Fin mode d'affichage ===== --}}

<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-sm text-success-600 dark:text-success-400">Disponibles</p>
        <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ $stats['available'] }}</p>
    </div>
</div>

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtres avancés</p>
    <form method="GET" action="{{ route('dashboard.vehicles.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
            <select name="vehicle_type" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Tous les types</option>
            @foreach(\App\Models\Vehicle::TYPES as $value => $label)
                <option value="{{ $value }}" {{ request('vehicle_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Places min.</label>
            <select name="seats" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Toutes</option>
            @for($i = 1; $i <= 20; $i++)
                <option value="{{ $i }}" {{ request('seats') == (string)$i ? 'selected' : '' }}>{{ $i }} place(s)</option>
            @endfor
        </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request()->hasAny(['search', 'vehicle_type', 'seats']))
            <a href="{{ route('dashboard.vehicles.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($vehicles as $vehicle)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$vehicle->is_available ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if(!$vehicle->is_available)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masqué</span>@endif
                    @if($vehicle->image)
                        <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="h-24 w-full object-cover rounded-lg mb-2 border border-gray-200 dark:border-gray-700">
                    @else
                        <div class="h-24 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-2 text-gray-400 dark:text-gray-500 text-xs">Pas d'image</div>
                    @endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $vehicle->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->type_label }} · {{ $vehicle->number_of_seats }} pl.</p>
                    <p class="text-xs text-brand-600 dark:text-brand-400 mt-1">{{ $vehicle->formatted_price_per_day }} / {{ $vehicle->formatted_price_half_day }} (demi-j.)</p>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div class="flex items-center gap-2">
                    <x-action-buttons
                        :showRoute="route('dashboard.vehicles.show', $vehicle)"
                        :editRoute="route('dashboard.vehicles.edit', $vehicle)"
                        :canDelete="false"
                    />
                    <form action="{{ route('dashboard.vehicles.toggle', $vehicle) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs {{ $vehicle->is_available ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">{{ $vehicle->is_available ? 'Masquer' : 'Afficher' }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Aucun véhicule. Les véhicules sont proposés dans le formulaire « Location » côté client.</p>
            <a href="{{ route('dashboard.vehicles.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Ajouter un véhicule</a>
        </div>
    @endforelse
</div>

@if($vehicles->hasPages())
<div class="mt-6">
    {{ $vehicles->links() }}
</div>
@endif
@endsection
