@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails de l'utilisateur</p>
            </div>
        </div>
        @can('edit users')
            <a href="{{ route('users.edit', $user) }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations personnelles -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations Personnelles</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Nom complet</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Téléphone</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Date d'inscription</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Rôles -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Rôles</h3>
                @if($user->roles->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->roles as $role)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium 
                                @if($role->name === 'admin') bg-purple-50 text-purple-600
                                @elseif($role->name === 'chauffeur') bg-blue-50 text-blue-600
                                @else bg-gray-50 text-gray-600
                                @endif">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun rôle attribué</p>
                @endif
            </div>

            <!-- Permissions -->
            @if($user->permissions->count() > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Permissions Directes</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->permissions as $permission)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-brand-50 text-brand-600">
                                {{ str_replace('_', ' ', $permission->name) }}
                            </span>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Ces permissions sont en plus de celles accordées par les rôles.
                    </p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Statistiques -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Statistiques</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Réservations</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->bookings()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Locations</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->vehicleRentals()->count() }}</span>
                    </div>
                    @if($user->isChauffeur())
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Véhicules assignés</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->vehicles()->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
