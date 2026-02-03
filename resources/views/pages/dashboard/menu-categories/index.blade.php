@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Catégories de menu</h1>
        <p class="text-gray-600 dark:text-gray-400">Organiser les menus par catégories</p>
    </div>
    <a href="{{ route('dashboard.menu-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nouvelle catégorie
    </a>
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
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Actives</p>
        <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['active'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Room Service</p>
        <p class="text-2xl font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $stats['room_service'] }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Restaurant</p>
        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $stats['restaurant'] }}</p>
    </div>
</div>

<!-- Liste des catégories -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    @if($categories->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->type_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->menuItems()->count() }} articles</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                        'inactive' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$category->status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ $category->status_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <x-action-buttons 
                                    :showRoute="route('dashboard.menu-categories.show', $category)"
                                    :editRoute="route('dashboard.menu-categories.edit', $category)"
                                    :deleteRoute="route('dashboard.menu-categories.destroy', $category)"
                                    deleteMessage="Êtes-vous sûr de vouloir supprimer cette catégorie ?"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $categories->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7M3 7l2-2m0 0h14l2 2M5 5h14"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Aucune catégorie</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Commencez par créer votre première catégorie de menu.</p>
            <div class="mt-6">
                <a href="{{ route('dashboard.menu-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Créer une catégorie
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
