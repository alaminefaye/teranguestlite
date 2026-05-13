@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center gap-4">
    <a href="{{ route('dashboard.hotel-infos-security.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm flex items-center gap-1">
        ← Hotel Infos & Sécurité
    </a>
</div>

<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">⚙️ Boîte « Hôtel » (application mobile)</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choisissez entre le livret d'accueil (Wi‑Fi, plan, règlement…) ou un document unique (PDF / image) à la place.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-600 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
@endif

<form action="{{ route('dashboard.hotel-box-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">Mode d'affichage dans l'app</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="relative flex cursor-pointer rounded-xl border-2 p-5 transition-all
                {{ ($hotelBoxSettings['display_mode'] ?? 'catalog') === 'catalog' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-brand-300' }}">
                <input type="radio" name="display_mode" value="catalog" class="sr-only"
                    {{ ($hotelBoxSettings['display_mode'] ?? 'catalog') === 'catalog' ? 'checked' : '' }}
                    onchange="this.form.querySelector('#document-section').style.display='none'">
                <div class="flex items-start gap-4">
                    <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/40 text-2xl">🏨</div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">Livret d'accueil</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Wi‑Fi, plan, règlement et infos pratiques comme aujourd'hui.</p>
                    </div>
                </div>
            </label>
            <label class="relative flex cursor-pointer rounded-xl border-2 p-5 transition-all
                {{ ($hotelBoxSettings['display_mode'] ?? 'catalog') === 'document' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-brand-300' }}">
                <input type="radio" name="display_mode" value="document" class="sr-only"
                    {{ ($hotelBoxSettings['display_mode'] ?? 'catalog') === 'document' ? 'checked' : '' }}
                    onchange="this.form.querySelector('#document-section').style.display='block'">
                <div class="flex items-start gap-4">
                    <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/40 text-2xl">📄</div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">Document (PDF / Image)</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Un seul fichier affiché dans l'app à la place du livret.</p>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <div id="document-section"
         class="mb-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
         style="{{ ($hotelBoxSettings['display_mode'] ?? 'catalog') === 'document' ? '' : 'display:none' }}">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-1">Document</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">PDF, JPG, PNG, WEBP — max 20 Mo.</p>
        @if(!empty($hotelBoxSettings['document_url']))
            <div class="mb-4 flex items-center gap-3 rounded-lg border border-success-200 bg-success-50 p-3 dark:border-success-800 dark:bg-success-900/20">
                <span class="text-success-600 dark:text-success-400 text-xl">✅</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-success-700 dark:text-success-300">Fichier actuel</p>
                    <a href="{{ $hotelBoxSettings['document_url'] }}" target="_blank" class="text-xs text-success-600 dark:text-success-400 hover:underline truncate block">
                        {{ basename($hotelBoxSettings['document_path'] ?? $hotelBoxSettings['document_url']) }}
                    </a>
                </div>
                <a href="{{ $hotelBoxSettings['document_url'] }}" target="_blank" class="text-xs text-brand-600 dark:text-brand-400 hover:underline shrink-0">Voir ↗</a>
            </div>
        @endif
        <div class="flex items-center justify-center w-full">
            <label for="document-upload" class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="mb-1 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-600 dark:text-brand-400">Choisir un fichier</span></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">PDF, JPG, PNG, WEBP — max 20 Mo</p>
                </div>
                <input id="document-upload" type="file" name="document" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="document.getElementById('file-name').textContent=this.files[0]?('📎 '+this.files[0].name):'';document.getElementById('file-name').classList.toggle('hidden',!this.files[0])">
            </label>
        </div>
        <p id="file-name" class="mt-2 text-sm text-gray-500 dark:text-gray-400 hidden"></p>
        @error('document')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('dashboard.hotel-infos-security.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</a>
        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Enregistrer</button>
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
</script>
@endsection
