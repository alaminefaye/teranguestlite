@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-brand-500">Réservations</a>
        <span>/</span>
        <span>Créer une réservation</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer une nouvelle réservation</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.reservations.store') }}" method="POST" x-data="{
        checkIn: '{{ e(old('check_in', $defaultCheckIn ?? date('Y-m-d\TH:i'))) }}',
        checkOut: '{{ e(old('check_out', $defaultCheckOut ?? date('Y-m-d\TH:i', strtotime('+1 day')))) }}',
        roomId: '{{ e(old('room_id')) }}',
        pricePerNight: 0,
        calculateTotal() {
            if (this.checkIn && this.checkOut && this.roomId) {
                const checkInDate = new Date(this.checkIn);
                const checkOutDate = new Date(this.checkOut);
                const nights = Math.max(1, Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
                if (nights > 0) {
                    return (this.pricePerNight * nights).toLocaleString('fr-FR') + ' FCFA';
                }
            }
            return '0 FCFA';
        },
        getNights() {
            if (this.checkIn && this.checkOut) {
                const checkInDate = new Date(this.checkIn);
                const checkOutDate = new Date(this.checkOut);
                const nights = Math.max(1, Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
                return nights > 0 ? nights : 1;
            }
            return 1;
        }
    }">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client (invité) : recherche par nom, code ou téléphone ; 5 derniers inscrits par défaut -->
            <script>
            window.guestSelectReservation = function(scriptId) {
                var el = document.getElementById(scriptId);
                var init = el ? JSON.parse(el.textContent) : {};
                var initialList = Array.isArray(init.guestList) ? init.guestList : [];
                return {
                    guestOpen: false,
                    guestSearch: '',
                    guestSelectedId: init.guestSelectedId || '',
                    guestSelectedLabel: init.guestSelectedLabel || '',
                    guestList: initialList,
                    searchUrl: init.searchUrl || '',
                    searchTimeout: null,
                    fetchGuests: function() {
                        var q = this.guestSearch.trim();
                        var url = this.searchUrl + (q ? '?q=' + encodeURIComponent(q) : '');
                        var self = this;
                        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { self.guestList = data.guests || []; })
                            .catch(function() { self.guestList = []; });
                    },
                    onSearchInput: function() {
                        clearTimeout(this.searchTimeout);
                        var self = this;
                        this.searchTimeout = setTimeout(function() { self.fetchGuests(); }, 200);
                    },
                    selectGuest: function(guest) {
                        this.guestSelectedId = guest.id;
                        this.guestSelectedLabel = guest.label;
                        this.guestOpen = false;
                        this.guestSearch = '';
                        if (this.$refs.guestHidden) this.$refs.guestHidden.value = guest.id;
                    },
                    openDropdown: function() {
                        this.guestOpen = true;
                        if (!this.guestSearch) this.guestList = initialList;
                        var self = this;
                        this.$nextTick(function() { if (self.$refs.guestSearchInput) self.$refs.guestSearchInput.focus(); });
                    }
                };
            };
            </script>
            <script type="application/json" id="guest-select-data-create">@json($guestSelectInit ?? ['guestList' => [], 'guestSelectedId' => '', 'guestSelectedLabel' => '', 'searchUrl' => route('dashboard.guests.search')])</script>
            <div class="md:col-span-2" x-data="guestSelectReservation('guest-select-data-create')" @click.outside="guestOpen = false">
                <label for="guest_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Client (invité) <span class="text-error-500">*</span>
                </label>
                <input type="hidden" name="guest_id" id="guest_id" x-ref="guestHidden" :value="guestSelectedId" required>
                <div class="relative">
                    <button type="button" @click="openDropdown()"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-left text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500 flex items-center justify-between">
                        <span x-text="guestSelectedLabel || 'Sélectionner un client'" :class="!guestSelectedLabel && 'text-gray-400 dark:text-gray-500'"></span>
                        <svg class="w-5 h-5 text-gray-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="guestOpen" x-transition
                        class="absolute z-20 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-72 overflow-hidden">
                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                            <input type="text" x-ref="guestSearchInput" x-model="guestSearch" @input="onSearchInput()"
                                placeholder="Rechercher par nom, code ou téléphone..."
                                class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-white/90 placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div class="overflow-y-auto max-h-56">
                            <template x-for="g in guestList" :key="g.id">
                                <button type="button" @click="selectGuest(g)"
                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-800 dark:text-white/90 hover:bg-brand-50 dark:hover:bg-brand-500/20 flex items-center gap-2"
                                    :class="guestSelectedId == g.id && 'bg-brand-50 dark:bg-brand-500/20'">
                                    <span x-text="g.label" class="truncate"></span>
                                    <span x-show="guestSelectedId == g.id" class="text-brand-500">✓</span>
                                </button>
                            </template>
                            <p x-show="guestList.length === 0 && !guestSearch" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Aucun client récent. Utilisez la recherche.</p>
                            <p x-show="guestList.length === 0 && guestSearch" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Aucun résultat.</p>
                        </div>
                    </div>
                </div>
                @error('guest_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date et heure check-in / check-out (pour validation du code tablette) -->
            <div>
                <label for="check_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Check-in (date et heure) <span class="text-error-500">*</span>
                </label>
                <input type="datetime-local" name="check_in" id="check_in" x-model="checkIn" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('check_in')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="check_out" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Check-out (date et heure) <span class="text-error-500">*</span>
                </label>
                <input type="datetime-local" name="check_out" id="check_out" x-model="checkOut" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('check_out')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Chambre -->
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Chambre <span class="text-error-500">*</span>
                </label>
                <select name="room_id" id="room_id" x-model="roomId" required
                    @change="pricePerNight = $event.target.selectedOptions[0].dataset.price"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner une chambre</option>
                    @foreach($roomsForSelect ?? [] as $room)
                        <option value="{{ $room['id'] }}" data-price="{{ $room['price_per_night'] }}" {{ old('room_id') == $room['id'] ? 'selected' : '' }}>
                            {{ $room['room_number'] }} - {{ $room['type_name'] }} ({{ number_format($room['price_per_night'], 0, ',', ' ') }} FCFA/nuit)
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nombre de guests -->
            <div>
                <label for="guests_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre de personnes <span class="text-error-500">*</span>
                </label>
                <input type="number" name="guests_count" id="guests_count" value="{{ old('guests_count', 1) }}" required min="1" max="10"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('guests_count')
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
                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ old('status', 'confirmed') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prix total (calculé) -->
            <div class="md:col-span-2 p-4 bg-brand-50 dark:bg-brand-500/10 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nombre de nuits</p>
                        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="getNights()"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Prix total estimé</p>
                        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400" x-text="calculateTotal()"></p>
                    </div>
                </div>
            </div>

            <!-- Demandes spéciales -->
            <div class="md:col-span-2">
                <label for="special_requests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Demandes spéciales
                </label>
                <textarea name="special_requests" id="special_requests" rows="3"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('special_requests') }}</textarea>
                @error('special_requests')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes internes -->
            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Notes internes (visibles uniquement par le staff)
                </label>
                <textarea name="notes" id="notes" rows="2"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Créer la réservation
            </button>
            <a href="{{ route('dashboard.reservations.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>

@endsection
