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
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Laisser un Avis</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Partagez votre expérience</p>
            </div>
        </div>
    </div>

    @if(!$canReview)
        <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-500/10">
            <p class="text-sm text-warning-600 dark:text-warning-400">
                Vous devez avoir utilisé ce service pour pouvoir laisser un avis.
            </p>
        </div>
    @endif

    @if($canReview && $reviewable)
        <form method="POST" action="{{ route('reviews.store') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="reviewable_type" value="{{ get_class($reviewable) }}">
            <input type="hidden" name="reviewable_id" value="{{ $reviewable->id }}">
            @if($bookingId)
                <input type="hidden" name="booking_id" value="{{ $bookingId }}">
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informations sur l'élément évalué -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Élément évalué</h3>
                        <div class="space-y-2">
                            @if($type === 'route')
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Trajet:</strong> {{ $reviewable->departureStation->name ?? 'N/A' }} → {{ $reviewable->arrivalStation->name ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Distance:</strong> {{ $reviewable->distance }} km
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Prix:</strong> {{ number_format($reviewable->price, 0, ',', ' ') }} FCFA
                                </p>
                            @elseif($type === 'vehicle')
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Véhicule:</strong> {{ $reviewable->name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Type:</strong> {{ $reviewable->type }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Capacité:</strong> {{ $reviewable->capacity }} places
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Note</h3>
                        <div class="flex items-center gap-2" x-data="{ rating: {{ old('rating', 5) }} }">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" 
                                        @click="rating = {{ $i }}"
                                        class="focus:outline-none">
                                    <svg class="h-8 w-8 transition-colors" 
                                         :class="rating >= {{ $i }} ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </button>
                            @endfor
                            <input type="hidden" name="rating" x-model="rating" required>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400" x-text="rating + '/5'"></span>
                        </div>
                        @error('rating')
                            <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Commentaire -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Commentaire (optionnel)</h3>
                        <textarea name="comment" 
                                  rows="5"
                                  placeholder="Partagez votre expérience..."
                                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                        <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Votre avis sera visible après modération</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Vous pouvez modifier votre avis plus tard</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Les avis aident les autres utilisateurs</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('reviews.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600"
                        {{ !$canReview ? 'disabled' : '' }}>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Publier l'avis
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
