@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Tableau de bord</a>
        <span>/</span>
        <span>Profil</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Profil</h1>
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

<div class="space-y-6">
    {{-- Infos utilisateur --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Informations personnelles</h3>
        <div class="flex flex-wrap items-center gap-6">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                @if($enterprise && $enterprise->logo)
                    <img src="{{ asset('storage/' . $enterprise->logo) }}" alt="{{ $enterprise->name }}" class="h-full w-full object-cover">
                @else
                    <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM12 20.5C10.1198 20.5 8.38223 19.8895 6.97461 18.8566V18.8455C6.97461 16.7744 8.65354 15.0955 10.7246 15.0955H13.2746C15.3457 15.0955 17.0246 16.7744 17.0246 18.8455V18.8566C15.6171 19.8898 13.8798 20.5 12 20.5Z" /></svg>
                @endif
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $user->name ?? '—' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email ?? '—' }}</p>
                @php
                    $roleLabels = ['admin' => 'Administrateur', 'staff' => 'Personnel', 'guest' => 'Client', 'super_admin' => 'Super Admin'];
                @endphp
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $roleLabels[$user->role ?? ''] ?? ucfirst($user->role ?? '') }}</p>
                @if($enterprise)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Établissement : {{ $enterprise->name }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Données de l'établissement : l'entreprise concernée peut modifier ses données directement --}}
    @if($enterprise)
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Données de l'établissement</h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Modifiez les informations de votre établissement (nom, logo, adresse, etc.). Elles apparaîtront sur les factures et dans l'application.</p>

        <form action="{{ route('dashboard.my-enterprise.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom de l'établissement <span class="text-error-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $enterprise->name) }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('name')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $enterprise->email) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('email')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $enterprise->phone) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse</label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('address', $enterprise->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ville</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $enterprise->city) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('city')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pays</label>
                    <input type="text" name="country" id="country" value="{{ old('country', $enterprise->country) }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('country')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                @if($enterprise->logo)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Logo actuel</label>
                    <img src="{{ asset('storage/' . $enterprise->logo) }}" alt="{{ $enterprise->name }}" class="h-20 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
                </div>
                @endif

                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $enterprise->logo ? 'Changer le logo' : 'Logo' }}</label>
                    <input type="file" name="logo" id="logo" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Logo affiché sur les factures et dans l'app.</p>
                    @error('logo')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                @if($enterprise->cover_photo)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image de couverture actuelle</label>
                    <img src="{{ asset('storage/' . $enterprise->cover_photo) }}" alt="Couverture" class="max-h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
                </div>
                @endif
                <div>
                    <label for="cover_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $enterprise->cover_photo ? "Changer l'image de couverture" : 'Image de couverture (écran d\'accueil app)' }}</label>
                    <input type="file" name="cover_photo" id="cover_photo" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('cover_photo')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Aucun établissement associé à votre compte. Les données d'établissement sont modifiables par les comptes admin/staff d'un hôtel.</p>
    </div>
    @endif
</div>
@endsection
