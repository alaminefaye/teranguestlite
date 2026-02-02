@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.show', $rental) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">État des Lieux - Début</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Location #{{ $rental->id }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Photos de l'état des lieux</h3>
                
                @if($rental->start_photos && count($rental->start_photos) > 0)
                    <div class="mb-6">
                        <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Photos existantes</h4>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($rental->start_photos as $photo)
                                <div class="relative group">
                                    <img src="{{ Storage::url($photo) }}" 
                                         alt="Photo" 
                                         class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <form action="{{ route('rentals.photos.delete', $rental) }}" method="POST" class="absolute top-1 right-1">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="photo_path" value="{{ $photo }}">
                                        <input type="hidden" name="type" value="start">
                                        <button type="submit" 
                                                onclick="return confirm('Supprimer cette photo ?')"
                                                class="rounded-full bg-error-500 p-1 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('rentals.condition.start.store', $rental) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Ajouter des photos (max 10, 5MB chacune)
                            </label>
                            <input type="file" 
                                   name="photos[]" 
                                   multiple
                                   accept="image/*"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('photos')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                            @error('photos.*')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Prenez des photos de l'extérieur, de l'intérieur, et de tout dommage éventuel.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Notes sur l'état du véhicule
                            </label>
                            <textarea name="condition_notes" 
                                      rows="4" 
                                      placeholder="Décrivez l'état général du véhicule, les éventuels dommages, rayures, etc."
                                      class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('condition_notes', $rental->start_condition_notes) }}</textarea>
                            @error('condition_notes')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('rentals.show', $rental) }}" 
                               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                Annuler
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Conseils</h3>
                <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Photographiez tous les angles du véhicule</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Notez tous les dommages visibles</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Vérifiez l'intérieur et l'extérieur</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Prenez des photos de bonne qualité</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
