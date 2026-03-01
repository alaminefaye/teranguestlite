@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.laundry-requests.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux demandes blanchisserie</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Demande {{ $request->request_number }}</h1>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Résumé</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Client</dt><dd class="font-medium">{{ $request->user?->name ?? '—' }}</dd></div>
            @if($request->room)
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $request->room->room_number }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="font-medium">{{ $request->formatted_total_price }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Prise en charge</dt><dd class="font-medium">{{ $request->pickup_time?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Livraison prévue</dt><dd class="font-medium">{{ $request->delivery_time?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                <dd>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                        @if($request->status === 'pending') bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400
                        @elseif($request->status === 'completed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400
                        @elseif($request->status === 'cancelled') bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400
                        @else bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400 @endif">
                        {{ $request->status }}
                    </span>
                </dd>
            </div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Créée le</dt><dd>{{ $request->created_at?->format('d/m/Y H:i') }}</dd></div>
        </dl>
        <div class="mt-6 flex flex-wrap gap-2">
            @if($request->status !== 'cancelled')
                <a href="{{ route('dashboard.laundry-requests.edit', $request) }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-500 text-white hover:bg-brand-600">Modifier</a>
                <form action="{{ route('dashboard.laundry-requests.cancel', $request) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer l\'annulation de cette demande ?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-warning-500 text-white hover:bg-warning-600">Annuler la demande</button>
                </form>
            @endif
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Instructions / Articles</h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap mb-4">{{ $request->special_instructions ?: '—' }}</p>
        @if(is_array($request->items) && count($request->items) > 0)
        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
            @foreach($request->items as $item)
                <li>{{ is_array($item) ? ($item['name'] ?? $item['label'] ?? json_encode($item)) : $item }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
@endsection
