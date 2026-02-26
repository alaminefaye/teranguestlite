@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.staff.index') }}" class="hover:text-brand-500">Staff</a>
        <span>/</span>
        <span>Modifier {{ $staff->name }}</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier {{ $staff->name }}</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifier les informations et l'affectation au département.</p>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.staff.update', $staff->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom complet <span class="text-error-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email <span class="text-error-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('email')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Département</label>
                <select name="department" id="department"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">— Aucun —</option>
                    @foreach($departmentOptions as $dept)
                        <option value="{{ $dept }}" {{ old('department', $staff->department) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                @error('department')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div></div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sections à gérer (app + notifications)</label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Ce membre ne verra et ne recevra des notifications que pour les sections cochées.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @php $managed = old('managed_sections', $staff->managed_sections ?? []); @endphp
                    @foreach($sectionOptions ?? [] as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="managed_sections[]" id="section_{{ $key }}" value="{{ $key }}"
                                {{ in_array($key, $managed) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <label for="section_{{ $key }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                @error('managed_sections')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nouveau mot de passe</label>
                <input type="password" name="password" id="password" autocomplete="new-password"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('password')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Laisser vide pour conserver le mot de passe actuel. Sinon, minimum 8 caractères.</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Enregistrer
            </button>
            <a href="{{ route('dashboard.staff.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
