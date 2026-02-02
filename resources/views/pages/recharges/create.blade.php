@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('recharges.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Recharger un Compte</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajouter du crédit au compte d'un client</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-error-50 p-4 text-sm text-error-600 dark:bg-error-500/10 dark:text-error-400">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('recharges.store') }}" id="rechargeForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Sélection du client -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Sélectionner le Client</h3>
                    <div class="relative">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rechercher par numéro de téléphone *</label>
                        <input type="text" 
                               id="phone_search" 
                               name="phone_search" 
                               value="{{ $client ? $client->phone : old('phone_search') }}"
                               placeholder="Tapez le numéro de téléphone (ex: +221771234567 ou 771234567)"
                               autocomplete="off"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <input type="hidden" name="user_id" id="user_id" value="{{ $client ? $client->id : old('user_id') }}" required>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" id="client_selection_hint">
                            @if($client)
                                Client sélectionné: {{ $client->name }}
                            @else
                                Tapez le numéro de téléphone et sélectionnez un client dans les suggestions
                            @endif
                        </p>
                        @error('user_id')
                            <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                        <div id="client_suggestions" class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 max-h-60 overflow-y-auto hidden"></div>
                    </div>
                    <div id="selected_client_info" class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800 {{ $client ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90" id="client_name">{{ $client ? $client->name : '' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" id="client_details">
                                    {{ $client ? ($client->email . ' | ' . $client->phone) : '' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Solde actuel</p>
                                <p class="text-lg font-semibold {{ ($client && ($client->balance ?? 0) > 0) ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}" id="client_balance">
                                    {{ $client ? number_format($client->balance ?? 0, 0, ',', ' ') . ' FCFA' : '0 FCFA' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Montant et méthode de paiement -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Détails de la Recharge</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Montant (FCFA) *</label>
                            <input type="number" name="amount" value="{{ old('amount') }}" 
                                   min="1000" step="100" required
                                   placeholder="Montant minimum: 1000 FCFA"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('amount')
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Méthode de paiement *</label>
                            <select name="payment_method" id="payment_method" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Sélectionner une méthode</option>
                                <option value="orange_money" {{ old('payment_method') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                                <option value="free_money" {{ old('payment_method') == 'free_money' ? 'selected' : '' }}>Free Money</option>
                                <option value="wave" {{ old('payment_method') == 'wave' ? 'selected' : '' }}>Wave</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="phone_number_field" style="display: none;">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Numéro de téléphone *</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" 
                                   placeholder="+221771234567"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('phone_number')
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description (optionnel)</label>
                            <textarea name="description" rows="3"
                                      placeholder="Note ou description de la recharge..."
                                      class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">Informations</h3>
                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Le montant minimum est de 1000 FCFA</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Le solde sera mis à jour immédiatement</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-brand-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Une transaction sera enregistrée</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('recharges.index') }}" 
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Annuler
            </a>
            <button type="submit" id="submitBtn"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Effectuer la recharge
            </button>
        </div>
    </form>
</div>

<script>
// Recherche de client par téléphone
const phoneSearch = document.getElementById('phone_search');
const clientSuggestions = document.getElementById('client_suggestions');
const userIdInput = document.getElementById('user_id');
const selectedClientInfo = document.getElementById('selected_client_info');
let searchTimeout;

phoneSearch.addEventListener('input', function() {
    let phone = this.value.trim();
    
    // Nettoyer le numéro (enlever les espaces pour la recherche)
    phone = phone.replace(/\s+/g, '');
    
    // Réinitialiser la sélection si on modifie le numéro
    if (userIdInput.value && phone !== phoneSearch.getAttribute('data-selected-phone')) {
        userIdInput.value = '';
        selectedClientInfo.classList.add('hidden');
        updateSubmitButton();
        const hint = document.getElementById('client_selection_hint');
        if (hint) {
            hint.textContent = 'Tapez le numéro de téléphone et sélectionnez un client dans les suggestions';
            hint.className = 'mt-2 text-xs text-gray-500 dark:text-gray-400';
        }
    }
    
    if (phone.length < 2) {
        clientSuggestions.classList.add('hidden');
        updateSubmitButton();
        return;
    }
    
    // Debounce pour éviter trop de requêtes
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetch(`{{ route('recharges.search-client') }}?phone=${encodeURIComponent(phone)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.clients && data.clients.length > 0) {
                let html = '';
                data.clients.forEach(client => {
                    html += `
                        <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 client-option" 
                             data-client-id="${client.id}"
                             data-client-name="${client.name}"
                             data-client-email="${client.email}"
                             data-client-phone="${client.phone}"
                             data-client-balance="${client.balance || 0}">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 mb-1">${client.phone || 'N/A'}</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">${client.name}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${client.email}</p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Solde</p>
                                    <p class="text-sm font-semibold ${client.balance > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-400'}">
                                        ${parseFloat(client.balance || 0).toLocaleString('fr-FR')} FCFA
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                clientSuggestions.innerHTML = html;
                clientSuggestions.classList.remove('hidden');
                
                // Ajouter les event listeners
                document.querySelectorAll('.client-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const clientId = this.getAttribute('data-client-id');
                        const clientName = this.getAttribute('data-client-name');
                        const clientEmail = this.getAttribute('data-client-email');
                        const clientPhone = this.getAttribute('data-client-phone');
                        const clientBalance = parseFloat(this.getAttribute('data-client-balance'));
                        
                        // Mettre à jour les champs
                        userIdInput.value = clientId;
                        phoneSearch.value = clientPhone;
                        phoneSearch.setAttribute('data-selected-phone', clientPhone.replace(/\s+/g, ''));
                        
                        // Afficher les informations du client
                        document.getElementById('client_name').textContent = clientName;
                        document.getElementById('client_details').textContent = `${clientEmail} | ${clientPhone}`;
                        document.getElementById('client_balance').textContent = `${clientBalance.toLocaleString('fr-FR')} FCFA`;
                        document.getElementById('client_balance').className = `text-lg font-semibold ${clientBalance > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90'}`;
                        
                        selectedClientInfo.classList.remove('hidden');
                        clientSuggestions.classList.add('hidden');
                        
                        // Mettre à jour le hint
                        const hint = document.getElementById('client_selection_hint');
                        if (hint) {
                            hint.textContent = `✓ Client sélectionné: ${clientName}`;
                            hint.className = 'mt-2 text-xs text-success-600 dark:text-success-400 font-medium';
                        }
                        
                        // Mettre à jour le bouton de soumission
                        updateSubmitButton();
                    });
                });
            } else {
                if (phone.length >= 3) {
                    clientSuggestions.innerHTML = '<div class="p-3 text-sm text-gray-500 dark:text-gray-400">Aucun client trouvé avec ce numéro. Vérifiez le numéro ou créez un nouveau client.</div>';
                    clientSuggestions.classList.remove('hidden');
                } else {
                    clientSuggestions.classList.add('hidden');
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            clientSuggestions.classList.add('hidden');
        });
    }, 300);
});

// Masquer les suggestions quand on clique ailleurs
document.addEventListener('click', function(e) {
    if (!phoneSearch.contains(e.target) && !clientSuggestions.contains(e.target)) {
        clientSuggestions.classList.add('hidden');
    }
});

// Gestion du champ méthode de paiement
document.getElementById('payment_method').addEventListener('change', function() {
    const phoneField = document.getElementById('phone_number_field');
    const paymentMethod = this.value;
    
    if (['orange_money', 'free_money', 'wave'].includes(paymentMethod)) {
        phoneField.style.display = 'block';
        phoneField.querySelector('input').setAttribute('required', 'required');
    } else {
        phoneField.style.display = 'none';
        phoneField.querySelector('input').removeAttribute('required');
    }
});

// Afficher le champ téléphone si une méthode mobile est déjà sélectionnée
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment_method').value;
    if (['orange_money', 'free_money', 'wave'].includes(paymentMethod)) {
        document.getElementById('phone_number_field').style.display = 'block';
    }
    
    // Si un client est pré-sélectionné, afficher ses infos
    @if($client)
        phoneSearch.value = '{{ $client->phone }}';
        userIdInput.value = '{{ $client->id }}';
        updateSubmitButton();
    @endif
    
    // Vérifier l'état du formulaire au chargement
    updateSubmitButton();
});

