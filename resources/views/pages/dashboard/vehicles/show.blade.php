@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.vehicles.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux véhicules</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $vehicle->name }}</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col md:flex-row gap-6">
        @if($vehicle->image)
            <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="md:w-64 h-48 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
        @else
            <div class="md:w-64 h-48 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500">Pas d'image</div>
        @endif
        <div class="flex-1">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Type</dt><dd class="font-medium">{{ $vehicle->type_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nombre de places</dt><dd class="font-medium">{{ $vehicle->number_of_seats }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Disponible</dt><dd class="font-medium">{{ $vehicle->is_available ? 'Oui' : 'Non' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Prix journée</dt><dd class="font-medium">{{ $vehicle->formatted_price_per_day }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Prix demi-journée</dt><dd class="font-medium">{{ $vehicle->formatted_price_half_day }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Ordre d'affichage</dt><dd>{{ $vehicle->display_order }}</dd></div>
            </dl>
            <div class="mt-6 flex items-center gap-2">
                <a href="{{ route('dashboard.vehicles.edit', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm">Modifier</a>
                <form action="{{ route('dashboard.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Supprimer ce véhicule ?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-error-600 dark:text-error-400 border border-error-300 dark:border-error-700 rounded-md hover:bg-error-50 dark:hover:bg-error-900/20 text-sm">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
