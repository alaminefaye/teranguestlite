@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.rooms.index') }}" class="hover:text-brand-500">Chambres</a>
        <span>/</span>
        <a href="{{ route('dashboard.rooms.show', $room) }}" class="hover:text-brand-500">Chambre {{ $room->room_number }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier chambre {{ $room->room_number }}</h1>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Numéro de chambre -->
            <div>
                <label for="room_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Numéro de chambre <span class="text-error-500">*</span>
                </label>
                <input type="text" name="room_number" id="room_number" value="{{ old('room_number', $room->room_number) }}" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('room_number')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Étage -->
            <div>
                <label for="floor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Étage
                </label>
                <input type="number" name="floor" id="floor" value="{{ old('floor', $room->floor) }}" min="0"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('floor')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Type <span class="text-error-500">*</span>
                </label>
                <select name="type" id="type" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="single" {{ old('type', $room->type) === 'single' ? 'selected' : '' }}>Chambre Simple</option>
                    <option value="double" {{ old('type', $room->type) === 'double' ? 'selected' : '' }}>Chambre Double</option>
                    <option value="suite" {{ old('type', $room->type) === 'suite' ? 'selected' : '' }}>Suite</option>
                    <option value="deluxe" {{ old('type', $room->type) === 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                    <option value="presidential" {{ old('type', $room->type) === 'presidential' ? 'selected' : '' }}>Suite Présidentielle</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Statut -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Statut <span class="text-error-500">*</span>
                </label>
                <select name="status" id="status" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="available" {{ old('status', $room->status) === 'available' ? 'selected' : '' }}>Disponible</option>
                    <option value="occupied" {{ old('status', $room->status) === 'occupied' ? 'selected' : '' }}>Occupée</option>
                    <option value="maintenance" {{ old('status', $room->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="reserved" {{ old('status', $room->status) === 'reserved' ? 'selected' : '' }}>Réservée</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prix par nuit -->
            <div>
                <label for="price_per_night" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Prix par nuit (FCFA) <span class="text-error-500">*</span>
                </label>
                <input type="number" name="price_per_night" id="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" required min="0" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('price_per_night')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacité -->
            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Capacité (personnes) <span class="text-error-500">*</span>
                </label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $room->capacity) }}" required min="1" max="10"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('capacity')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wi‑Fi chambre (optionnel : remplace l'affichage sur la tablette pour cette chambre) -->
            <div>
                <label for="wifi_network" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom du réseau Wi‑Fi (chambre)
                </label>
                <input type="text" name="wifi_network" id="wifi_network" value="{{ old('wifi_network', $room->wifi_network ?? '') }}" placeholder="Laisser vide = Wi‑Fi global de l'hôtel"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('wifi_network')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="wifi_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mot de passe Wi‑Fi (chambre)
                </label>
                <input type="text" name="wifi_password" id="wifi_password" value="{{ old('wifi_password', $room->wifi_password ?? '') }}" placeholder="Si réseau chambre renseigné"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('wifi_password')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $room->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Équipements -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Équipements</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @php
                        $amenitiesList = [
                            'wifi' => 'Wi-Fi',
                            'tv' => 'Télévision',
                            'minibar' => 'Minibar',
                            'ac' => 'Climatisation',
                            'safe' => 'Coffre-fort',
                            'balcony' => 'Balcon',
                            'bathtub' => 'Baignoire',
                            'shower' => 'Douche',
                            'hairdryer' => 'Sèche-cheveux',
                            'phone' => 'Téléphone',
                            'ironing' => 'Fer à repasser',
                            'desk' => 'Bureau',
                        ];
                        $selectedAmenities = old('amenities', $room->amenities ?? []);
                    @endphp
                    @foreach($amenitiesList as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="amenities[]" id="amenity_{{ $key }}" value="{{ $key }}"
                                {{ in_array($key, $selectedAmenities) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <label for="amenity_{{ $key }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Image actuelle -->
            @if($room->image)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image actuelle</label>
                <img src="{{ asset('storage/' . $room->image) }}" alt="Chambre {{ $room->room_number }}" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
            </div>
            @endif

            <!-- Nouvelle image -->
            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $room->image ? 'Changer l\'image' : 'Image de la chambre' }}
                </label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('image')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Mettre à jour
            </button>
            <a href="{{ route('dashboard.rooms.show', $room) }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