// Validation du formulaire avant soumission
document.getElementById('rechargeForm').addEventListener('submit', function(e) {
    if (!userIdInput.value) {
        e.preventDefault();
        alert('Veuillez sélectionner un client en tapant son numéro de téléphone et en cliquant sur une suggestion.');
        phoneSearch.focus();
        return false;
    }
    
    const amount = document.querySelector('input[name="amount"]').value;
    if (!amount || parseFloat(amount) < 1000) {
        e.preventDefault();
        alert('Le montant minimum est de 1000 FCFA.');
        return false;
    }
    
    const paymentMethod = document.getElementById('payment_method').value;
    if (!paymentMethod) {
        e.preventDefault();
        alert('Veuillez sélectionner une méthode de paiement.');
        return false;
    }
    
    if (['orange_money', 'free_money', 'wave'].includes(paymentMethod)) {
        const phoneNumber = document.querySelector('input[name="phone_number"]').value;
        if (!phoneNumber) {
            e.preventDefault();
            alert('Veuillez entrer un numéro de téléphone pour cette méthode de paiement.');
            return false;
        }
    }
});

// Fonction pour mettre à jour l'état du bouton de soumission
function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    if (!userIdInput.value) {
        submitBtn.disabled = true;
        submitBtn.title = 'Veuillez sélectionner un client';
    } else {
        submitBtn.disabled = false;
        submitBtn.title = '';
    }
}

// Mettre à jour le bouton quand un client est sélectionné
phoneSearch.addEventListener('input', function() {
    updateSubmitButton();
});
</script>
@endsection
