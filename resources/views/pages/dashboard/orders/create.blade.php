@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard.orders.index') }}" class="hover:text-brand-500">Commandes</a>
        <span>/</span>
        <span>Nouvelle commande</span>
    </div>
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Créer une nouvelle commande</h1>
</div>

<div x-data="orderForm()" class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <form action="{{ route('dashboard.orders.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Type de commande -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Type <span class="text-error-500">*</span>
                </label>
                <select name="type" id="type" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner un type</option>
                    <option value="room_service" {{ old('type') === 'room_service' ? 'selected' : '' }}>Room Service</option>
                    <option value="restaurant" {{ old('type') === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="bar" {{ old('type') === 'bar' ? 'selected' : '' }}>Bar</option>
                    <option value="spa" {{ old('type') === 'spa' ? 'selected' : '' }}>Spa</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Chambre -->
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Chambre <span class="text-error-500">*</span>
                </label>
                <select name="room_id" id="room_id" required
                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Sélectionner une chambre</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            Chambre {{ $room->room_number }} - {{ $room->type }}
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Articles -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Articles <span class="text-error-500">*</span>
                </label>
                <button type="button" @click="addItem()" class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    + Ajouter un article
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex gap-3 items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Article -->
                            <div class="md:col-span-2">
                                <select x-model="item.menu_item_id" :name="'items[' + index + '][menu_item_id]'" required
                                    @change="updatePrice(index)"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                                    <option value="">Sélectionner un article</option>
                                    @foreach($menuItems as $categoryName => $items)
                                        <optgroup label="{{ $categoryName }}">
                                            @foreach($items as $menuItem)
                                                <option value="{{ $menuItem->id }}" data-price="{{ $menuItem->price }}">
                                                    {{ $menuItem->name }} - {{ number_format($menuItem->price, 0, ',', ' ') }} FCFA
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantité -->
                            <div class="flex items-center gap-2">
                                <input type="number" x-model.number="item.quantity" :name="'items[' + index + '][quantity]'" 
                                    min="1" required @input="updateTotal()"
                                    class="w-24 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-800 dark:text-white/90">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatPrice(item.price * item.quantity)"></span>
                            </div>
                        </div>

                        <!-- Bouton supprimer -->
                        <button type="button" @click="removeItem(index)" class="text-error-600 hover:text-error-700 dark:text-error-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            @error('items')
                <p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Résumé du total -->
        <div class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Sous-total:</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">TVA (18%):</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(tax)"></span>
                </div>
                <div class="flex justify-between text-sm" x-show="deliveryFee > 0">
                    <span class="text-gray-600 dark:text-gray-400">Frais de livraison:</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(deliveryFee)"></span>
                </div>
                <div class="flex justify-between text-base font-semibold pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-800 dark:text-white/90">Total:</span>
                    <span class="text-brand-600 dark:text-brand-400" x-text="formatPrice(total)"></span>
                </div>
            </div>
        </div>

        <!-- Instructions spéciales -->
        <div class="mb-6">
            <label for="special_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Instructions spéciales
            </label>
            <textarea name="special_instructions" id="special_instructions" rows="3"
                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90 focus:border-brand-500 focus:ring-brand-500">{{ old('special_instructions') }}</textarea>
            @error('special_instructions')
                <p class="mt-1 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700">
                Créer la commande
            </button>
            <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
    function orderForm() {
        return {
            items: [{menu_item_id: '', quantity: 1, price: 0}],
            
            addItem() {
                this.items.push({menu_item_id: '', quantity: 1, price: 0});
            },
            
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.updateTotal();
                }
            },
            
            updatePrice(index) {
                const selectEl = document.querySelector(`select[name="items[${index}][menu_item_id]"]`);
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                this.items[index].price = parseFloat(selectedOption.dataset.price || 0);
                this.updateTotal();
            },
            
            updateTotal() {
                // Force recalculation
                this.$nextTick();
            },
            
            get subtotal() {
                return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },
            
            get tax() {
                return this.subtotal * 0.18;
            },
            
            get deliveryFee() {
                const type = document.getElementById('type').value;
                return type === 'room_service' ? 1000 : 0;
            },
            
            get total() {
                return this.subtotal + this.tax + this.deliveryFee;
            },
            
            formatPrice(price) {
                return new Intl.NumberFormat('fr-FR', { 
                    style: 'currency', 
                    currency: 'XOF',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(price);
            }
        }
    }
</script>
@endsection
