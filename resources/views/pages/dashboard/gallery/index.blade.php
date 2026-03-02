@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Dashboard</a>
        <span>/</span>
        <span>Galerie</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Galerie</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Image d'établissement et albums de photos affichés dans l'app mobile (Hotel Infos). Taille max : 20 Mo par image.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Image d'établissement (cover_photo) -->
<div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Image d'établissement</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Photo principale affichée en tête de la galerie dans l'app. Max 20 Mo.</p>
    @if($enterprise->cover_photo)
        <div class="mb-4">
            <img src="{{ asset('storage/' . $enterprise->cover_photo) }}" alt="Image établissement" class="max-h-64 rounded-lg border border-gray-200 dark:border-gray-700 object-cover">
        </div>
    @endif
    <form action="{{ route('dashboard.gallery.cover-photo.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
        @csrf
        @method('PUT')
        <div class="min-w-[200px]">
            <input type="file" name="cover_photo" id="cover_photo" accept="image/*" required
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            @error('cover_photo')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Mettre à jour</button>
    </form>
</div>

<!-- Albums -->
<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Albums</h2>
        <a href="{{ route('dashboard.gallery-albums.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">+ Créer un album</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($albums as $album)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 {{ !$album->is_active ? 'opacity-70' : '' }}">
                @if(!$album->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masqué</span>@endif
                <h3 class="font-semibold text-gray-800 dark:text-white/90 mt-1">{{ $album->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $album->photos_count }} photo(s)</p>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <a href="{{ route('dashboard.gallery-albums.photos.index', $album) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-brand-500 text-white rounded hover:bg-brand-600">Photos</a>
                    <a href="{{ route('dashboard.gallery-albums.edit', $album) }}" class="inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Modifier</a>
                    <form action="{{ route('dashboard.gallery-albums.destroy', $album) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet album et toutes ses photos ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-error-600 dark:text-error-400 hover:underline">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-8 text-gray-500 dark:text-gray-400">
                Aucun album. Créez un album pour y ajouter des photos.
            </div>
        @endforelse
    </div>
</div>
@endsection
