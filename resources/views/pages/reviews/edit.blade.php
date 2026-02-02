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
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Modifier l'Avis</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Avis de {{ $review->user->name }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reviews.update', $review) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Note -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Note</h3>
                    <div class="flex items-center gap-2" x-data="{ rating: {{ old('rating', $review->rating) }} }">
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
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Commentaire</h3>
                    <textarea name="comment" 
                              rows="5"
                              placeholder="Partagez votre expérience..."
                              class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('comment', $review->comment) }}</textarea>
                    @error('comment')
                        <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(auth()->check() && (auth()->user()->can('manage reviews') || auth()->user()->role === 'admin'))
                    <!-- Modération (admin uniquement) -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Modération</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="is_approved" value="1" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Approuvé</span>
                            </label>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $review->is_visible) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Visible</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                        <p><strong>Créé le:</strong> {{ $review->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Dernière mise à jour:</strong> {{ $review->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('reviews.index') }}" 
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
