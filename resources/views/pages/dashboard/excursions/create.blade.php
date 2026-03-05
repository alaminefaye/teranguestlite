@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <a href="{{ route('dashboard.excursions.index') }}" class="hover:text-brand-500">Excursions</a>
            <span>/</span>
            <span>Créer une excursion</span>
        </div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer une excursion</h1>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('dashboard.excursions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span
                            class="text-error-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('name')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span
                            class="text-error-500">*</span></label>
                    <select name="type" id="type" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                        <option value="cultural" {{ old('type') == 'cultural' ? 'selected' : '' }}>Culturel</option>
                        <option value="adventure" {{ old('type') == 'adventure' ? 'selected' : '' }}>Aventure</option>
                        <option value="relaxation" {{ old('type') == 'relaxation' ? 'selected' : '' }}>Détente</option>
                        <option value="city_tour" {{ old('type') == 'city_tour' ? 'selected' : '' }}>Tour de ville</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="duration_hours"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée (heures) <span
                            class="text-error-500">*</span></label>
                    <input type="number" name="duration_hours" id="duration_hours" value="{{ old('duration_hours', 4) }}"
                        required min="1"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('duration_hours')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="departure_time"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Heure de départ</label>
                    <input type="text" name="departure_time" id="departure_time" value="{{ old('departure_time') }}"
                        placeholder="ex: 09:00"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('departure_time')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="schedule_description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Horaires (détail)</label>
                    <textarea name="schedule_description" id="schedule_description" rows="3"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="ex: Départ 09h00, pause déjeuner 12h30-14h, retour 18h">{{ old('schedule_description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Décrivez les horaires et le déroulé de l'activité.</p>
                    @error('schedule_description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price_adult" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix
                        adulte (FCFA) <span class="text-error-500">*</span></label>
                    <input type="number" name="price_adult" id="price_adult" value="{{ old('price_adult') }}" required
                        min="0" step="0.01"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('price_adult')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price_child" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prix
                        enfant (FCFA)</label>
                    <input type="number" name="price_child" id="price_child" value="{{ old('price_child') }}" min="0"
                        step="0.01"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('price_child')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="children_age_range"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tranche d'âge enfants</label>
                    <input type="text" name="children_age_range" id="children_age_range" value="{{ old('children_age_range') }}"
                        placeholder="ex: 3-12 ans"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Indiquez la tranche d'âge applicable aux enfants.</p>
                    @error('children_age_range')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="min_participants"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants min <span
                            class="text-error-500">*</span></label>
                    <input type="number" name="min_participants" id="min_participants"
                        value="{{ old('min_participants', 1) }}" required min="1"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('min_participants')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_participants"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants max</label>
                    <input type="number" name="max_participants" id="max_participants" value="{{ old('max_participants') }}"
                        min="1"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('max_participants')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span
                            class="text-error-500">*</span></label>
                    <select name="status" id="status"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Disponible
                        </option>
                        <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>Indisponible
                        </option>
                        <option value="seasonal" {{ old('status') == 'seasonal' ? 'selected' : '' }}>Saisonnier</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordre
                        d'affichage</label>
                    <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}"
                        min="0"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @error('display_order')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                    <label for="is_featured" class="ml-2 text-sm text-gray-700 dark:text-gray-300">En vedette</label>
                </div>

                <div class="md:col-span-2">
                    <label for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description détaillée</label>
                    <textarea name="description" id="description" rows="5"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="included" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Inclus (un
                        élément par ligne)</label>
                    <textarea name="included" id="included" rows="3"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Transport&#10;Guide&#10;Déjeuner">{{ old('included') }}</textarea>
                    @error('included')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="not_included" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Non
                        inclus (un élément par ligne)</label>
                    <textarea name="not_included" id="not_included" rows="2"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Assurance&#10;Pourboires">{{ old('not_included') }}</textarea>
                    @error('not_included')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommandé : 800×600 px, max 30 Mo.</p>
                    @error('image')<p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">Créer</button>
                <a href="{{ route('dashboard.excursions.index') }}"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</a>
            </div>
        </form>
    </div>
@endsection