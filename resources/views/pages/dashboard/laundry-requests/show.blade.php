@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard.laundry-requests.index') }}" class="text-brand-600 dark:text-brand-400 text-sm mb-2 inline-block">← Retour aux demandes blanchisserie</a>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Demande {{ $request->request_number }}</h1>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Résumé</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">N° Demande</dt><dd class="font-mono font-medium">{{ $request->request_number }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date</dt><dd>{{ $request->created_at?->format('d/m/Y H:i') }}</dd></div>
            @if($request->room)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $request->room->room_number }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="font-medium">{{ $request->formatted_total_price }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                <dd><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium @if($request->status === 'pending') bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400 @elseif($request->status === 'completed') bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 @else bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400 @endif">{{ $request->status }}</span></dd>
            </div>
            @if($request->special_instructions)
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <dt class="text-gray-500 dark:text-gray-400 mb-1">Instructions</dt>
                <dd class="text-gray-800 dark:text-white/90 whitespace-pre-wrap">{{ $request->special_instructions }}</dd>
            </div>
            @endif
            @if(is_array($request->items) && count($request->items) > 0)
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <dt class="text-gray-500 dark:text-gray-400 mb-1">Articles</dt>
                <dd class="text-gray-800 dark:text-white/90">{{ is_string($request->items) ? $request->items : implode(', ', array_map(fn($i) => is_array($i) ? ($i['name'] ?? $i['label'] ?? json_encode($i)) : $i, $request->items)) }}</dd>
            </div>
            @endif
        </dl>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Client</h2>
        @if($request->guest)
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nom</dt><dd class="font-medium">{{ $request->guest->name }}</dd></div>
            @if($request->guest->email)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd>{{ $request->guest->email }}</dd></div>@endif
            @if($request->guest->phone)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Téléphone</dt><dd>{{ $request->guest->phone }}</dd></div>@endif
            @if($request->room)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Chambre</dt><dd class="font-medium">{{ $request->room->room_number }}</dd></div>@endif
        </dl>
        @else
        <p class="text-gray-500 dark:text-gray-400 text-sm">@if($request->room)Client Chambre {{ $request->room->room_number }}@else—@endif</p>
        @endif
    </div>
</div>
@endsection
