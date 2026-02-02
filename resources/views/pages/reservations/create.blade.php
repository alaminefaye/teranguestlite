@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('reservations.index') }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Nouvelle Réservation</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créez une nouvelle réservation</p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('reservations.store') }}" method="POST" id="booking-form">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Horaire <span class="text-error-500">*</span>
                    </label>
                    
                    <!-- Filtre par station de départ -->
                    <div class="mb-2">
                        <select id="filter_departure_station"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Toutes les stations de départ</option>
                            @foreach($departureStations as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Champ de recherche -->
                    <div class="relative mb-2">
                        <input type="text" 
                               id="schedule_search" 
                               placeholder="Rechercher par date, station, heure..."
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <svg class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <!-- Select amélioré -->
                    <div class="relative">
                        <select name="schedule_id" id="schedule_id" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Sélectionner un horaire</option>
                            @php
                                // Grouper les horaires par date
                                $schedulesByDate = $schedules->groupBy(function($schedule) {
                                    return $schedule->schedule_date->format('Y-m-d');
                                });
                            @endphp
                            @foreach($schedulesByDate as $date => $daySchedules)
                                <optgroup label="{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}">
                                    @foreach($daySchedules as $schedule)
                                        <option value="{{ $schedule->id }}" 
                                                data-price="{{ $schedule->route->price }}"
                                                data-seats="{{ $schedule->available_seats }}"
                                                data-date="{{ $schedule->schedule_date->format('d/m/Y') }}"
                                                data-time="{{ $schedule->departure_time }}"
                                                data-departure="{{ $schedule->route->departureStation->name ?? 'N/A' }}"
                                                data-departure-id="{{ $schedule->route->departureStation->id ?? '' }}"
                                                data-arrival="{{ $schedule->route->arrivalStation->name ?? 'N/A' }}"
                                                data-search-text="{{ strtolower($schedule->schedule_date->format('d/m/Y') . ' ' . $schedule->departure_time . ' ' . ($schedule->route->departureStation->name ?? '') . ' ' . ($schedule->route->arrivalStation->name ?? '')) }}"
                                                {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                            {{ $schedule->departure_time }} | 
                                            {{ $schedule->route->departureStation->name ?? 'N/A' }} → {{ $schedule->route->arrivalStation->name ?? 'N/A' }} | 
                                            {{ number_format($schedule->route->price, 0, ',', ' ') }} FCFA | 
                                            {{ $schedule->available_seats }} places
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Informations de l'horaire sélectionné -->
                    <div id="schedule-info" class="mt-2 hidden rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                        <div class="space-y-1 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Date:</span>
                                <span class="font-medium text-gray-900 dark:text-white" id="schedule-date">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Heure:</span>
                                <span class="font-medium text-gray-900 dark:text-white" id="schedule-time">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Trajet:</span>
                                <span class="font-medium text-gray-900 dark:text-white" id="schedule-route">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Prix:</span>
                                <span class="font-semibold text-brand-600 dark:text-brand-400" id="schedule-price">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Places disponibles:</span>
                                <span class="font-medium text-gray-900 dark:text-white" id="schedule-seats">-</span>
                            </div>
                        </div>
                    </div>
                    
                    @error('schedule_id')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Numéro de téléphone du client <span class="text-error-500">*</span>
                    </label>
                    <input type="tel" 
                           name="client_phone" 
                           id="client_phone" 
                           value="{{ old('client_phone') }}"
                           placeholder="Ex: +221771234567"
                           required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <div id="client-search-status" class="mt-1 text-xs"></div>
                    <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}">
                    @error('user_id')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                    @error('client_phone')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Champ nom du client (affiché seulement si le client n'existe pas) -->
                <div id="client-name-container" class="hidden">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nom du client <span class="text-error-500">*</span>
                    </label>
                    <input type="text" 
                           name="client_name" 
                           id="client_name" 
                           value="{{ old('client_name') }}"
                           placeholder="Nom complet du client"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('client_name')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Sélection visuelle des sièges -->
            <div id="seat-selection-container" class="mt-6 hidden">
                <label class="mb-4 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Sélectionnez un siège <span class="text-error-500">*</span>
                </label>
                
                <!-- Légende -->
                <div class="mb-4 flex flex-wrap items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded border-2 border-gray-300" style="background-color: #57ac45;"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Disponible</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded border-2 border-gray-300" style="background-color: #ef4444;"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Occupé</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded border-2" style="background-color: #0d3650; border-color: #0d3650;"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sélectionné</span>
                    </div>
                </div>

                <!-- Plan du bus -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-800">
                    <div id="bus-layout" class="mx-auto flex flex-col gap-0.5" style="max-width: 500px;">
                        <!-- Zone du conducteur (en haut) -->
                        <div class="mb-2 flex items-center justify-center">
                            <div class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 px-4 py-2 shadow-sm">
                                <!-- Icône de volant -->
                                <svg class="h-8 w-8 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Cercle extérieur du volant -->
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" fill="none"/>
                                    <!-- Cercle intérieur du volant -->
                                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <!-- Rayons du volant -->
                                    <path d="M12 3 L12 7 M12 17 L12 21 M3 12 L7 12 M17 12 L21 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <!-- Poignées du volant -->
                                    <circle cx="12" cy="3" r="1.5" fill="currentColor"/>
                                    <circle cx="12" cy="21" r="1.5" fill="currentColor"/>
                                    <circle cx="3" cy="12" r="1.5" fill="currentColor"/>
                                    <circle cx="21" cy="12" r="1.5" fill="currentColor"/>
                                </svg>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Conducteur</span>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Chauffeur</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sièges du bus -->
                        <div id="seats-grid" class="space-y-0.5">
                            <!-- Les sièges seront générés dynamiquement par JavaScript -->
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="seat_numbers" id="seat_numbers" value="{{ old('seat_numbers') }}" required>
                <div id="selected-seats-info" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <span id="selected-seats-count">0</span> siège(s) sélectionné(s) (maximum 5)
                </div>
                @error('seat_numbers')
                    <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                @enderror
                @error('seat_number')
                    <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('reservations.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Créer la réservation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleSelect = document.getElementById('schedule_id');
    const scheduleSearch = document.getElementById('schedule_search');
    const filterDepartureStation = document.getElementById('filter_departure_station');
    const scheduleInfo = document.getElementById('schedule-info');
    const seatSelectionContainer = document.getElementById('seat-selection-container');
    const seatsGrid = document.getElementById('seats-grid');
    const seatNumbersInput = document.getElementById('seat_numbers');
    const selectedSeatsCount = document.getElementById('selected-seats-count');
    const clientPhoneInput = document.getElementById('client_phone');
    
    // Vérifier que les éléments existent
    if (!seatNumbersInput || !selectedSeatsCount) {
        console.error('Éléments de sélection de sièges non trouvés');
    }
    const clientNameContainer = document.getElementById('client-name-container');
    const clientNameInput = document.getElementById('client_name');
    const userIdInput = document.getElementById('user_id');
    const clientSearchStatus = document.getElementById('client-search-status');
    let selectedSeats = new Map(); // Map<seatNumber, seatButton>
    const MAX_SELECTED_SEATS = 5;
    let refreshInterval = null;
    let currentScheduleId = null;
    let clientSearchTimeout = null;

    // Fonction pour filtrer les horaires
    function filterSchedules() {
        const searchTerm = scheduleSearch.value.toLowerCase().trim();
        const selectedStationId = filterDepartureStation.value;
        const options = scheduleSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                return; // Garder l'option par défaut visible
            }
            
            const searchText = option.getAttribute('data-search-text') || '';
            const departureStationId = option.getAttribute('data-departure-id') || '';
            const optgroup = option.closest('optgroup');
            
            // Vérifier le filtre par station
            let matchesStation = true;
            if (selectedStationId && departureStationId !== selectedStationId) {
                matchesStation = false;
            }
            
            // Vérifier la recherche textuelle
            let matchesSearch = true;
            if (searchTerm && !searchText.includes(searchTerm)) {
                matchesSearch = false;
            }
            
            // Afficher ou masquer selon les filtres
            if (matchesStation && matchesSearch) {
                option.style.display = '';
                if (optgroup) {
                    optgroup.style.display = '';
                }
            } else {
                option.style.display = 'none';
                // Masquer l'optgroup si toutes ses options sont masquées
                if (optgroup) {
                    const visibleOptions = Array.from(optgroup.querySelectorAll('option')).filter(opt => opt.style.display !== 'none');
                    if (visibleOptions.length === 0) {
                        optgroup.style.display = 'none';
                    }
                }
            }
        });
    }

    // Filtre par station de départ
    filterDepartureStation.addEventListener('change', function() {
        filterSchedules();
    });

    // Recherche dans la liste des horaires
    scheduleSearch.addEventListener('input', function() {
        filterSchedules();
    });

    // Afficher les informations de l'horaire sélectionné
    scheduleSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const date = selectedOption.getAttribute('data-date') || '-';
            const time = selectedOption.getAttribute('data-time') || '-';
            const departure = selectedOption.getAttribute('data-departure') || '-';
            const arrival = selectedOption.getAttribute('data-arrival') || '-';
            const price = selectedOption.getAttribute('data-price') || '0';
            const seats = selectedOption.getAttribute('data-seats') || '0';
            
            document.getElementById('schedule-date').textContent = date;
            document.getElementById('schedule-time').textContent = time;
            document.getElementById('schedule-route').textContent = departure + ' → ' + arrival;
            document.getElementById('schedule-price').textContent = parseInt(price).toLocaleString('fr-FR') + ' FCFA';
            document.getElementById('schedule-seats').textContent = seats + ' place' + (seats > 1 ? 's' : '');
            
            scheduleInfo.classList.remove('hidden');
        } else {
            scheduleInfo.classList.add('hidden');
        }
        
        // Déclencher l'événement de changement d'horaire pour charger les sièges
        const scheduleId = this.value;
        
        // Arrêter le rafraîchissement automatique si on change d'horaire
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
        
        if (!scheduleId) {
            seatSelectionContainer.classList.add('hidden');
            seatNumbersInput.value = '';
            selectedSeats.clear();
            updateSelectedSeatsCount();
            currentScheduleId = null;
            return;
        }

        currentScheduleId = scheduleId;
        // Charger les sièges disponibles
        loadSeats(scheduleId);
        
        // Rafraîchir automatiquement les sièges toutes les 5 secondes pour éviter les conflits
        refreshInterval = setInterval(() => {
            if (currentScheduleId && selectedSeats.size === 0) {
                loadSeats(currentScheduleId);
            }
        }, 5000);
    });
    
    // Initialiser l'affichage si un horaire est déjà sélectionné
    if (scheduleSelect.value) {
        scheduleSelect.dispatchEvent(new Event('change'));
    }

    // Recherche automatique du client par téléphone
    clientPhoneInput.addEventListener('input', function() {
        const phone = this.value.trim();
        
        // Réinitialiser
        userIdInput.value = '';
        clientNameContainer.classList.add('hidden');
        clientNameInput.removeAttribute('required');
        clientSearchStatus.textContent = '';
        
        if (phone.length < 8) {
            return;
        }
        
        // Debounce pour éviter trop de requêtes
        if (clientSearchTimeout) {
            clearTimeout(clientSearchTimeout);
        }
        
        clientSearchTimeout = setTimeout(() => {
            searchClientByPhone(phone);
        }, 500);
    });

    async function searchClientByPhone(phone) {
        clientSearchStatus.innerHTML = '<span class="text-gray-500">Recherche en cours...</span>';
        
        try {
            const response = await fetch(`/api/clients/search?phone=${encodeURIComponent(phone)}`);
            const data = await response.json();
            
            if (data.success && data.client) {
                // Client trouvé
                userIdInput.value = data.client.id;
                clientNameContainer.classList.add('hidden');
                clientNameInput.removeAttribute('required');
                clientSearchStatus.innerHTML = `<span class="text-success-600">✓ Client trouvé: ${data.client.name}</span>`;
            } else {
                // Client non trouvé - afficher le champ nom
                userIdInput.value = '';
                clientNameContainer.classList.remove('hidden');
                clientNameInput.setAttribute('required', 'required');
                clientSearchStatus.innerHTML = '<span class="text-warning-600">⚠ Nouveau client - Veuillez saisir le nom</span>';
            }
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
            clientSearchStatus.innerHTML = '<span class="text-error-500">Erreur lors de la recherche</span>';
        }
    }


    function loadSeats(scheduleId) {
        fetch(`/schedules/${scheduleId}/seats`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSeats(data.data.seats);
                    seatSelectionContainer.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des sièges:', error);
            });
    }

    function updateSelectedSeatsCount() {
        if (!selectedSeatsCount || !seatNumbersInput) {
            console.warn('Éléments de sélection non disponibles');
            return;
        }
        
        const count = selectedSeats.size;
        selectedSeatsCount.textContent = count;
        
        // Mettre à jour le champ hidden avec les numéros de sièges sélectionnés
        const seatNumbers = Array.from(selectedSeats.keys()).sort((a, b) => parseInt(a) - parseInt(b)).join(',');
        seatNumbersInput.value = seatNumbers;
        
        // Afficher un message d'avertissement si le maximum est atteint
        if (count >= MAX_SELECTED_SEATS) {
            selectedSeatsCount.parentElement.classList.remove('text-gray-600', 'dark:text-gray-400');
            selectedSeatsCount.parentElement.classList.add('text-warning-600', 'font-semibold');
        } else {
            selectedSeatsCount.parentElement.classList.remove('text-warning-600', 'font-semibold');
            selectedSeatsCount.parentElement.classList.add('text-gray-600', 'dark:text-gray-400');
        }
    }
    
    // Initialiser le compteur après le chargement
    setTimeout(() => {
        updateSelectedSeatsCount();
    }, 100);

    function renderSeats(seats) {
        // Sauvegarder les sièges sélectionnés avant de re-rendre
        const previouslySelectedSeatNumbers = Array.from(selectedSeats.keys());
        
        seatsGrid.innerHTML = '';
        
        // Ne pas réinitialiser si des sièges étaient sélectionnés
        if (previouslySelectedSeatNumbers.length === 0) {
            seatNumbersInput.value = '';
        }

        // Organiser les sièges en rangées (2 sièges de chaque côté du couloir)
        const seatsPerSide = 2;
        const totalRows = Math.ceil(seats.length / (seatsPerSide * 2));
        
        let seatIndex = 0;

        for (let row = 0; row < totalRows; row++) {
            const rowContainer = document.createElement('div');
            rowContainer.className = 'flex items-center justify-center gap-0.5 bg-white dark:bg-gray-900 p-1 rounded';
            
            // Sièges de gauche (2 sièges)
            const leftSeats = document.createElement('div');
            leftSeats.className = 'flex gap-1';
            for (let i = 0; i < seatsPerSide && seatIndex < seats.length; i++) {
                const seat = seats[seatIndex];
                const seatElement = createSeatElement(seat, previouslySelectedSeatNumbers);
                leftSeats.appendChild(seatElement);
                seatIndex++;
            }
            rowContainer.appendChild(leftSeats);
            
            // Couloir (blanc) - largeur fixe réduite
            const aisle = document.createElement('div');
            aisle.className = 'h-14 w-4 bg-white dark:bg-gray-800 border-l border-r border-dashed border-gray-300 dark:border-gray-600';
            rowContainer.appendChild(aisle);
            
            // Sièges de droite (2 sièges)
            const rightSeats = document.createElement('div');
            rightSeats.className = 'flex gap-1';
            for (let i = 0; i < seatsPerSide && seatIndex < seats.length; i++) {
                const seat = seats[seatIndex];
                const seatElement = createSeatElement(seat, previouslySelectedSeatNumbers);
                rightSeats.appendChild(seatElement);
                seatIndex++;
            }
            rowContainer.appendChild(rightSeats);
            
            seatsGrid.appendChild(rowContainer);
        }
    }

    function createSeatElement(seat, previouslySelectedSeatNumbers = []) {
        const seatDiv = document.createElement('div');
        seatDiv.className = 'relative';
        
        const seatButton = document.createElement('button');
        seatButton.type = 'button';
        
        // Couleurs personnalisées
        const availableColor = '#57ac45'; // Vert pour disponible
        const occupiedColor = '#ef4444'; // Rouge pour occupé
        const selectedColor = '#0d3650'; // Bleu foncé pour sélectionné
        
        // Vérifier si ce siège était précédemment sélectionné
        const isPreviouslySelected = previouslySelectedSeatNumbers.includes(seat.number.toString()) && seat.available;
        
        // Créer l'icône SVG de siège
        const seatIcon = `
            <svg width="44" height="44" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Dossier du siège (assise) -->
                <path d="M10 22C10 19.7909 11.7909 18 14 18H34C36.2091 18 38 19.7909 38 22V30C38 32.2091 36.2091 34 34 34H14C11.7909 34 10 32.2091 10 30V22Z" fill="currentColor"/>
                <!-- Dossier (backrest) -->
                <path d="M12 16C12 14.8954 12.8954 14 14 14H34C35.1046 14 36 14.8954 36 16V22H12V16Z" fill="currentColor" opacity="0.9"/>
                <!-- Accoudoir gauche -->
                <path d="M8 20V30C8 32.2091 9.79086 34 12 34H10C7.79086 34 6 32.2091 6 30V20C6 17.7909 7.79086 16 10 16H12C9.79086 16 8 17.7909 8 20Z" fill="currentColor" opacity="0.6"/>
                <!-- Accoudoir droit -->
                <path d="M40 20V30C40 32.2091 38.2091 34 36 34H38C40.2091 34 42 32.2091 42 30V20C42 17.7909 40.2091 16 38 16H36C38.2091 16 40 17.7909 40 20Z" fill="currentColor" opacity="0.6"/>
            </svg>
        `;
        
        // Créer un conteneur pour l'icône et le numéro
        const seatContent = document.createElement('div');
        seatContent.className = 'relative flex flex-col items-center justify-center w-full h-full pointer-events-none';
        seatContent.innerHTML = seatIcon;
        
        // Ajouter le numéro du siège au centre
        const seatNumber = document.createElement('span');
        seatNumber.className = 'absolute text-sm font-bold text-white pointer-events-none';
        seatNumber.style.textShadow = '0 1px 2px rgba(0, 0, 0, 0.5)';
        seatNumber.textContent = seat.number;
        seatContent.appendChild(seatNumber);
        
        if (seat.available) {
            if (isPreviouslySelected) {
                // Siège disponible mais précédemment sélectionné - le garder sélectionné
                seatButton.style.color = selectedColor;
                seatButton.className = 'h-12 w-12 transition-all duration-200 flex items-center justify-center scale-110 hover:scale-105 cursor-pointer active:scale-95';
                seatButton.style.filter = 'drop-shadow(0 4px 6px rgba(13, 54, 80, 0.3))';
                selectedSeats.set(seat.number.toString(), seatButton);
            } else {
                // Siège disponible - vert
                seatButton.style.color = availableColor;
                seatButton.className = 'h-12 w-12 transition-all duration-200 flex items-center justify-center hover:scale-105 cursor-pointer active:scale-95';
            }
        } else {
            // Siège occupé - rouge
            seatButton.style.color = occupiedColor;
            seatButton.style.opacity = '0.7';
            seatButton.className = 'h-12 w-12 transition-all duration-200 flex items-center justify-center cursor-not-allowed';
        }
        
        // Stocker le numéro du siège dans le bouton pour faciliter l'accès
        seatButton.dataset.seatNumber = seat.number.toString();
        
        seatButton.appendChild(seatContent);
        seatButton.disabled = !seat.available;
        
        if (seat.available) {
            seatButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const seatNum = this.dataset.seatNumber;
                if (!seatNum) {
                    console.error('Numéro de siège non trouvé');
                    return;
                }
                
                const isCurrentlySelected = selectedSeats.has(seatNum);
                
                // Arrêter le rafraîchissement automatique quand un siège est sélectionné
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                }
                
                if (isCurrentlySelected) {
                    // Désélectionner le siège
                    this.style.color = availableColor;
                    this.classList.remove('scale-110');
                    this.style.filter = '';
                    selectedSeats.delete(seatNum);
                } else {
                    // Vérifier si on peut encore sélectionner (max 5)
                    if (selectedSeats.size >= MAX_SELECTED_SEATS) {
                        alert(`Vous ne pouvez sélectionner que ${MAX_SELECTED_SEATS} sièges maximum.`);
                        return;
                    }
                    
                    // Sélectionner le nouveau siège - bleu foncé
                    this.style.color = selectedColor;
                    this.style.filter = 'drop-shadow(0 4px 6px rgba(13, 54, 80, 0.3))';
                    this.classList.add('scale-110');
                    selectedSeats.set(seatNum, this);
                }
                
                updateSelectedSeatsCount();
            });
        }
        
        seatDiv.appendChild(seatButton);
        return seatDiv;
    }

    // Vérification en temps réel avant soumission du formulaire
    const bookingForm = document.getElementById('booking-form');
    bookingForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const scheduleId = scheduleSelect.value;
        const seatNumbers = seatNumbersInput.value;
        const phone = clientPhoneInput.value.trim();
        const clientName = clientNameInput.value.trim();
        const userId = userIdInput.value;
        
        // Validation
        if (!scheduleId || !seatNumbers) {
            alert('Veuillez sélectionner un horaire et au moins un siège.');
            return;
        }
        
        if (selectedSeats.size === 0) {
            alert('Veuillez sélectionner au moins un siège.');
            return;
        }
        
        if (selectedSeats.size > MAX_SELECTED_SEATS) {
            alert(`Vous ne pouvez sélectionner que ${MAX_SELECTED_SEATS} sièges maximum.`);
            return;
        }
        
        if (!phone) {
            alert('Veuillez saisir le numéro de téléphone du client.');
            return;
        }
        
        // Si le client n'existe pas, le nom est requis
        if (!userId && !clientName) {
            alert('Veuillez saisir le nom du client.');
            clientNameContainer.classList.remove('hidden');
            clientNameInput.focus();
            return;
        }

        // Désactiver le bouton de soumission
        const submitButton = bookingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Vérification en cours...';

        try {
            // Vérifier la disponibilité du siège en temps réel
            const response = await fetch(`/schedules/${scheduleId}/seats`);
            const data = await response.json();
            
            if (data.success) {
                const selectedSeatNumbers = seatNumbers.split(',').map(n => parseInt(n.trim()));
                const unavailableSeats = [];
                
                for (const seatNum of selectedSeatNumbers) {
                    const seat = data.data.seats.find(s => s.number == seatNum);
                    
                    if (!seat) {
                        unavailableSeats.push(seatNum);
                    } else if (!seat.available) {
                        unavailableSeats.push(seatNum);
                    }
                }
                
                if (unavailableSeats.length > 0) {
                    alert(`Les sièges suivants ne sont plus disponibles: ${unavailableSeats.join(', ')}. Veuillez en sélectionner d'autres.`);
                    // Recharger les sièges pour mettre à jour l'affichage
                    loadSeats(scheduleId);
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                    return;
                }
            }
            
            // Si tout est OK, soumettre le formulaire
            bookingForm.submit();
            
        } catch (error) {
            console.error('Erreur lors de la vérification:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
});
</script>
@endpush
@endsection
