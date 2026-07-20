@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center gap-4">
    <a href="{{ route('dashboard.leisure-categories.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm flex items-center gap-1">
        ← Retour à Sport & Loisirs
    </a>
</div>

<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">⚙️ Paramètres d'affichage — Sport & Fitness</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choisissez comment la section Sport s'affiche dans l'application mobile.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-600 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
@endif

<form action="{{ route('dashboard.sport-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Mode d'affichage dans l'app</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="relative flex cursor-pointer rounded-xl border-2 p-5 transition-all
                {{ ($sportSettings['display_mode'] ?? 'catalog') === 'catalog' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-brand-300' }}">
                <input type="radio" name="display_mode" value="catalog" class="sr-only"
                    {{ ($sportSettings['display_mode'] ?? 'catalog') === 'catalog' ? 'checked' : '' }}
                    onchange="this.form.querySelector('#document-section').style.display='none'">
                <div class="flex items-start gap-4">
                    <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/40 text-2xl">🏋️</div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">Catalogue de services</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Affiche les horaires, activités et services sport (coach, réservation…).</p>
                    </div>
                </div>
            </label>
            <label class="relative flex cursor-pointer rounded-xl border-2 p-5 transition-all
                {{ ($sportSettings['display_mode'] ?? 'catalog') === 'document' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-brand-300' }}">
                <input type="radio" name="display_mode" value="document" class="sr-only"
                    {{ ($sportSettings['display_mode'] ?? 'catalog') === 'document' ? 'checked' : '' }}
                    onchange="this.form.querySelector('#document-section').style.display='block'">
                <div class="flex items-start gap-4">
                    <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/40 text-2xl">📄</div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">Document (PDF / Image)</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ouvre directement un document dans l'app (programme, planning, tarifs…).</p>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <div id="document-section"
         class="mb-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 {{ ($sportSettings['display_mode'] ?? 'catalog') === 'document' ? '' : 'hidden' }}">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-1">Documents à afficher dans l'app</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">L'app affiche le document de la langue choisie. Si la version anglaise n'existe pas, la version française est utilisée par défaut.</p>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Document français</p>
                @if(!empty($sportSettings['document_url_fr']))
                    <div class="mb-4 flex items-center gap-3 rounded-lg border border-success-200 bg-success-50 p-3 dark:border-success-800 dark:bg-success-900/20">
                        <span class="text-success-600 dark:text-success-400 text-xl">✅</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-success-700 dark:text-success-300">Version FR actuelle</p>
                            <a href="{{ $sportSettings['document_url_fr'] }}" target="_blank" class="text-xs text-success-600 dark:text-success-400 hover:underline truncate block">{{ basename($sportSettings['document_path_fr'] ?? $sportSettings['document_url_fr']) }}</a>
                        </div>
                        <a href="{{ $sportSettings['document_url_fr'] }}" target="_blank" class="text-xs text-brand-600 dark:text-brand-400 hover:underline shrink-0">Voir ↗</a>
                    </div>
                @endif
                <label for="document-upload-fr" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="mb-1 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-600 dark:text-brand-400">Choisir le fichier FR</span></p>
                    </div>
                    <input id="document-upload-fr" type="file" name="document_fr" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="updateFileName(this, 'file-name-fr')">
                </label>
                <p id="file-name-fr" class="mt-2 text-sm text-gray-500 dark:text-gray-400 hidden"></p>
                @error('document_fr')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Document anglais</p>
                @if(!empty($sportSettings['document_url_en']))
                    <div class="mb-4 flex items-center gap-3 rounded-lg border border-success-200 bg-success-50 p-3 dark:border-success-800 dark:bg-success-900/20">
                        <span class="text-success-600 dark:text-success-400 text-xl">✅</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-success-700 dark:text-success-300">Version EN actuelle</p>
                            <a href="{{ $sportSettings['document_url_en'] }}" target="_blank" class="text-xs text-success-600 dark:text-success-400 hover:underline truncate block">{{ basename($sportSettings['document_path_en'] ?? $sportSettings['document_url_en']) }}</a>
                        </div>
                        <a href="{{ $sportSettings['document_url_en'] }}" target="_blank" class="text-xs text-brand-600 dark:text-brand-400 hover:underline shrink-0">Voir ↗</a>
                    </div>
                @endif
                <label for="document-upload-en" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="mb-1 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-600 dark:text-brand-400">Choisir le fichier EN</span></p>
                    </div>
                    <input id="document-upload-en" type="file" name="document_en" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="updateFileName(this, 'file-name-en')">
                </label>
                <p id="file-name-en" class="mt-2 text-sm text-gray-500 dark:text-gray-400 hidden"></p>
                @error('document_en')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
        @if(!empty($sportSettings['legacy_document_url']))
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                Ancien document par défaut détecté :
                <a href="{{ $sportSettings['legacy_document_url'] }}" target="_blank" class="underline">{{ basename($sportSettings['legacy_document_path'] ?? $sportSettings['legacy_document_url']) }}</a>
            </div>
        @endif
        @error('document')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('dashboard.leisure-categories.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</a>
        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Enregistrer les paramètres</button>
    </div>
</form>

<script>
    document.querySelectorAll('input[name="display_mode"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('label:has(input[name="display_mode"])').forEach(function(label) {
                label.classList.remove('border-brand-500', 'bg-brand-50', 'dark:bg-brand-900/20');
                label.classList.add('border-gray-200', 'dark:border-gray-700');
            });
            this.closest('label').classList.add('border-brand-500', 'bg-brand-50', 'dark:bg-brand-900/20');
            this.closest('label').classList.remove('border-gray-200', 'dark:border-gray-700');
        });
    });
    function updateFileName(input, elementId) {
        var label = document.getElementById(elementId);
        if (input.files && input.files[0]) { label.textContent = '📎 ' + input.files[0].name; label.classList.remove('hidden'); }
        else { label.classList.add('hidden'); }
    }
</script>
@endsection
