@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('admin.guide-categories.index') }}" class="hover:text-brand-500">Guides & Infos</a>
                <span>/</span>
                <span>Éléments</span>
            </div>

            <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">
                @if($category)
                    Éléments de "{{ $category->name }}"
                @else
                    Tous les éléments (Guides)
                @endif
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Gérez les contacts, adresses et lieux.</p>
        </div>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('admin.guide-items.index') }}" class="flex items-center gap-2">
                <select name="category" onchange="this.form.submit()"
                    class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.guide-items.create', ['category' => $category?->id]) }}"
                class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvel Élément
            </a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Titre
                        </th>
                        @if(!$category)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                                Catégorie</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Contact / Adresse</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">Ordre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-400 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</div>
                                @if($item->description)
                                    <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ $item->description }}</div>
                                @endif
                            </td>
                            @if(!$category)
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $item->category->name ?? 'N/A' }}
                                </td>
                            @endif
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if($item->phone)
                                <div>📞 {{ $item->phone }}</div> @endif
                                @if($item->address)
                                <div>📍 {{ $item->address }}</div> @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $item->order }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.guide-items.toggle', $item) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->is_active ? 'bg-success-50 text-success-600 hover:bg-success-100' : 'bg-error-50 text-error-600 hover:bg-error-100' }}">
                                        {{ $item->is_active ? 'Actif' : 'Inactif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <x-action-buttons :editRoute="route('admin.guide-items.edit', $item)"
                                        :deleteRoute="route('admin.guide-items.destroy', $item)"
                                        deleteMessage="Vraiment supprimer cet élément ?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $category ? 5 : 6 }}"
                                class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun élément trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection