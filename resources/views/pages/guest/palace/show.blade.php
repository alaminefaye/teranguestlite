@extends('layouts.guest')

@php
    $nameLower = strtolower($service->name ?? '');
    $isVehicleService = str_contains($nameLower, 'voiture') || str_contains($nameLower, 'chauffeur') || str_contains($nameLower, 'location');
@endphp

@section('content')
<div class="mb-6">
    <a href="{{ route('guest.palace.index') }}" class="text-brand-600 text-sm mb-2 inline-block">← Retour</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $service->name }}</h1>
</div>

<!-- Info Service -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $service->description }}</p>
    <p class="text-sm text-brand-600 dark:text-brand-400 font-semibold">{{ $service->formatted_price }}</p>
</div>

<!-- Formulaire Demande -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Faire une demande</h2>
    <form action="{{ route('guest.palace.request', $service) }}" method="POST" id="palace-request-form">
        @csrf

        @if($isVehicleService)
        <!-- Type : Taxi ou Location -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type de demande</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="metadata[vehicle_request_type]" value="taxi" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="ml-2">Taxi</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="metadata[vehicle_request_type]" value="rental" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="ml-2">Location</span>
                </label>
            </div>
        </div>

        <!-- Bloc Taxi (affiché si Taxi coché) -->
        <div id="taxi-fields" class="space-y-4 mb-4 hidden">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prise en charge</label>
                <div class="flex gap-2">
                    <input type="text" name="metadata[pickup_address]" id="pickup_address" placeholder="Adresse ou utilisez « Ma position »" class="flex-1 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                    <button type="button" id="btn-my-location" class="px-4 py-3 rounded-md border border-brand-500 text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 text-sm font-medium">Ma position</button>
                </div>
                <input type="hidden" name="metadata[pickup_lat]" id="pickup_lat">
                <input type="hidden" name="metadata[pickup_lng]" id="pickup_lng">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Destination *</label>
                <input type="text" name="metadata[destination_address]" id="destination_address" placeholder="Adresse de destination" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distance (km, optionnel)</label>
                <input type="number" name="metadata[distance_km]" id="distance_km" step="0.1" min="0" placeholder="Ex: 5.2" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
        </div>

        <!-- Bloc Location (affiché si Location cochée) -->
        <div id="rental-fields" class="space-y-4 mb-4 hidden">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de places</label>
                <input type="number" name="metadata[number_of_seats]" id="number_of_seats" min="1" max="20" placeholder="Ex: 4" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type de véhicule</label>
                <input type="text" name="metadata[vehicle_type]" id="vehicle_type" placeholder="Ex: Berline, SUV, Minibus" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de jours</label>
                <input type="number" name="metadata[rental_days]" id="rental_days" min="1" max="90" placeholder="Ex: 2" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durée (heures)</label>
                <input type="number" name="metadata[rental_duration_hours]" id="rental_duration_hours" min="1" placeholder="Ex: 8" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
        </div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (optionnel si vous avez rempli Taxi/Location)</label>
                <textarea name="description" rows="4" placeholder="Décrivez en détail votre demande..." class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pour quand ? (optionnel)</label>
                <input type="datetime-local" name="requested_for" min="{{ date('Y-m-d\TH:i') }}" value="{{ old('requested_for') }}" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium">Envoyer la demande</button>
        </div>
    </form>
</div>

@if($isVehicleService)
<script>
(function() {
    const typeTaxi = document.querySelector('input[name="metadata[vehicle_request_type]"][value="taxi"]');
    const typeRental = document.querySelector('input[name="metadata[vehicle_request_type]"][value="rental"]');
    const taxiFields = document.getElementById('taxi-fields');
    const rentalFields = document.getElementById('rental-fields');
    const btnMyLocation = document.getElementById('btn-my-location');
    const pickupAddress = document.getElementById('pickup_address');
    const pickupLat = document.getElementById('pickup_lat');
    const pickupLng = document.getElementById('pickup_lng');

    function toggleVehicleBlocks() {
        const isTaxi = typeTaxi && typeTaxi.checked;
        const isRental = typeRental && typeRental.checked;
        if (taxiFields) taxiFields.classList.toggle('hidden', !isTaxi);
        if (rentalFields) rentalFields.classList.toggle('hidden', !isRental);
    }

    if (typeTaxi) typeTaxi.addEventListener('change', toggleVehicleBlocks);
    if (typeRental) typeRental.addEventListener('change', toggleVehicleBlocks);

    if (btnMyLocation && navigator.geolocation) {
        btnMyLocation.addEventListener('click', function() {
            btnMyLocation.disabled = true;
            btnMyLocation.textContent = 'Chargement...';
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    pickupLat.value = pos.coords.latitude;
                    pickupLng.value = pos.coords.longitude;
                    pickupAddress.value = 'Position actuelle (' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5) + ')';
                    btnMyLocation.disabled = false;
                    btnMyLocation.textContent = 'Ma position';
                },
                function() {
                    pickupAddress.placeholder = 'Activer la localisation ou saisir l\'adresse';
                    btnMyLocation.disabled = false;
                    btnMyLocation.textContent = 'Ma position';
                }
            );
        });
    }
})();
</script>
@endif
@endsection
