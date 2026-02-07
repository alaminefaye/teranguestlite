@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.guests.index') }}" class="hover:text-brand-500">Clients</a>
        <span>/</span>
        <span>Enregistrer un client</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Enregistrer un client</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Formulaire complet d'enregistrement. Un code à 6 chiffres sera généré pour la tablette en chambre.</p>
</div>

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
    <form action="{{ route('dashboard.guests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Identité --}}
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Identité</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom complet <span class="text-error-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('name')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Genre</label>
                    <select name="gender" id="gender" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                        <option value="">—</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Homme</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Femme</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de naissance</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('date_of_birth')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationalité</label>
                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality') }}" placeholder="ex. Sénégalaise, Française"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('nationality')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Pièce d'identité --}}
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Pièce d'identité</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="id_document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de pièce</label>
                    <select name="id_document_type" id="id_document_type" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                        <option value="">—</option>
                        <option value="CNI" {{ old('id_document_type') === 'CNI' ? 'selected' : '' }}>CNI</option>
                        <option value="Passeport" {{ old('id_document_type') === 'Passeport' ? 'selected' : '' }}>Passeport</option>
                        <option value="Carte de séjour" {{ old('id_document_type') === 'Carte de séjour' ? 'selected' : '' }}>Carte de séjour</option>
                        <option value="Permis" {{ old('id_document_type') === 'Permis' ? 'selected' : '' }}>Permis de conduire</option>
                        <option value="Autre" {{ old('id_document_type') === 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label for="id_document_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de la pièce</label>
                    <input type="text" name="id_document_number" id="id_document_number" value="{{ old('id_document_number') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('id_document_number')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="id_document_place_of_issue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lieu de délivrance</label>
                    <input type="text" name="id_document_place_of_issue" id="id_document_place_of_issue" value="{{ old('id_document_place_of_issue') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                </div>
                <div>
                    <label for="id_document_issued_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de délivrance</label>
                    <input type="date" name="id_document_issued_at" id="id_document_issued_at" value="{{ old('id_document_issued_at') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                </div>
                <div class="md:col-span-2">
                    <label for="id_document_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo / scan de la pièce (JPEG, PNG ou PDF, max 5 Mo)</label>
                    <input type="file" name="id_document_photo" id="id_document_photo" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-brand-500 file:text-white">
                    @error('id_document_photo')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Adresse --}}
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Adresse</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" placeholder="Rue, numéro, complément"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                </div>
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pays</label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}" placeholder="ex. Sénégal"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Contact</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('email')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="ex. +221 77 123 45 67"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
                    @error('phone')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="p-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Remarques, préférences, etc."
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex gap-3">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">Enregistrer le client</button>
            <a href="{{ route('dashboard.guests.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md">Annuler</a>
        </div>
    </form>
</div>
@endsection
