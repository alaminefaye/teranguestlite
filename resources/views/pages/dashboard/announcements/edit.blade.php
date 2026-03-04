@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.announcements.index') }}" class="hover:text-brand-500">Annonces</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier l'annonce</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Titre --}}
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Titre (optionnel)</label>
                <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('title')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Affiche actuelle + nouveau upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Affiche (image)</label>
                @if($announcement->poster_path)
                    <img src="{{ asset('storage/' . $announcement->poster_path) }}" alt="Affiche actuelle" class="h-24 w-auto rounded-md mb-2 border border-gray-200 dark:border-gray-700">
                    <label class="flex items-center gap-2 text-xs text-error-600 dark:text-error-400 mb-2 cursor-pointer">
                        <input type="checkbox" name="remove_poster" value="1" {{ old('remove_poster') ? 'checked' : '' }} class="h-3 w-3 rounded border-gray-300">
                        Supprimer l'affiche actuelle
                    </label>
                @endif
                <input type="file" name="poster" id="poster" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPEG, PNG, WebP, GIF – max 20 Mo.</p>
                @error('poster')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Vidéo actuelle + nouveau upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vidéo</label>
                @if($announcement->video_path)
                    <p class="text-xs text-success-600 dark:text-success-400 mb-2">✓ Une vidéo est actuellement chargée.</p>
                    <label class="flex items-center gap-2 text-xs text-error-600 dark:text-error-400 mb-2 cursor-pointer">
                        <input type="checkbox" name="remove_video" value="1" {{ old('remove_video') ? 'checked' : '' }} class="h-3 w-3 rounded border-gray-300">
                        Supprimer la vidéo actuelle
                    </label>
                @endif
                <input type="file" name="video" id="video" accept=".mp4,.webm,.mov,.ogv"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">MP4, WebM, MOV, OGV – max 20 Mo.</p>
                @error('video')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Durée + ordre --}}
            <div>
                <label for="display_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée affiche seule (minutes)</label>
                <input type="number" name="display_duration_minutes" id="display_duration_minutes"
                    value="{{ old('display_duration_minutes', $announcement->display_duration_minutes ?? 1) }}" min="1" max="60"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('display_duration_minutes')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre d'affichage</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $announcement->display_order) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Dates --}}
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de début</label>
                <input type="datetime-local" name="starts_at" id="starts_at"
                    value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('starts_at')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de fin</label>
                <input type="datetime-local" name="ends_at" id="ends_at"
                    value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('ends_at')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            {{-- Ciblage entreprises --}}
            @php $selectedEnterpriseIds = $announcement->targetEnterprises->pluck('id')->toArray(); @endphp
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ciblage entreprises</label>
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" name="target_all_enterprises" id="target_all_enterprises" value="1"
                        {{ old('target_all_enterprises', $announcement->target_all_enterprises) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                        onchange="document.getElementById('enterprise-selector').style.display = this.checked ? 'none' : 'block'">
                    <label for="target_all_enterprises" class="text-sm text-gray-700 dark:text-gray-300">Diffuser à <strong>toutes</strong> les entreprises</label>
                </div>
                <div id="enterprise-selector" class="{{ old('target_all_enterprises', $announcement->target_all_enterprises) ? 'hidden' : '' }}">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3">
                        @foreach($enterprises as $enterprise)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="enterprise_ids[]" value="{{ $enterprise->id }}"
                                    {{ in_array($enterprise->id, (array) old('enterprise_ids', $selectedEnterpriseIds)) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                                {{ $enterprise->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actif--}}
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Annonce active</label>
            </div>

            {{-- Stats vues --}}
            <div class="flex items-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">👁 <strong>{{ number_format($announcement->view_count) }}</strong> vue(s) enregistrée(s)</p>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Enregistrer</button>
            <a href="{{ route('dashboard.announcements.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
