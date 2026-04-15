@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $room->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Détails de la salle</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.seminar-rooms.edit', $room) }}" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Modifier</a>
        <a href="{{ route('dashboard.seminar-rooms.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Retour</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @if($room->image)
            <img src="{{ asset('storage/' . $room->image) }}" alt="{{ $room->name }}" class="h-56 w-full object-cover rounded-lg border border-gray-200 dark:border-gray-700 mb-5">
        @endif

        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</p>
                <p class="text-gray-800 dark:text-white/90 whitespace-pre-line">{{ $room->description ?: '—' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Équipements</p>
                @php($eq = $room->equipments ?? [])
                @if(count($eq) > 0)
                    <ul class="mt-2 space-y-1 list-disc pl-5 text-gray-800 dark:text-white/90">
                        @foreach($eq as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-800 dark:text-white/90">—</p>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacité</p>
            <p class="text-gray-800 dark:text-white/90">{{ $room->capacity ?: '—' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</p>
            <p class="text-gray-800 dark:text-white/90">{{ $room->contact_phone ?: '—' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
            <p class="text-gray-800 dark:text-white/90">{{ $room->contact_email ?: '—' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut</p>
            <p class="text-gray-800 dark:text-white/90">{{ $room->is_active ? 'Active' : 'Masquée' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ordre</p>
            <p class="text-gray-800 dark:text-white/90">{{ $room->display_order }}</p>
        </div>
    </div>
</div>
@endsection

