@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.menu-categories.index') }}" class="hover:text-brand-500">Catégories de menu</a>
        <span>/</span>
        <span>{{ $category->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $category->name }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $category->type_name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.menu-categories.edit', $category) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            <form action="{{ route('dashboard.menu-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
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

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total articles</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total_items'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Disponibles</p>
        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['available_items'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">En vedette</p>
        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $stats['featured_items'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informations -->
    <div class="lg:col-span-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Informations</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $category->type_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Statut</label>
                    @php
                        $statusColors = [
                            'active' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                            'inactive' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$category->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ $category->status_name }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Ordre d'affichage</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $category->display_order }}</p>
                </div>

                @if($category->icon)
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Icône</label>
                    <p class="text-2xl">{{ $category->icon }}</p>
                </div>
                @endif

                @if($category->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                    <p class="text-gray-800 dark:text-white/90">{{ $category->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Articles -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Articles ({{ $category->menuItems->count() }})</h3>
                <a href="{{ route('dashboard.menu-items.create', ['category_id' => $category->id]) }}" class="text-sm text-brand-500 hover:text-brand-600">
                    + Ajouter un article
                </a>
            </div>
            
            @if($category->menuItems->count() > 0)
                <div class="space-y-3">
                    @foreach($category->menuItems as $item)
                        <div class="border-l-4 pl-4 py-2 {{ $item->is_featured ? 'border-brand-500' : 'border-gray-300 dark:border-gray-700' }}">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    @if($item->is_featured)
                                        <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endif
                                    <a href="{{ route('dashboard.menu-items.show', $item) }}" class="font-medium text-gray-800 dark:text-white/90 hover:text-brand-500">
                                        {{ $item->name }}
                                    </a>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $item->formatted_price }}</span>
                            </div>
                            @if($item->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($item->description, 80) }}</p>
                            @endif
                            @if($item->preparation_time)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">⏱️ {{ $item->preparation_time_text }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center py-8 text-gray-500 dark:text-gray-400">Aucun article dans cette catégorie</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('dashboard.menu-items.create', ['category_id' => $category->id]) }}" class="block w-full px-4 py-2 text-center bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    Ajouter un article
                </a>
                <a href="{{ route('dashboard.menu-categories.edit', $category) }}" class="block w-full px-4 py-2 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Modifier la catégorie
                </a>
                <a href="{{ route('dashboard.menu-categories.index') }}" class="block w-full px-4 py-2 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
