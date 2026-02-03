@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <a href="{{ route('guest.palace.index') }}" class="text-brand-600 text-sm mb-2 inline-block">← Retour</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $service->name }}</h1>
</div>

<!-- Info Service -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $service->description }}</p>
    <p class="text-sm text-brand-600 dark:text-brand-400 font-semibold">{{ $service->formatted_price }}</p>
</div>

<!-- Formulaire Demande -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Faire une demande</h2>
    <form action="{{ route('guest.palace.request', $service) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description de votre demande *</label>
                <textarea name="description" rows="4" required placeholder="Décrivez en détail votre demande..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pour quand? (optionnel)</label>
                <input type="date" name="requested_for" min="{{ date('Y-m-d') }}" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium">Envoyer la demande</button>
        </div>
    </form>
</div>
@endsection
