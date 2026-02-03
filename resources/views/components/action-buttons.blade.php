@props(['showRoute' => null, 'editRoute' => null, 'deleteRoute' => null, 'deleteMessage' => 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'canDelete' => true])

<div class="flex items-center justify-end gap-2">
    @if($showRoute)
        <a href="{{ $showRoute }}" 
           class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600 hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400 dark:hover:bg-primary-500/20 transition-colors"
           title="Voir les détails">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
        </a>
    @endif

    @if($editRoute)
        <a href="{{ $editRoute }}" 
           class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20 transition-colors"
           title="Modifier">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </a>
    @endif

    @if($deleteRoute && $canDelete)
        <form action="{{ $deleteRoute }}" method="POST" class="inline" onsubmit="return confirm('{{ $deleteMessage }}')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-error-50 text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20 transition-colors"
                    title="Supprimer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </form>
    @endif
</div>
