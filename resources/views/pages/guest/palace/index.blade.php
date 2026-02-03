@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Services Palace</h1>
</div>

@foreach($services as $category => $categoryServices)
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">{{ ucfirst(str_replace('_', ' ', $category)) }}</h2>
        <div class="grid grid-cols-1 gap-3">
            @foreach($categoryServices as $service)
                <a href="{{ route('guest.palace.show', $service) }}" class="tablet-card rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $service->name }}</h3>
                                @if($service->is_premium)
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-warning-50 text-warning-600">⭐ Premium</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $service->description }}</p>
                            <p class="text-sm text-brand-600 dark:text-brand-400 font-semibold mt-2">{{ $service->formatted_price }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endforeach
@endsection
