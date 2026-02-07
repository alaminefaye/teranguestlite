@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.guests.index') }}" class="hover:text-brand-500">Clients</a>
        <span>/</span>
        <span>{{ $guest->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $guest->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.guests.edit', $guest) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 text-sm">Modifier</a>
            <form action="{{ route('dashboard.guests.regenerate-code', $guest) }}" method="POST" class="inline" onsubmit="return confirm('Générer un nouveau code ? L\'ancien ne fonctionnera plus.');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-brand-500 text-brand-600 dark:text-brand-400 rounded-md hover:bg-brand-50 dark:hover:bg-brand-900/20 text-sm">Régénérer le code</button>
            </form>
            <form action="{{ route('dashboard.guests.destroy', $guest) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce client ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-error-300 text-error-600 rounded-md text-sm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Code tablette</h2>
        <p class="text-2xl font-mono font-bold text-brand-600 dark:text-brand-400">{{ $guest->access_code }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Utilisé par le client sur la tablette en chambre pour valider commandes et réservations.</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Contact</h2>
        <p class="text-gray-800 dark:text-white/90">{{ $guest->email ?? '—' }}</p>
        <p class="text-gray-800 dark:text-white/90 mt-1">{{ $guest->phone ?? '—' }}</p>
    </div>
</div>

{{-- Identité --}}
@if($guest->gender || $guest->date_of_birth || $guest->nationality)
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Identité</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
        @if($guest->gender)
        <dt class="text-gray-500 dark:text-gray-400">Genre</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->gender === 'male' ? 'Homme' : ($guest->gender === 'female' ? 'Femme' : 'Autre') }}</dd>
        @endif
        @if($guest->date_of_birth)
        <dt class="text-gray-500 dark:text-gray-400">Date de naissance</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->date_of_birth->format('d/m/Y') }}</dd>
        @endif
        @if($guest->nationality)
        <dt class="text-gray-500 dark:text-gray-400">Nationalité</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->nationality }}</dd>
        @endif
    </dl>
</div>
@endif

{{-- Pièce d'identité --}}
@if($guest->id_document_type || $guest->id_document_number || $guest->id_document_photo)
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Pièce d'identité</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
        @if($guest->id_document_type)
        <dt class="text-gray-500 dark:text-gray-400">Type</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->id_document_type }}</dd>
        @endif
        @if($guest->id_document_number)
        <dt class="text-gray-500 dark:text-gray-400">N°</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->id_document_number }}</dd>
        @endif
        @if($guest->id_document_place_of_issue)
        <dt class="text-gray-500 dark:text-gray-400">Lieu de délivrance</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->id_document_place_of_issue }}</dd>
        @endif
        @if($guest->id_document_issued_at)
        <dt class="text-gray-500 dark:text-gray-400">Date de délivrance</dt>
        <dd class="text-gray-800 dark:text-white/90">{{ $guest->id_document_issued_at->format('d/m/Y') }}</dd>
        @endif
    </dl>
    @if($guest->id_document_photo)
    <p class="mt-3">
        <a href="{{ asset('storage/'.$guest->id_document_photo) }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline text-sm">Voir la photo / scan de la pièce →</a>
    </p>
    @endif
</div>
@endif

{{-- Adresse --}}
@if($guest->address || $guest->city || $guest->country)
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Adresse</h2>
    <p class="text-gray-800 dark:text-white/90">
        @if($guest->address){{ $guest->address }}<br>@endif
        @if($guest->city || $guest->country){{ trim(implode(', ', array_filter([$guest->city, $guest->country]))) ?: '—' }}@endif
    </p>
</div>
@endif

@if($guest->notes)
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Notes</h2>
    <p class="text-gray-700 dark:text-gray-300">{{ $guest->notes }}</p>
</div>
@endif

<div class="mt-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">Réservations associées : {{ $guest->reservations_count }}</p>
    <a href="{{ route('dashboard.reservations.index') }}?guest_id={{ $guest->id }}" class="text-brand-600 dark:text-brand-400 text-sm hover:underline">Voir les réservations</a>
</div>
<div class="mt-6">
    <a href="{{ route('dashboard.guests.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md">← Retour à la liste</a>
</div>
@endsection
