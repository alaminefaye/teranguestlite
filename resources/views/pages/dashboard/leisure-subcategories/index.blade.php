@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.leisure-categories.index') }}" class="hover:text-brand-500">Bien-être, Sport & Loisirs</a>
        <span>/</span>
        <span>{{ $parent->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Activités : {{ $parent->name }}</h1>
        <a href="{{ route('dashboard.leisure-categories.subcategories.create', $parent) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm font-medium">+ Ajouter une activité</a>
    </div>
</div>

<p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Ces activités s'affichent dans l'app lorsque le client choisit « {{ $parent->name }} ». Le type détermine l'écran ouvert (Golf & Tennis, Fitness, Spa, etc.).</p>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:text-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ordre</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type (écran app)</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($subcategories as $sub)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $sub->display_order }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $sub->name }}</td>
                    <td class="px-4 py-3 text-sm text-brand-600 dark:text-brand-400">{{ $sub->type_label }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('dashboard.leisure-categories.subcategories.edit', [$parent, $sub]) }}" class="text-brand-600 dark:text-brand-400 hover:underline text-sm">Modifier</a>
                        <form action="{{ route('dashboard.leisure-categories.subcategories.destroy', [$parent, $sub]) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer cette activité ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-error-600 dark:text-error-400 hover:underline text-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Aucune activité. <a href="{{ route('dashboard.leisure-categories.subcategories.create', $parent) }}" class="text-brand-600 dark:text-brand-400 hover:underline">Ajouter une activité</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
