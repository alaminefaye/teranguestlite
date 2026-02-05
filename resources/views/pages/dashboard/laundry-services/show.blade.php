@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.laundry-services.index') }}" class="hover:text-brand-500">Blanchisserie</a>
        <span>/</span>
        <span>{{ $service->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $service->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.laundry-services.edit', $service) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm">Modifier</a>
            <form action="{{ route('dashboard.laundry-services.destroy', $service) }}" method="POST" onsubmit="return confirm('Supprimer ce service ?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-error-300 text-error-600 rounded-md hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-900/20 text-sm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $service->category_label }}</p>
    @if($service->description)
        <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $service->description }}</p>
    @endif
    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Prix</p>
            <p class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ $service->formatted_price }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Délai</p>
            <p class="text-lg text-gray-800 dark:text-white/90">{{ $service->turnaround_hours }} h</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Statut</p>
            <p class="text-lg {{ $service->status === 'available' ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $service->status === 'available' ? 'Disponible' : 'Indisponible' }}</p>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('dashboard.laundry-services.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">← Retour à la liste</a>
</div>
@endsection
