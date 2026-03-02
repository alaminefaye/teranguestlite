@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.gallery.index') }}" class="hover:text-brand-500">Galerie</a>
        <span>/</span>
        <span>{{ $album->name }}</span>
        <span>/</span>
        <span>Photos</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Photos : {{ $album->name }}</h1>
        <a href="{{ route('dashboard.gallery.index') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">← Retour à la galerie</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Ajouter une photo -->
<div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Ajouter une photo</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Taille max : 20 Mo. Formats : JPG, PNG, etc.</p>
    <form action="{{ route('dashboard.gallery-albums.photos.store', $album) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
        @csrf
        <div class="min-w-[200px]">
            <input type="file" name="photo" id="photo" accept="image/*" required
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            @error('photo')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>
        <div class="min-w-[180px]">
            <label for="title" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Titre (optionnel)</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div class="min-w-[180px]">
            <label for="description" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description (optionnel)</label>
            <input type="text" name="description" id="description" value="{{ old('description') }}"
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Ajouter</button>
    </form>
</div>

<!-- Liste des photos -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-6">
        @forelse($photos as $photo)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <a href="{{ $photo->url }}" target="_blank" class="block aspect-square bg-gray-100 dark:bg-gray-800">
                    <img src="{{ $photo->url }}" alt="{{ $photo->title ?? 'Photo' }}" class="w-full h-full object-cover">
                </a>
                <div class="p-2">
                    @if($photo->title)<p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate">{{ $photo->title }}</p>@endif
                    <form action="{{ route('dashboard.gallery-albums.photos.destroy', [$album, $photo]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette photo ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-error-600 dark:text-error-400 hover:underline">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                Aucune photo. Ajoutez-en une avec le formulaire ci-dessus.
            </div>
        @endforelse
    </div>
</div>
@endsection
