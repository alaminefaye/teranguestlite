@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.establishments.index') }}" class="hover:text-brand-500">Nos établissements</a>
        <span>/</span>
        <span>{{ $establishment->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $establishment->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.establishments.photos.index', $establishment) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">Galerie photos</a>
            <a href="{{ route('dashboard.establishments.edit', $establishment) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium">Modifier</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        @if($establishment->cover_photo_url)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <img src="{{ $establishment->cover_photo_url }}" alt="{{ $establishment->name }}" class="w-full aspect-video object-cover">
            </div>
        @endif
        @if($establishment->description)
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">Présentation</h2>
                <div class="prose dark:prose-invert text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $establishment->description }}</div>
            </div>
        @endif
        @if($establishment->photos->isNotEmpty())
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">Galerie ({{ $establishment->photos->count() }} photo(s))</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($establishment->photos->take(6) as $photo)
                        <a href="{{ $photo->url }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? 'Photo' }}" class="w-full h-full object-cover">
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('dashboard.establishments.photos.index', $establishment) }}" class="mt-3 inline-block text-sm text-brand-600 dark:text-brand-400 hover:underline">Gerer la galerie</a>
            </div>
        @endif
    </div>
    <div>
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">Infos</h2>
            <dl class="space-y-2 text-sm">
                @if($establishment->location)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Lieu</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $establishment->location }}</dd>
                    </div>
                @endif
                @if($establishment->address)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Adresse</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $establishment->address }}</dd>
                    </div>
                @endif
                @if($establishment->phone)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Telephone</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $establishment->phone }}</dd>
                    </div>
                @endif
                @if($establishment->website)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Site web</dt>
                        <dd><a href="{{ $establishment->website }}" target="_blank" rel="noopener" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $establishment->website }}</a></dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Visible dans l'app</dt>
                    <dd class="text-gray-800 dark:text-white/90">{{ $establishment->is_active ? 'Oui' : 'Non' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
