@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('rentals.index') }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Nouvelle Location</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créez une nouvelle demande de location</p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('rentals.store') }}" method="POST" id="rentalForm">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Véhicule <span class="text-error-500">*</span>
                    </label>
                    <select name="vehicle_id" id="vehicle_id" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Sélectionner un véhicule</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" 
                                    data-price="{{ $vehicle->price_per_day > 0 ? $vehicle->price_per_day : 10000 }}"
                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->name }} ({{ $vehicle->plate_number }}) - {{ $vehicle->type }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Date de début <span class="text-error-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" 
                           min="{{ date('Y-m-d') }}" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('start_date')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Date de fin <span class="text-error-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('end_date')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Notes (optionnel)
                    </label>
                    <textarea name="notes" rows="3" 
                              placeholder="Informations supplémentaires sur la location..."
                              class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Estimation du prix -->
            <div id="priceEstimate" class="mt-6 hidden rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-brand-700 dark:text-brand-400">Estimation du prix</p>
                        <p class="mt-1 text-xs text-brand-600 dark:text-brand-300">Prix calculé automatiquement</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-brand-600 dark:text-brand-400" id="estimatedPrice">0 FCFA</p>
                        <p class="text-xs text-brand-500 dark:text-brand-300" id="daysCount">0 jour(s)</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('rentals.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Créer la demande de location
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const priceEstimate = document.getElementById('priceEstimate');
        const estimatedPrice = document.getElementById('estimatedPrice');
        const daysCount = document.getElementById('daysCount');
        
        function getDailyRate(vehicleId) {
            const vehicleSelect = document.getElementById('vehicle_id');
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            
            if (selectedOption.dataset.price) {
                return parseFloat(selectedOption.dataset.price);
            }
            
            return 10000; // Fallback default
        }

        function calculatePrice() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end > start) {
                    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    const dailyRate = getDailyRate();
                    const total = days * dailyRate;
                    
                    daysCount.textContent = days + ' jour(s)';
                    estimatedPrice.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
                    priceEstimate.classList.remove('hidden');
                } else {
                    priceEstimate.classList.add('hidden');
                }
            } else {
                priceEstimate.classList.add('hidden');
            }
        }

        document.getElementById('vehicle_id').addEventListener('change', calculatePrice);

        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && new Date(endDateInput.value) <= new Date(this.value)) {
                endDateInput.value = '';
            }
            endDateInput.min = this.value;
            calculatePrice();
        });

        endDateInput.addEventListener('change', calculatePrice);
    });
</script>
@endpush
@endsection
