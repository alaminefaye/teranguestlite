@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('admin.announcements.index') }}" class="hover:text-brand-500">Annonces</a>
        <span>/</span>
        <span>Nouvelle annonce</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Nouvelle annonce (Super Admin)</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Titre --}}
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Titre (optionnel)</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('title')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Affiche --}}
            <div>
                <label for="poster" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Affiche (image)</label>
                <input type="file" name="poster" id="poster" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPEG, PNG, WebP, GIF – max 50 Mo. Au moins une affiche ou vidéo requise.</p>
                @error('poster')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Vidéo --}}
            <div>
                <label for="video" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vidéo</label>
                <input type="file" name="video" id="video" accept=".mp4,.webm,.mov,.ogv"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">MP4, WebM, MOV, OGV – max 50 Mo. Audio désactivé par défaut dans l'app.</p>
                @error('video')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Durée d'affichage (affiche seule) --}}
            <div>
                <label for="display_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée d'affichage affiche seule (minutes)</label>
                <input type="number" name="display_duration_minutes" id="display_duration_minutes" value="{{ old('display_duration_minutes', 1) }}" min="1" max="60"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Utilisé uniquement si l'annonce est une affiche sans vidéo. Défaut : 1 min.</p>
                @error('display_duration_minutes')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Ordre d'affichage --}}
            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Dates --}}
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de début (optionnel)</label>
                <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('starts_at')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de fin (optionnel)</label>
                <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('ends_at')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Ciblage entreprises --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ciblage entreprises</label>
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" name="target_all_enterprises" id="target_all_enterprises" value="1" {{ old('target_all_enterprises') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                        onchange="document.getElementById('enterprise-selector').style.display = this.checked ? 'none' : 'block'">
                    <label for="target_all_enterprises" class="text-sm text-gray-700 dark:text-gray-300">Diffuser à <strong>toutes</strong> les entreprises</label>
                </div>
                <div id="enterprise-selector" class="{{ old('target_all_enterprises') ? 'hidden' : '' }}">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Sélectionner les entreprises cibles (laissez vide pour n'en cibler aucune) :</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3">
                        @foreach($enterprises as $enterprise)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="enterprise_ids[]" value="{{ $enterprise->id }}"
                                    {{ in_array($enterprise->id, (array) old('enterprise_ids', [])) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                                {{ $enterprise->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('enterprise_ids')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Actif --}}
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Annonce active</label>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Créer l'annonce</button>
            <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
