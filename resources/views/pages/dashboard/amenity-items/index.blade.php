@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.amenity-categories.index') }}" class="hover:text-brand-500">Amenities & Conciergerie</a>
        <span>/</span>
        <span>{{ $category->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Articles : {{ $category->name }}</h1>
        <a href="{{ route('dashboard.amenity-categories.items.create', $category) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">+ Ajouter un article</a>
    </div>
</div>

<p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Ces articles seront proposés aux clients avec un sélecteur de quantité dans l’app mobile.</p>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ordre</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($items as $item)
                <tr class="{{ !$item->is_active ? 'opacity-70' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item->display_order }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->name }}</td>
                    <td class="px-4 py-3">
                        @if(!$item->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 mr-2">Masqué</span>@endif
                        <div class="flex items-center justify-end gap-2">
                            <x-action-buttons
                                :editRoute="route('dashboard.amenity-categories.items.edit', [$category, $item])"
                                :canDelete="false"
                            />
                            <form action="{{ route('dashboard.amenity-categories.items.toggle', [$category, $item]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm {{ $item->is_active ? 'text-amber-600 dark:text-amber-400 hover:underline' : 'text-success-600 dark:text-success-400 hover:underline' }}">{{ $item->is_active ? 'Masquer' : 'Afficher' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Aucun article. <a href="{{ route('dashboard.amenity-categories.items.create', $category) }}" class="text-brand-600 dark:text-brand-400 hover:underline">Ajouter un article</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
