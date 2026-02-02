@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Services Spa & Bien-être</h1>
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
        <p class="text-sm text-primary-600 dark:text-primary-400">Massages</p>
        <p class="text-2xl font-semibold text-primary-700 dark:text-primary-300">{{ $stats['massages'] }}</p>
    </div>
</div>

<!-- Liste -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($services as $service)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between mb-2">
                <div>
                    @if($service->is_featured)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 dark:text-brand-400 mb-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Populaire
                        </span>
                    @endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $service->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $service->category_label }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div>
                    <p class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $service->formatted_price }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">⏱️ {{ $service->duration_text }}</p>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12"><p class="text-gray-600 dark:text-gray-400">Aucun service trouvé</p>
        </div>
    @endforelse
</div>
@endsection
