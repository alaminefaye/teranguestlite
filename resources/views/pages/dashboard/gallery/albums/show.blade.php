@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.gallery.index') }}" class="hover:text-brand-500">Galerie</a>
        <span>/</span>
        <span>{{ $album->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $album->name }}</h1>
        <a href="{{ route('dashboard.gallery-albums.photos.index', $album) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">Gérer les photos</a>
    </div>
</div>

@if($album->description)
    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">{{ $album->description }}</p>
@endif

<p class="mb-6 text-sm text-gray-600 dark:text-gray-400">{{ $album->photos->count() }} photo(s) dans cet album.</p>

<div class="flex items-center gap-3">
    <a href="{{ route('dashboard.gallery-albums.edit', $album) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Modifier l'album</a>
    <a href="{{ route('dashboard.gallery.index') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">← Retour à la galerie</a>
</div>
@endsection
