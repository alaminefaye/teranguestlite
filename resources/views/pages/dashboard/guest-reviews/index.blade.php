@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Avis clients</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Retours et évaluations laissés par les invités après commande livrée, check-out, demande traitée ou excursion terminée.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<!-- Statistiques -->
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total avis</p>
        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:border-warning-900/20">
        <p class="text-sm text-warning-600 dark:text-warning-400">Note moyenne</p>
        <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $stats['avg_rating'] }}/5</p>
    </div>
    <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
        <p class="text-sm text-success-600 dark:text-success-400">5 étoiles</p>
        <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ $stats['rating_5'] }}</p>
    </div>
</div>

<!-- Filtres -->
<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <form method="GET" action="{{ route('dashboard.guest-reviews.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email ou commentaire..."
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Note</label>
            <select name="rating" class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 min-w-[100px]">
                <option value="">Toutes</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') === (string)$i ? 'selected' : '' }}>{{ $i }} étoile(s)</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Filtrer</button>
        @if(request()->hasAny(['search', 'rating']))
            <a href="{{ route('dashboard.guest-reviews.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Réinitialiser</a>
        @endif
    </form>
</div>

<!-- Liste des avis -->
<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Client</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Élément noté</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Note</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Commentaire</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $review->user->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $review->user->email ?? '' }}</div>
                            @if($review->room)
                                <div class="text-xs text-gray-500 dark:text-gray-400">Chambre {{ $review->room->room_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ $review->reviewable_label }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                                <span class="ml-1 text-sm text-gray-600 dark:text-gray-400">({{ $review->rating }}/5)</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($review->comment ?? '—', 80) }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $review->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            Aucun avis pour le moment. Les avis apparaîtront ici lorsque les invités en laisseront depuis l’application (Profil → Avis).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection
