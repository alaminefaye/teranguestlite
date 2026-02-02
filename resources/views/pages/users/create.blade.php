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
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Nouvel Utilisateur</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créer un nouveau compte utilisateur</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations de base -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations Personnelles</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Nom complet <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('name')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email <span class="text-error-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('email')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Téléphone
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   placeholder="+221 77 123 45 67"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('phone')
                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Mot de passe <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password" required
                                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @error('password')
                                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Confirmer le mot de passe <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required
                                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rôles -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Rôles</h3>
                    
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 role-checkbox" 
                                   data-role="{{ $role->name }}">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                       class="role-input h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                       onchange="updatePermissionsFromRoles()">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ ucfirst($role->name) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $role->permissions->count() }} permission(s)
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        <strong>Astuce :</strong> Sélectionnez un ou plusieurs rôles. Les permissions seront automatiquement attribuées selon les rôles choisis.
                    </p>
                </div>

                <!-- Permissions -->
                @can('manage roles')
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Permissions</h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            Les permissions des rôles sélectionnés sont pré-cochées. Vous pouvez les modifier si nécessaire.
                        </p>
                        
                        <div class="space-y-4">
                            @foreach($permissions as $group => $perms)
                                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            {{ ucfirst($group) }}
                                        </h4>
                                        <button type="button" 
                                                onclick="toggleGroup('{{ $group }}')"
                                                class="text-xs text-brand-500 hover:text-brand-600">
                                            Tout sélectionner
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                        @foreach($perms as $permission)
                                            <label class="flex items-center gap-2 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                       class="permission-input group-{{ $group }} h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                                    {{ str_replace('_', ' ', $permission->name) }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endcan
            </div>

            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Le mot de passe doit contenir au moins 8 caractères</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Un utilisateur peut avoir plusieurs rôles</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Les permissions peuvent être modifiées après création</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" 
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Créer l'utilisateur
            </button>
        </div>
    </form>
</div>

<script>
// Récupérer les permissions de chaque rôle
const rolePermissions = @json($roles->mapWithKeys(function($role) {
    return [$role->name => $role->permissions->pluck('name')->toArray()];
}));

function updatePermissionsFromRoles() {
    const selectedRoles = Array.from(document.querySelectorAll('.role-input:checked')).map(cb => cb.value);
    const permissionInputs = document.querySelectorAll('.permission-input');
    
    // Réinitialiser toutes les permissions
    permissionInputs.forEach(input => {
        input.checked = false;
    });
    
    // Cocher les permissions des rôles sélectionnés
    selectedRoles.forEach(roleName => {
        if (rolePermissions[roleName]) {
            rolePermissions[roleName].forEach(permissionName => {
                const input = document.querySelector(`.permission-input[value="${permissionName}"]`);
                if (input) {
                    input.checked = true;
                }
            });
        }
    });
}

function toggleGroup(group) {
    const checkboxes = document.querySelectorAll(`.group-${group}`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}

// Initialiser les permissions au chargement si des rôles sont présélectionnés
document.addEventListener('DOMContentLoaded', function() {
    updatePermissionsFromRoles();
});
</script>
@endsection

