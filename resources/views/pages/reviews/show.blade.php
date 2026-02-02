@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('reviews.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Détails de l'Avis</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Avis de {{ $review->user->name }}</p>
            </div>
        </div>
        @if(auth()->check() && ($review->user_id === auth()->id() || auth()->user()->can('edit reviews') || auth()->user()->can('manage reviews')))
            <a href="{{ route('reviews.edit', $review) }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations de l'avis -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Avis</h3>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="h-5 w-5 {{ $i <= $review->rating ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">({{ $review->rating }}/5)</span>
                    </div>
                </div>
                
                @if($review->comment)
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $review->comment }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun commentaire</p>
                @endif
            </div>

            <!-- Élément évalué -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Élément évalué</h3>
                <div class="space-y-2">
                    @if($review->reviewable_type === 'App\Models\Route')
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Type:</strong> Trajet
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Départ:</strong> {{ $review->reviewable->departureStation->name ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Arrivée:</strong> {{ $review->reviewable->arrivalStation->name ?? 'N/A' }}
                        </p>
                    @elseif($review->reviewable_type === 'App\Models\Vehicle')
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Type:</strong> Véhicule
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Nom:</strong> {{ $review->reviewable->name ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Type:</strong> {{ $review->reviewable->type ?? 'N/A' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Informations utilisateur -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Auteur</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <span class="text-sm font-semibold">{{ strtoupper(substr($review->user->name, 0, 2)) }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $review->user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $review->user->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <p><strong>Date:</strong> {{ $review->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Statut:</strong> 
                        @if($review->is_approved)
                            <span class="text-success-600">Approuvé</span>
                        @else
                            <span class="text-warning-600">En attente</span>
                        @endif
                    </p>
                    @if($review->booking)
                        <p><strong>Réservation:</strong> {{ $review->booking->booking_reference }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
