@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.index') }}" class="hover:text-brand-500">Dashboard</a>
        <span>/</span>
        <span>Galerie Chambres & Suites</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Galerie Chambres & Suites</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Ajoutez des photos classées par type (Chambre / Suite). Formats acceptés : JPG, PNG, WEBP, GIF, <strong>TIF/TIFF</strong>. Max&nbsp;60&nbsp;Mo par photo.
    </p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- =====================================================================
     Sections par type
     ===================================================================== --}}
@foreach($types as $typeKey => $typeLabel)
    @php $typePhotos = $photos->get($typeKey, collect()); @endphp

    <div class="mb-10 rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
         id="section-{{ $typeKey }}">

        {{-- Header section --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">
                    @if($typeKey === 'chambre') 🛌 @else 🛋️ @endif
                    {{ $typeLabel }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $typePhotos->count() }} photo(s) — glissez-déposez pour réordonner
                </p>
            </div>
            <button type="button"
                    onclick="document.getElementById('upload-form-{{ $typeKey }}').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter des photos
            </button>
        </div>

        {{-- Upload Form --}}
        <div id="upload-form-{{ $typeKey }}" class="hidden border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-6 py-5">
            <form action="{{ route('dashboard.room-gallery.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $typeKey }}">

                {{-- Zone de dépôt --}}
                <div id="drop-zone-{{ $typeKey }}"
                     class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 p-8 text-center cursor-pointer hover:border-brand-400 transition-colors"
                     ondragover="event.preventDefault(); this.classList.add('border-brand-500','bg-brand-50')"
                     ondragleave="this.classList.remove('border-brand-500','bg-brand-50')"
                     ondrop="handleDrop(event, '{{ $typeKey }}')">

                    <svg class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Glissez vos photos ici ou</p>
                    <label class="mt-2 inline-block cursor-pointer rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600 transition-colors">
                        Parcourir les fichiers
                        <input type="file"
                               id="photos-{{ $typeKey }}"
                               name="photos[]"
                               multiple
                               accept=".jpg,.jpeg,.png,.webp,.gif,.tif,.tiff,image/*"
                               class="sr-only"
                               onchange="previewFiles(this, '{{ $typeKey }}')">
                    </label>
                    <p class="mt-2 text-xs text-gray-400">JPG, PNG, WEBP, GIF, <span class="font-semibold text-amber-600 dark:text-amber-400">TIF/TIFF</span> — max 60 Mo par fichier</p>
                </div>

                {{-- Prévisualisation --}}
                <div id="preview-{{ $typeKey }}" class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 empty:hidden"></div>

                {{-- Bouton submit --}}
                <div class="mt-4 flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 text-sm font-medium transition-colors">
                        Enregistrer les photos
                    </button>
                    <button type="button"
                            onclick="document.getElementById('upload-form-{{ $typeKey }}').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:underline">
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        {{-- Grille de photos --}}
        <div class="p-6">
            @if($typePhotos->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="text-5xl mb-3 opacity-30">🖼️</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Aucune photo pour les {{ strtolower($typeLabel) }}.<br>Cliquez sur "Ajouter des photos" pour commencer.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
                     id="sortable-{{ $typeKey }}"
                     data-type="{{ $typeKey }}">
                    @foreach($typePhotos as $photo)
                        <div class="group relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 cursor-grab aspect-square shadow-sm hover:shadow-md transition-shadow"
                             data-id="{{ $photo->id }}">

                            {{-- Image --}}
                            @if(in_array($photo->original_extension, ['tif', 'tiff']))
                                {{-- Les TIFF sont toujours convertis en JPG, URL accessible --}}
                                <img src="{{ asset('storage/' . $photo->path) }}"
                                     alt="{{ $photo->title ?? 'Photo' }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.parentElement.querySelector('.tif-placeholder').classList.remove('hidden'); this.classList.add('hidden')">
                                <div class="tif-placeholder hidden w-full h-full flex items-center justify-center text-xs text-gray-500">
                                    <div class="text-center p-2">
                                        <div class="text-2xl">🖼️</div>
                                        <div class="font-mono text-xs mt-1">.TIF</div>
                                    </div>
                                </div>
                            @else
                                <img src="{{ asset('storage/' . $photo->path) }}"
                                     alt="{{ $photo->title ?? 'Photo' }}"
                                     class="w-full h-full object-cover">
                            @endif

                            {{-- Badge inactif --}}
                            @if(!$photo->is_active)
                                <div class="absolute top-1 left-1 rounded px-1.5 py-0.5 text-xs font-medium bg-amber-500 text-white">Masqué</div>
                            @endif

                            {{-- Badge extension originale --}}
                            @if(in_array($photo->original_extension, ['tif', 'tiff']))
                                <div class="absolute top-1 right-1 rounded px-1.5 py-0.5 text-xs font-bold bg-purple-600 text-white">TIF</div>
                            @endif

                            {{-- Overlay actions --}}
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">

                                @if($photo->title)
                                    <p class="text-white text-xs font-medium text-center truncate w-full">{{ $photo->title }}</p>
                                @endif

                                {{-- Éditer --}}
                                <button type="button"
                                        onclick="openEditModal({{ $photo->id }}, '{{ addslashes($photo->title ?? '') }}', '{{ addslashes($photo->description ?? '') }}', {{ $photo->is_active ? 'true' : 'false' }})"
                                        class="w-full px-2 py-1 bg-white/20 hover:bg-white/30 text-white text-xs rounded transition-colors">
                                    ✏️ Modifier
                                </button>

                                {{-- Supprimer --}}
                                <form action="{{ route('dashboard.room-gallery.destroy', $photo) }}"
                                      method="POST"
                                      class="w-full"
                                      onsubmit="return confirm('Supprimer cette photo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full px-2 py-1 bg-red-500/80 hover:bg-red-600 text-white text-xs rounded transition-colors">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>

                            {{-- Poignée de déplacement --}}
                            <div class="drag-handle absolute bottom-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity text-white cursor-grab select-none text-sm" title="Déplacer">
                                ⠿
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endforeach

{{-- ===================================================================
     Modal d'édition
     =================================================================== --}}
<div id="edit-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4"
     onclick="if(event.target===this) closeEditModal()">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 shadow-2xl overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white/90">Modifier la photo</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
        </div>
        <form id="edit-form" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre (optionnel)</label>
                <input type="text" id="edit-title" name="title" maxlength="255"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-gray-900 dark:text-white/90 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (optionnel)</label>
                <textarea id="edit-description" name="description" rows="3" maxlength="2000"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-gray-900 dark:text-white/90 text-sm resize-none focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="edit-active" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-500">
                <label for="edit-active" class="text-sm text-gray-700 dark:text-gray-300">Visible dans l'app</label>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 text-sm font-medium transition-colors">
                    Enregistrer
                </button>
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// ============================================================
// Drag & Drop – réordonnage
// ============================================================
document.querySelectorAll('[id^="sortable-"]').forEach(function(grid) {
    const type = grid.dataset.type;
    new Sortable(grid, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-30',
        onEnd: function() { saveOrder(grid, type); }
    });
});

function saveOrder(grid, type) {
    const items = Array.from(grid.querySelectorAll('[data-id]')).map(function(el, idx) {
        return { id: parseInt(el.dataset.id), order: idx };
    });
    fetch('{{ route("dashboard.room-gallery.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ items }),
    });
}

