@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Spa & Bien-être</h1>
</div>

@foreach($services as $category => $categoryServices)
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">{{ $categoryServices->first()->category_label }}</h2>
        <div class="grid grid-cols-1 gap-3">
            @foreach($categoryServices as $service)
                <a href="{{ route('guest.spa.show', $service) }}" class="tablet-card rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $service->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $service->description }}</p>
                            <div class="flex items-center gap-4 mt-2 text-sm">
                                <span class="text-brand-600 dark:text-brand-400 font-semibold">{{ $service->formatted_price }}</span>
                                <span class="text-gray-500 dark:text-gray-400">⏱️ {{ $service->duration_text }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endforeach
@endsection
