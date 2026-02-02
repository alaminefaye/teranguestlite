@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('vehicles.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $vehicle->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails du véhicule</p>
            </div>
        </div>
        <a href="{{ route('vehicles.edit', $vehicle) }}" 
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Modifier
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Photo et infos de base -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start gap-6">
                    @if($vehicle->photo)
                        <img src="{{ Storage::url($vehicle->photo) }}" alt="{{ $vehicle->name }}" class="h-32 w-32 rounded-lg object-cover">
                    @else
                        <div class="flex h-32 w-32 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $vehicle->name }}</h2>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ ucfirst($vehicle->type) }}
                            </span>
                            @php
                                $statusColors = [
                                    'available' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                    'maintenance' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                    'in_use' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                                ];
                                $statusLabels = [
                                    'available' => 'Disponible',
                                    'maintenance' => 'Maintenance',
                                    'in_use' => 'En cours',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$vehicle->status] ?? 'bg-gray-50 text-gray-600' }}">
                                {{ $statusLabels[$vehicle->status] ?? $vehicle->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($vehicle->description)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Description</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->description }}</p>
                </div>
            @endif

            <!-- Avis et notations -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Avis et Notations</h3>
                    @if($canReview && auth()->check())
                        <a href="{{ route('reviews.create', ['type' => 'vehicle', 'reviewable_id' => $vehicle->id]) }}" 
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Laisser un avis
                        </a>
                    @endif
                </div>

                @if($reviews->count() > 0)
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= round($vehicle->averageRating()) ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ number_format($vehicle->averageRating(), 1) }}/5 ({{ $reviews->total() }} avis)
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($reviews as $review)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                            <span class="text-xs font-semibold">{{ strtoupper(substr($review->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $review->user->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $review->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($reviews->hasPages())
                        <div class="mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun avis pour le moment</p>
                @endif
            </div>
        </div>

        <!-- Informations détaillées -->
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Numéro de plaque</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $vehicle->plate_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Capacité</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $vehicle->capacity }} places</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Chauffeur</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $vehicle->chauffeur ? $vehicle->chauffeur->name : 'Non assigné' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kilométrage actuel</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($vehicle->current_mileage, 0, ',', ' ') }} km</p>
                    </div>
                </div>
            </div>

            @if($reviews->count() > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Note moyenne</h3>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-6 w-6 {{ $i <= round($vehicle->averageRating()) ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($vehicle->averageRating(), 1) }}/5
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $reviews->total() }} avis</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

