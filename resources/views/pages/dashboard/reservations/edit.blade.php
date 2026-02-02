@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-brand-500">Réservations</a>
        <span>/</span>
        <a href="{{ route('dashboard.reservations.show', $reservation) }}" class="hover:text-brand-500">{{ $reservation->reservation_number }}</a>
        <span>/</span>
        <span>Modifier</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Modifier réservation {{ $reservation->reservation_number }}</h1>
</div>

@if(session('error'))
    <div class="mb-6 rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
        {{ session('error') }}
    </div>
@endif

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.reservations.update', $reservation) }}" method="POST" x-data="{
        checkIn: '{{ old('check_in', $reservation->check_in->format('Y-m-d')) }}',
        checkOut: '{{ old('check_out', $reservation->check_out->format('Y-m-d')) }}',
        roomId: '{{ old('room_id', $reservation->room_id) }}',
        pricePerNight: {{ $reservation->room->price_per_night }},
        calculateTotal() {
            if (this.checkIn && this.checkOut) {
                const checkInDate = new Date(this.checkIn);
                const checkOutDate = new Date(this.checkOut);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
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
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                return nights > 0 ? nights : 0;
            }
            return 0;
        }
    }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client -->
            <div class="md:col-span-2">
                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Client <span class="text-error-500">*</span>
                </label>
                <select name="user_id" id="user_id" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @foreach($guests as $guest)
                        <option value="{{ $guest->id }}" {{ old('user_id', $reservation->user_id) == $guest->id ? 'selected' : '' }}>
                            {{ $guest->name }} ({{ $guest->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dates -->
            <div>
                <label for="check_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date de check-in <span class="text-error-500">*</span>
                </label>
                <input type="date" name="check_in" id="check_in" x-model="checkIn" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                @error('check_in')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="check_out" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date de check-out <span class="text-error-500">*</span>
                </label>
                <input type="date" name="check_out" id="check_out" x-model="checkOut" required
                    :min="checkIn"
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
                    @change="pricePerNight = parseFloat($event.target.selectedOptions[0].dataset.price)"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" data-price="{{ $room->price_per_night }}" {{ old('room_id', $reservation->room_id) == $room->id ? 'selected' : '' }}>
                            {{ $room->room_number }} - {{ $room->type_name }} ({{ number_format($room->price_per_night, 0, ',', ' ') }} FCFA/nuit)
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
                <input type="number" name="guests_count" id="guests_count" value="{{ old('guests_count', $reservation->guests_count) }}" required min="1" max="10"
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
                    <option value="pending" {{ old('status', $reservation->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ old('status', $reservation->status) === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="checked_in" {{ old('status', $reservation->status) === 'checked_in' ? 'selected' : '' }}>Check-in</option>
                    <option value="checked_out" {{ old('status', $reservation->status) === 'checked_out' ? 'selected' : '' }}>Check-out</option>
                    <option value="cancelled" {{ old('status', $reservation->status) === 'cancelled' ? 'selected' : '' }}>Annulée</option>
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
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('special_requests', $reservation->special_requests) }}</textarea>
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
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $reservation->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Mettre à jour
            </button>
            <a href="{{ route('dashboard.reservations.show', $reservation) }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
