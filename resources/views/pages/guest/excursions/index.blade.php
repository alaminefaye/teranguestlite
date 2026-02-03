@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Excursions</h1>
</div>

<div class="grid grid-cols-1 gap-4">
    @forelse($excursions as $excursion)
        <a href="{{ route('guest.excursions.show', $excursion) }}" class="tablet-card rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white/90">{{ $excursion->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $excursion->description }}</p>
                    <div class="flex items-center gap-4 mt-3 text-sm">
                        <span class="text-brand-600 dark:text-brand-400 font-semibold">{{ $excursion->formatted_price_adult }} / adulte</span>
                        <span class="text-gray-500">⏱️ {{ $excursion->duration_hours }}h</span>
                        @if($excursion->departure_time)
                            <span class="text-gray-500">🕐 Départ {{ substr($excursion->departure_time, 0, 5) }}</span>
                        @endif
                    </div>
                </div>
                @if($excursion->is_featured)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-warning-50 text-warning-600">⭐ Populaire</span>
                @endif
            </div>
        </a>
    @empty
        <div class="text-center py-12"><p class="text-gray-600">Aucune excursion disponible</p></div>
    @endforelse
</div>
@endsection
