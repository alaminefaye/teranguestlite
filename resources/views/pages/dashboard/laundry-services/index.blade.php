@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Services Blanchisserie</h1>
    <a href="{{ route('dashboard.laundry-services.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Créer un service</a>
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
        <p class="text-sm text-brand-600 dark:text-brand-400">Lavage</p>
        <p class="text-2xl font-semibold text-brand-700 dark:text-brand-300">{{ $stats['washing'] }}</p>
    </div>
    <div class="rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-900/20">
        <p class="text-sm text-primary-600 dark:text-primary-400">Express</p>
        <p class="text-2xl font-semibold text-primary-700 dark:text-primary-300">{{ $stats['express'] }}</p>
    </div>
</div>

<!-- Liste -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($services as $service)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$service->is_active ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if(!$service->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masqué</span>@endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $service->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $service->category_label }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div>
                    <p class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $service->formatted_price }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Délai {{ $service->turnaround_hours }}h</p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard.laundry-services.show', $service) }}" class="inline-flex items-center px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Voir</a>
                    <a href="{{ route('dashboard.laundry-services.edit', $service) }}" class="inline-flex items-center px-2 py-1 text-xs bg-brand-500 text-white rounded hover:bg-brand-600">Modifier</a>
                    <form action="{{ route('dashboard.laundry-services.toggle', $service) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs {{ $service->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">{{ $service->is_active ? 'Masquer' : 'Afficher' }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Aucun service trouvé.</p>
            <a href="{{ route('dashboard.laundry-services.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer un service</a>
        </div>
    @endforelse
</div>

@if($services->hasPages())
<div class="mt-6">
    {{ $services->links() }}
</div>
@endif
@endsection
