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
    <form method="GET" action="{{ route('dashboard.vehicles.index') }}" class="flex flex-wrap gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom..."
            class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[180px]">
        <select name="vehicle_type" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Tous les types</option>
            @foreach(\App\Models\Vehicle::TYPES as $value => $label)
                <option value="{{ $value }}" {{ request('vehicle_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="seats" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            <option value="">Places min.</option>
            @for($i = 1; $i <= 20; $i++)
                <option value="{{ $i }}" {{ request('seats') == (string)$i ? 'selected' : '' }}>{{ $i }} place(s)</option>
            @endfor
        </select>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($vehicles as $vehicle)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if($vehicle->image)
                        <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="h-24 w-full object-cover rounded-lg mb-2 border border-gray-200 dark:border-gray-700">
                    @else
                        <div class="h-24 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-2 text-gray-400 dark:text-gray-500 text-xs">Pas d'image</div>
                    @endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $vehicle->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->type_label }} · {{ $vehicle->number_of_seats }} pl.</p>
                    <p class="text-xs text-brand-600 dark:text-brand-400 mt-1">{{ $vehicle->formatted_price_per_day }} / {{ $vehicle->formatted_price_half_day }} (demi-j.)</p>
                    @if(!$vehicle->is_available)
                        <span class="inline-flex text-xs font-medium text-warning-600 dark:text-warning-400">Indisponible</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard.vehicles.show', $vehicle) }}" class="inline-flex items-center px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Voir</a>
                    <a href="{{ route('dashboard.vehicles.edit', $vehicle) }}" class="inline-flex items-center px-2 py-1 text-xs bg-brand-500 text-white rounded hover:bg-brand-600">Modifier</a>
                    <form action="{{ route('dashboard.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Supprimer ce véhicule ?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs text-error-600 dark:text-error-400 border border-error-300 dark:border-error-700 rounded hover:bg-error-50 dark:hover:bg-error-900/20">Suppr.</button>
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
