@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('reservations.show', $booking) }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Paiement de la Réservation</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Référence: {{ $booking->booking_reference }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Méthode de Paiement</h3>
                
                <form action="{{ route('payments.process', $booking) }}" method="POST" id="paymentForm">
                    @csrf

                    <div class="space-y-4">
                        <!-- Méthodes de paiement -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Choisissez votre méthode de paiement <span class="text-error-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none payment-method-label" data-method="orange_money">
                                    <input type="radio" name="method" value="orange_money" class="peer sr-only" required>
                                    <div class="flex flex-1 items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-500/10">
                                            <span class="text-xl font-bold text-orange-600 dark:text-orange-400">O</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white/90">Orange Money</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Paiement mobile</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none payment-method-label" data-method="free_money">
                                    <input type="radio" name="method" value="free_money" class="peer sr-only" required>
                                    <div class="flex flex-1 items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-500/10">
                                            <span class="text-xl font-bold text-green-600 dark:text-green-400">F</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white/90">Free Money</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Paiement mobile</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none payment-method-label" data-method="wave">
                                    <input type="radio" name="method" value="wave" class="peer sr-only" required>
                                    <div class="flex flex-1 items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/10">
                                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">W</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white/90">Wave</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Paiement mobile</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none payment-method-label" data-method="cash">
                                    <input type="radio" name="method" value="cash" class="peer sr-only" required>
                                    <div class="flex flex-1 items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                            <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800 dark:text-white/90">Espèces</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Paiement sur place</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('method')
                                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Numéro de téléphone (pour Orange Money, Free Money, Wave) -->
                        <div id="phoneField" class="hidden">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Numéro de téléphone <span class="text-error-500">*</span>
                            </label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $booking->user->phone) }}" 
                                   placeholder="+221771234567"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('phone_number')
                                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Transaction ID (optionnel) -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                ID de transaction (optionnel)
                            </label>
                            <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" 
                                   placeholder="ID de transaction si disponible"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('transaction_id')
                                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Notes (optionnel)
                            </label>
                            <textarea name="notes" rows="3" 
                                      placeholder="Informations supplémentaires..."
                                      class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('reservations.show', $booking) }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Annuler
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            Procéder au paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Récapitulatif -->
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Récapitulatif</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Référence</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_reference }}</span>
                    </div>
                    @if($booking->schedule && $booking->schedule->route)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Trajet</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $booking->schedule->route->departureStation->name ?? 'N/A' }} → {{ $booking->schedule->route->arrivalStation->name ?? 'N/A' }}
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Siège</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->seat_number }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold text-gray-800 dark:text-white/90">Total</span>
                            <span class="text-lg font-bold text-brand-500">{{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="method"]');
        const phoneField = document.getElementById('phoneField');
        const phoneInput = phoneField.querySelector('input[name="phone_number"]');

        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                const selectedMethod = this.value;
                const requiresPhone = ['orange_money', 'free_money', 'wave'].includes(selectedMethod);
                
                if (requiresPhone) {
                    phoneField.classList.remove('hidden');
                    phoneInput.setAttribute('required', 'required');
                } else {
                    phoneField.classList.add('hidden');
                    phoneInput.removeAttribute('required');
                }
            });
        });

        // Style pour les méthodes sélectionnées
        document.querySelectorAll('.payment-method-label').forEach(label => {
            const input = label.querySelector('input[type="radio"]');
            input.addEventListener('change', function() {
                document.querySelectorAll('.payment-method-label').forEach(l => {
                    l.classList.remove('border-brand-500', 'bg-brand-50');
                    l.classList.add('border-gray-200');
                });
                if (this.checked) {
                    label.classList.add('border-brand-500', 'bg-brand-50');
                    label.classList.remove('border-gray-200');
                }
            });
        });
    });
</script>
@endpush

<style>
    .payment-method-label input:checked ~ * {
        border-color: rgb(59 130 246);
    }
</style>
@endsection
