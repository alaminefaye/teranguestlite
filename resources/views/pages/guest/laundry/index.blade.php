@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Blanchisserie</h1>
</div>

<!-- Formulaire Demande -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Nouvelle demande</h2>
    <form action="{{ route('guest.laundry.request') }}" method="POST" x-data="laundryManager()">
        @csrf
        @foreach($services as $category => $categoryServices)
            <div class="mb-6">
                <h3 class="font-medium text-gray-900 dark:text-white/90 mb-3">{{ ucfirst(str_replace('_', ' ', $category)) }}</h3>
                @foreach($categoryServices as $service)
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white/90">{{ $service->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $service->formatted_price }} · Délai: {{ $service->turnaround_hours }}h</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="decrease({{ $service->id }})" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">−</button>
                            <input type="hidden" :name="'items[' + items.findIndex(i => i.laundry_service_id == {{ $service->id }}) + '][laundry_service_id]'" :value="{{ $service->id }}" x-show="items.find(i => i.laundry_service_id == {{ $service->id }})">
                            <input type="hidden" :name="'items[' + items.findIndex(i => i.laundry_service_id == {{ $service->id }}) + '][quantity]'" :value="items.find(i => i.laundry_service_id == {{ $service->id }})?.quantity || 0" x-show="items.find(i => i.laundry_service_id == {{ $service->id }})">
                            <span class="w-8 text-center font-medium" x-text="items.find(i => i.laundry_service_id == {{ $service->id }})?.quantity || 0"></span>
                            <button type="button" @click="add({{ $service->id }}, {{ $service->price }})" class="w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center">+</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
        <div class="bg-brand-50 dark:bg-brand-900/20 p-4 rounded-lg mb-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">Total: <span class="font-bold text-brand-600 text-lg" x-text="total.toLocaleString('fr-FR') + ' FCFA'"></span></p>
        </div>
        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code client</label>
            <input type="text" name="client_code" value="{{ old('client_code') }}" maxlength="20" placeholder="Ex: 123456 (reçu à l'enregistrement)" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">À remplir si votre compte n'a pas de séjour actif lié à la chambre. Sinon laissez vide.</p>
            @error('client_code')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instructions spéciales</label>
            <textarea name="special_instructions" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3"></textarea>
        </div>
        <button type="submit" class="w-full px-6 py-3 bg-brand-500 text-white rounded-md hover:bg-brand-600 font-medium" x-bind:disabled="items.length === 0">Envoyer la demande</button>
    </form>
</div>

<script>
function laundryManager() {
    return {
        items: [],
        add(id, price) {
            let item = this.items.find(i => i.laundry_service_id == id);
            if (item) {
                item.quantity++;
            } else {
                this.items.push({ laundry_service_id: id, quantity: 1, price: price });
            }
        },
        decrease(id) {
            let item = this.items.find(i => i.laundry_service_id == id);
            if (item) {
                item.quantity--;
                if (item.quantity <= 0) {
                    this.items = this.items.filter(i => i.laundry_service_id != id);
                }
            }
        },
        get total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }
    }
}
</script>
@endsection
