@extends('layouts.app')

@section('content')
<div class="mb-6">
    <nav class="flex gap-2 text-sm text-gray-600 dark:text-gray-400">
        <a href="{{ route('dashboard.tablet-accesses.index') }}" class="hover:text-brand-500">Accès tablettes</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">Nouvel accès</span>
    </nav>
</div>

<h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90 mb-2">Nouvel accès tablette</h1>
<p class="text-gray-600 dark:text-gray-400 mb-6">Créez un compte « Client Chambre XXX » pour que la tablette de cette chambre puisse se connecter (email + mot de passe).</p>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

@if($rooms->isEmpty())
    <div class="rounded-lg border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-900 text-center">
        <p class="text-gray-600 dark:text-gray-400">Toutes les chambres ont déjà un accès tablette, ou aucune chambre n'existe encore.</p>
        <a href="{{ route('dashboard.tablet-accesses.index') }}" class="mt-4 inline-block text-brand-500 hover:text-brand-600">Retour</a>
        <span class="mx-2">|</span>
        <a href="{{ route('dashboard.rooms.index') }}" class="inline-block text-brand-500 hover:text-brand-600">Gérer les chambres</a>
    </div>
@else
    <form action="{{ route('dashboard.tablet-accesses.store') }}" method="POST" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 max-w-lg">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chambre <span class="text-error-500">*</span></label>
                <select name="room_id" id="room_id" required class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                    <option value="">Choisir une chambre</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id', $preselectedRoomId ?? null) == $room->id ? 'selected' : '' }}>
                            {{ $room->room_number }} ({{ ($roomTypeLabels ?? [])[$room->type ?? ''] ?? '—' }})
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom affiché (optionnel)</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="ex. Client Chambre 101"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Par défaut : « Client Chambre XXX »</p>
                @error('name')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-error-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('email')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mot de passe <span class="text-error-500">*</span></label>
                <input type="password" name="password" id="password" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
                @error('password')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirmer le mot de passe <span class="text-error-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90">
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Créer l'accès tablette</button>
            <a href="{{ route('dashboard.tablet-accesses.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
        </div>
    </form>
@endif
@endsection
