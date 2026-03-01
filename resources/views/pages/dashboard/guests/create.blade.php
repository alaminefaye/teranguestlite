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

<div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-visible" x-data="{ documentType: '{{ old('id_document_type', '') }}' }">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de naissance</label>
                    <x-form.date-picker name="date_of_birth" label="" placeholder="jj/mm/aaaa" :defaultDate="old('date_of_birth')" />
                    @error('date_of_birth')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2"
                    data-nationalities="{{ e(json_encode($nationalities ?? [])) }}"
                    data-initial-query="{{ e(old('nationality', '')) }}"
                    x-data="{
                        nationalities: [],
                        query: '',
                        open: false,
                        get suggestions() {
                            const q = (this.query || '').toString().toLowerCase().trim();
                            if (!q) return this.nationalities.slice(0, 5);
                            return this.nationalities.filter(n => String(n).toLowerCase().includes(q)).slice(0, 10);
                        }
                    }"
                    x-init="
                        try { nationalities = JSON.parse($el.dataset.nationalities || '[]'); } catch(e) { nationalities = []; }
                        query = ($el.dataset.initialQuery || '').trim();
                        $nextTick(() => { if ($refs.input && $refs.input.value !== undefined) query = $refs.input.value; });
                    "
                    @click.away="open = false">
                    <label for="nationality_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationalité</label>
                    <div class="relative">
                        <input type="text" x-ref="input" name="nationality" id="nationality_input" value="{{ old('nationality') }}"
                            x-model="query"
                            @focus="open = true"
                            @input="open = true"
                            placeholder="Rechercher ou choisir (ex. Sénégalaise, Française)"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 pr-10"
                            autocomplete="off">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                        <ul x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="absolute z-50 left-0 right-0 mt-1 w-full max-h-48 overflow-auto rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg py-1 text-sm">
                            <template x-for="nat in suggestions" :key="nat">
                                <li><button type="button" @click="query = nat; open = false; $refs.input.value = nat"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" x-text="nat"></button></li>
                            </template>
                            <li x-show="open && suggestions.length === 0" class="px-4 py-2 text-gray-500 text-sm">Aucune suggestion</li>
                        </ul>
                    </div>
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
                    <select name="id_document_type" id="id_document_type" x-model="documentType"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de délivrance</label>
                    <x-form.date-picker name="id_document_issued_at" label="" placeholder="jj/mm/aaaa" :defaultDate="old('id_document_issued_at')" />
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photo / scan de la pièce (JPEG, PNG ou PDF, max 5 Mo)</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Passeport : recto uniquement. Autres types : recto et verso.</p>
                    <div class="space-y-3">
                        <div>
                            <label for="id_document_photo" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Recto <span class="text-error-500">*</span></label>
                            <input type="file" name="id_document_photo" id="id_document_photo" accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-brand-500 file:text-white">
                            @error('id_document_photo')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="documentType !== 'Passeport' && documentType !== ''" x-transition>
                            <label for="id_document_photo_verso" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Verso (obligatoire sauf pour Passeport)</label>
                            <input type="file" name="id_document_photo_verso" id="id_document_photo_verso" accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-brand-500 file:text-white">
                            @error('id_document_photo_verso')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
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
