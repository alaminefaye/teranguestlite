@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('admin.users.index') }}" class="hover:text-brand-500">Utilisateurs</a>
        <span>/</span>
        <span>Créer</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer un utilisateur</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nom -->
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom complet <span class="text-error-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email <span class="text-error-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('email')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rôle -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rôle <span class="text-error-500">*</span>
                </label>
                <select name="role" id="role" required x-data="{ role: '{{ old('role', 'admin') }}' }" x-model="role"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner un rôle</option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin Hôtel</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="guest" {{ old('role') === 'guest' ? 'selected' : '' }}>Guest (Client)</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Entreprise (sauf pour super_admin) -->
            <div x-data="{ role: '{{ old('role', 'admin') }}' }" x-show="role !== 'super_admin'" class="md:col-span-2">
                <label for="enterprise_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Entreprise <span class="text-error-500" x-show="role !== 'super_admin'">*</span>
                </label>
                <select name="enterprise_id" id="enterprise_id" x-bind:required="role !== 'super_admin'"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner une entreprise</option>
                    @foreach($enterprises as $enterprise)
                        <option value="{{ $enterprise->id }}" {{ old('enterprise_id') == $enterprise->id ? 'selected' : '' }}>
                            {{ $enterprise->name }}
                        </option>
                    @endforeach
                </select>
                @error('enterprise_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Département (pour admin et staff) -->
            <div x-data="{ role: '{{ old('role', 'admin') }}' }" x-show="role === 'admin' || role === 'staff'">
                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Département
                </label>
                <input type="text" name="department" id="department" value="{{ old('department') }}" placeholder="Ex: Réception, Service, etc."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('department')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Numéro de chambre (pour guest uniquement) -->
            <div x-data="{ role: '{{ old('role', 'admin') }}' }" x-show="role === 'guest'">
                <label for="room_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Numéro de chambre
                </label>
                <input type="text" name="room_number" id="room_number" value="{{ old('room_number') }}" placeholder="Ex: 101"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('room_number')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Code tablette (pour guest uniquement) -->
            <div x-data="{ role: '{{ old('role', 'admin') }}' }" x-show="role === 'guest'">
                <label for="tablet_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Code tablette
                </label>
                <input type="text" name="tablet_code" id="tablet_code" value="{{ old('tablet_code') }}" placeholder="Ex: 1234" maxlength="20"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Code utilisé par le client sur la tablette (unique par établissement).</p>
                @error('tablet_code')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mot de passe -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mot de passe <span class="text-error-500">*</span>
                </label>
                <input type="password" name="password" id="password" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('password')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmation mot de passe -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Confirmer mot de passe <span class="text-error-500">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Créer l'utilisateur
            </button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
