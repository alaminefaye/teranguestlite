@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.menu-items.index') }}" class="hover:text-brand-500">Articles de menu</a>
        <span>/</span>
        <span>{{ $item->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $item->name }}</h1>
                @if($item->is_featured)
                    <svg class="w-6 h-6 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endif
            </div>
            <p class="text-gray-600 dark:text-gray-400">{{ $item->category->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.menu-items.edit', $item) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            <form action="{{ route('dashboard.menu-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-error-500 text-white rounded-md hover:bg-error-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informations principales -->
    <div class="lg:col-span-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Catégorie</label>
                    <a href="{{ route('dashboard.menu-categories.show', $item->category) }}" class="text-brand-600 dark:text-brand-400 hover:underline">
                        {{ $item->category->name }}
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Prix</label>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $item->formatted_price }}</p>
                </div>

                @if($item->preparation_time)
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Temps de préparation</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $item->preparation_time_text }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Disponibilité</label>
                    @if($item->is_available)
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            Disponible
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            Indisponible
                        </span>
                    @endif
                </div>

                @if($item->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $item->description }}</p>
                </div>
                @endif

                @if($item->ingredients && count($item->ingredients) > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Ingrédients</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($item->ingredients as $ingredient)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $ingredient }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($item->allergens && count($item->allergens) > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Allergènes</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($item->allergens as $allergen)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                                ⚠️ {{ $allergen }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Image -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Image</h3>
            @if($item->image)
                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full rounded-lg">
            @else
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aucune image</p>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('dashboard.menu-items.edit', $item) }}" class="block w-full px-4 py-2 text-center bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    Modifier l'article
                </a>
                <a href="{{ route('dashboard.menu-categories.show', $item->category) }}" class="block w-full px-4 py-2 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Voir la catégorie
                </a>
                <a href="{{ route('dashboard.menu-items.index') }}" class="block w-full px-4 py-2 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
