@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Clients (invités)</h1>
    <a href="{{ route('dashboard.guests.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700 text-sm font-medium">+ Enregistrer un client</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
    <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</p>
</div>

<form method="GET" class="mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, téléphone ou code..."
        class="rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 w-full max-w-md">
    <button type="submit" class="mt-2 md:mt-0 md:ml-2 inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-md text-sm">Rechercher</button>
</form>

<div class="rounded-lg border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contact</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code tablette</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($guests as $guest)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $guest->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        @if($guest->email){{ $guest->email }}<br>@endif
                        @if($guest->phone){{ $guest->phone }}@endif
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ $guest->access_code }}</code>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('dashboard.guests.show', $guest) }}" class="text-brand-600 dark:text-brand-400 hover:underline">Voir</a>
                        <a href="{{ route('dashboard.guests.edit', $guest) }}" class="ml-3 text-brand-600 dark:text-brand-400 hover:underline">Modifier</a>
                        <form action="{{ route('dashboard.guests.destroy', $guest) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ce client ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-error-600 dark:text-error-400 hover:underline">Suppr.</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucun client. <a href="{{ route('dashboard.guests.create') }}" class="text-brand-600">Créer un client</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($guests->hasPages())
<div class="mt-6">{{ $guests->links() }}</div>
@endif
@endsection
