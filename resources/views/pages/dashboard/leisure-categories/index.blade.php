@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Bien-être, Sport & Loisirs</h1>
    <a href="{{ route('dashboard.leisure-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Ajouter une catégorie</a>
</div>

<p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Deux catégories principales (Sport, Loisirs) s'affichent dans l'app. Cliquez sur « Gérer les activités » pour ajouter les sports ou loisirs proposés (Golf & Tennis, Fitness, Spa, etc.). Tout est dynamique comme Amenities & Conciergerie.</p>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($categories as $category)
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 {{ !$category->is_active ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    @if(!$category->is_active)<span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Masquée</span>@endif
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $category->name }}</h3>
                    <p class="text-sm text-brand-600 dark:text-brand-400">{{ $category->type_label }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $category->children_count }} activité(s)</p>
                    @if($category->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3 flex-wrap">
                <a href="{{ route('dashboard.leisure-categories.subcategories.index', $category) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-brand-500 text-white rounded hover:bg-brand-600">Gérer les activités</a>
                <a href="{{ route('dashboard.leisure-categories.edit', $category) }}" class="inline-flex items-center px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-800">Modifier</a>
                <form action="{{ route('dashboard.leisure-categories.toggle', $category) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-2 py-1 text-xs {{ $category->is_active ? 'text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-success-600 dark:text-success-400 border-success-300 dark:border-success-700 hover:bg-success-50 dark:hover:bg-success-900/20' }} border rounded">
                        {{ $category->is_active ? 'Masquer' : 'Afficher' }}
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Aucune catégorie principale. Créez Sport et Loisirs pour que les clients voient les deux boxes dans l'app, puis ajoutez les activités (Golf, Tennis, Spa, etc.) dans chaque catégorie.</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Si vous venez d'installer : exécutez <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan db:seed --class=LeisureSeeder</code> pour créer Sport, Loisirs et les activités par défaut pour votre établissement. Vérifiez aussi que votre compte est bien rattaché à un établissement.</p>
            <a href="{{ route('dashboard.leisure-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Ajouter une catégorie</a>
        </div>
    @endforelse
</div>

@if($categories->hasPages())
<div class="mt-6">
    {{ $categories->links() }}
</div>
@endif
@endsection