// ============================================================
// Drag & Drop – upload zone
// ============================================================
function handleDrop(event, type) {
    event.preventDefault();
    const zone = document.getElementById('drop-zone-' + type);
    zone.classList.remove('border-brand-500', 'bg-brand-50');

    const files = event.dataTransfer.files;
    const input = document.getElementById('photos-' + type);

    // Transfert des fichiers vers l'input
    const dt = new DataTransfer();
    Array.from(files).forEach(function(f) { dt.items.add(f); });
    input.files = dt.files;
    previewFiles(input, type);
}

// ============================================================
// Prévisualisation des fichiers sélectionnés
// ============================================================
function previewFiles(input, type) {
    const container = document.getElementById('preview-' + type);
    container.innerHTML = '';

    Array.from(input.files).forEach(function(file) {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800';

        const ext = file.name.split('.').pop().toLowerCase();

        if (['tif', 'tiff'].includes(ext)) {
            // Les TIFF ne sont pas prévisualisables dans le navigateur
            wrapper.innerHTML = '<div class="w-full h-full flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 text-xs p-2 text-center"><div class="text-2xl mb-1">🖼️</div><span class="font-mono font-bold text-purple-600">.TIF</span><span class="mt-1 truncate w-full text-center" title="' + file.name + '">' + file.name.substring(0, 12) + (file.name.length > 12 ? '…' : '') + '</span></div>';
        } else if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'w-full h-full object-cover';
            img.src = URL.createObjectURL(file);
            wrapper.appendChild(img);
        } else {
            wrapper.innerHTML = '<div class="w-full h-full flex items-center justify-center text-xs text-gray-400">' + ext.toUpperCase() + '</div>';
        }

        container.appendChild(wrapper);
    });
}

// ============================================================
// Modal édition
// ============================================================
function openEditModal(id, title, description, isActive) {
    document.getElementById('edit-title').value       = title;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-active').checked    = isActive;
    document.getElementById('edit-form').action       = '/dashboard/room-gallery/' + id;
    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('edit-modal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.getElementById('edit-modal').classList.remove('flex');
}
</script>
@endpush
