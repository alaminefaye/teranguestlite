@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('rentals.show', $rental) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Paiement de la Location</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Location #{{ $rental->id }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Informations de la Location</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Véhicule</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $rental->vehicle->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Période</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $rental->start_date->format('d/m/Y') }} - {{ $rental->end_date->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Durée</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $rental->start_date->diffInDays($rental->end_date) + 1 }} jour(s)
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-800">
                        <span class="text-base font-semibold text-gray-800 dark:text-white/90">Montant total</span>
                        <span class="text-xl font-bold text-brand-500">{{ number_format($rental->total_price, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('rentals.payment.process', $rental) }}" class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                @csrf
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Méthode de Paiement</h3>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Choisir une méthode</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex cursor-pointer items-center rounded-lg border-2 border-gray-200 p-4 hover:border-brand-500 dark:border-gray-700">
                                <input type="radio" name="method" value="orange_money" class="peer sr-only" checked>
                                <div class="flex w-full items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">Orange Money</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Mobile Money</div>
                                    </div>
                                    <svg class="h-5 w-5 text-brand-500 opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-lg border-2 border-gray-200 p-4 hover:border-brand-500 dark:border-gray-700">
                                <input type="radio" name="method" value="free_money" class="peer sr-only">
                                <div class="flex w-full items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">Free Money</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Mobile Money</div>
                                    </div>
                                    <svg class="h-5 w-5 text-brand-500 opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-lg border-2 border-gray-200 p-4 hover:border-brand-500 dark:border-gray-700">
                                <input type="radio" name="method" value="wave" class="peer sr-only">
                                <div class="flex w-full items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">Wave</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Mobile Money</div>
                                    </div>
                                    <svg class="h-5 w-5 text-brand-500 opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-lg border-2 border-gray-200 p-4 hover:border-brand-500 dark:border-gray-700">
                                <input type="radio" name="method" value="cash" class="peer sr-only">
                                <div class="flex w-full items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">Espèces</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Paiement direct</div>
                                    </div>
                                    <svg class="h-5 w-5 text-brand-500 opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="phone-field" class="hidden">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Numéro de téléphone</label>
                        <input type="text" name="phone_number" 
                               placeholder="Ex: 77 123 45 67"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">ID Transaction (optionnel)</label>
                        <input type="text" name="transaction_id" 
                               placeholder="ID de transaction si disponible"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes (optionnel)</label>
                        <textarea name="notes" rows="3" 
                                  placeholder="Notes supplémentaires..."
                                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('rentals.show', $rental) }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            Annuler
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Procéder au paiement
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Résumé</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Montant</span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ number_format($rental->total_price, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const methodInputs = document.querySelectorAll('input[name="method"]');
        const phoneField = document.getElementById('phone-field');
        
        methodInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (['orange_money', 'free_money', 'wave'].includes(this.value)) {
                    phoneField.classList.remove('hidden');
                    phoneField.querySelector('input').setAttribute('required', 'required');
                } else {
                    phoneField.classList.add('hidden');
                    phoneField.querySelector('input').removeAttribute('required');
                }
            });
        });
    });
</script>
@endsection
