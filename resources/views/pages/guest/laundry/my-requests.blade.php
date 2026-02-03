@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Mes Demandes Blanchisserie</h1>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600">{{ session('success') }}</div>
@endif

<div class="space-y-4">
    @forelse($requests as $request)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $request->request_number }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ count($request->items) }} article(s)</p>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-warning-50 text-warning-600">{{ ucfirst($request->status) }}</span>
            </div>
            <p class="text-sm text-brand-600 dark:text-brand-400 font-semibold mt-2">{{ $request->formatted_total_price }}</p>
        </div>
    @empty
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg"><p class="text-gray-600">Aucune demande</p></div>
    @endforelse
</div>
@endsection
