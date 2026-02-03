@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('admin.users.index') }}" class="hover:text-brand-500">Utilisateurs</a>
        <span>/</span>
        <span>{{ $user->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $user->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-error-500 text-error-600 rounded-md hover:bg-error-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Informations principales -->
    <div class="md:col-span-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Informations</h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</span>
                    <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->name }}</span>
                </div>

                <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                    <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->email }}</span>
                </div>

                <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Rôle</span>
                    <span class="col-span-2">
                        @php
                            $roleColors = [
                                'super_admin' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                'admin' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
                                'staff' => 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400',
                                'guest' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                            ];
                            $roleLabels = [
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin Hôtel',
                                'staff' => 'Staff',
                                'guest' => 'Guest (Client)',
                            ];
                        @endphp
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-50 text-gray-600' }}">
                            {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                        </span>
                    </span>
                </div>

                @if($user->enterprise)
                    <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Entreprise</span>
                        <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->enterprise->name }}</span>
                    </div>
                @endif

                @if($user->department)
                    <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Département</span>
                        <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->department }}</span>
                    </div>
                @endif

                @if($user->room_number)
                    <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Numéro de chambre</span>
                        <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->room_number }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-3 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de création</span>
                    <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                </div>

                <div class="grid grid-cols-3 py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Dernière modification</span>
                    <span class="col-span-2 text-sm text-gray-900 dark:text-white/90">{{ $user->updated_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte latérale -->
    <div class="md:col-span-1">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center mb-4">
                    <span class="text-3xl font-bold text-brand-600 dark:text-brand-400">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-1">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $user->email }}</p>
                
                @if($user->enterprise)
                    <div class="w-full mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Entreprise</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white/90">{{ $user->enterprise->name }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
