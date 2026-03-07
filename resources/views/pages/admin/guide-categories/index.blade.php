@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Guides & Infos</h1>
            <p class="text-gray-600 dark:text-gray-400">Gérer les guides globaux affichés sur tablettes.</p>
        </div>
        <button class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvelle Catégorie
        </button>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Nom
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Ordre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Éléments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->order }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $category->items_count }} éléments
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $category->is_active ? 'bg-success-50 text-success-600' : 'bg-error-50 text-error-600' }}">
                                    {{ $category->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <!-- Placeholder pour actions -->
                                <button class="text-brand-600 hover:text-brand-900 mx-1">Éditer (Bientôt)</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection