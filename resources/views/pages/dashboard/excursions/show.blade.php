@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.excursions.index') }}" class="hover:text-brand-500">Excursions</a>
        <span>/</span>
        <span>{{ $excursion->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $excursion->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.excursions.edit', $excursion) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm">Modifier</a>
            <form action="{{ route('dashboard.excursions.destroy', $excursion) }}" method="POST" onsubmit="return confirm('Supprimer cette excursion ?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-error-300 text-error-600 rounded-md hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-900/20 text-sm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2 rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        @if($excursion->image)
            <img src="{{ asset('storage/' . $excursion->image) }}" alt="{{ $excursion->name }}" class="w-full max-h-64 object-contain rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
        @else
            <div class="h-48 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <span class="text-gray-400 dark:text-gray-500">Aucune image</span>
            </div>
        @endif
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $excursion->type_label }}</p>
        @if($excursion->description)
            <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $excursion->description }}</p>
        @endif
        @if(is_array($excursion->included) && count($excursion->included) > 0)
            <p class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-300">Inclus</p>
            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 mt-1">
                @foreach($excursion->included as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @endif
        @if(is_array($excursion->not_included) && count($excursion->not_included) > 0)
            <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Non inclus</p>
            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 mt-1">
                @foreach($excursion->not_included as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Prix adulte</p>
        <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $excursion->formatted_price_adult }}</p>
        @if($excursion->price_child)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Prix enfant</p>
            <p class="text-lg text-gray-800 dark:text-white/90">{{ $excursion->formatted_price_child }}</p>
        @endif
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Durée</p>
        <p class="text-lg text-gray-800 dark:text-white/90">{{ $excursion->duration_hours }} h</p>
        @if($excursion->departure_time)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Départ</p>
            <p class="text-lg text-gray-800 dark:text-white/90">{{ $excursion->departure_time }}</p>
        @endif
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Participants</p>
        <p class="text-lg text-gray-800 dark:text-white/90">{{ $excursion->min_participants }} @if($excursion->max_participants)– {{ $excursion->max_participants }} @endif</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Statut</p>
        <p class="text-lg {{ $excursion->status === 'available' ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-400' }}">
            @if($excursion->status === 'available') Disponible
            @elseif($excursion->status === 'seasonal') Saisonnier
            @else Indisponible
            @endif
        </p>
        @if($excursion->is_featured)
            <span class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 dark:text-brand-400 mt-2">★ En vedette</span>
        @endif
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('dashboard.excursions.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">← Retour à la liste</a>
</div>
@endsection
