@extends('layouts.guest')

@section('content')
<div x-data="cartManager()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Mon Panier</h2>
            <p class="text-gray-600 dark:text-gray-400">Vérifiez votre commande avant de valider</p>
        </div>
        <a href="{{ route('guest.room-service.index') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">
            ← Continuer mes achats
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-success-50 p-4 text-success-600 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-error-50 p-4 text-error-600 dark:bg-error-500/10 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Cart Items -->
    <div x-show="cart.length > 0" class="space-y-4">
        <template x-for="(item, index) in cart" :key="index">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <!-- Item Info -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800 dark:text-white/90" x-text="item.name"></h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="formatPrice(item.price)"></p>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="flex items-center gap-2">
                        <button @click="decreaseQuantity(index)" 
                            class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="w-8 text-center font-semibold text-gray-800 dark:text-white/90" x-text="item.quantity"></span>
                        <button @click="increaseQuantity(index)" 
                            class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Item Total -->
                    <div class="text-right">
                        <p class="font-semibold text-gray-800 dark:text-white/90" x-text="formatPrice(item.price * item.quantity)"></p>
                    </div>

                    <!-- Remove Button -->
                    <button @click="removeItem(index)" class="text-error-600 hover:text-error-700 dark:text-error-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty Cart -->
    <div x-show="cart.length === 0" class="text-center py-12">
        <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="text-gray-600 dark:text-gray-400 mb-4">Votre panier est vide</p>
        <a href="{{ route('guest.room-service.index') }}" class="inline-flex items-center px-6 py-3 bg-brand-500 text-white rounded-lg hover:bg-brand-600">
            Découvrir notre menu
        </a>
    </div>

    <!-- Summary & Checkout -->
    <div x-show="cart.length > 0" class="space-y-4">
        <!-- Instructions spéciales -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <label for="special_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Instructions spéciales (optionnel)
            </label>
            <textarea x-model="specialInstructions" id="special_instructions" rows="2" 
                placeholder="Ex: Sans sel, sans épices..." 
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-800 dark:text-white/90"></textarea>
        </div>

        <!-- Order Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Résumé de la commande</h3>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Sous-total:</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">TVA (18%):</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(tax)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Frais de livraison:</span>
                    <span class="text-gray-800 dark:text-white/90 font-medium" x-text="formatPrice(deliveryFee)"></span>
                </div>
                <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-800 dark:text-white/90">Total:</span>
                    <span class="text-brand-600 dark:text-brand-400" x-text="formatPrice(total)"></span>
                </div>
            </div>

            <form @submit.prevent="checkout" method="POST" action="{{ route('guest.room-service.checkout') }}">
                @csrf
                <input type="hidden" name="special_instructions" :value="specialInstructions">
                <template x-for="(item, index) in cart" :key="index">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][menu_item_id]'" :value="item.menu_item_id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                    </div>
                </template>
                
                <button type="submit" 
                    class="w-full px-6 py-4 bg-brand-500 text-white rounded-lg hover:bg-brand-600 active:scale-95 transition-transform font-semibold text-lg">
                    Commander maintenant
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function cartManager() {
        return {
            cart: [],
            specialInstructions: '',
            
            init() {
                this.loadCart();
            },
            
            loadCart() {
                this.cart = JSON.parse(localStorage.getItem('cart') || '[]');
            },
            
            saveCart() {
                localStorage.setItem('cart', JSON.stringify(this.cart));
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            },
            
            increaseQuantity(index) {
                this.cart[index].quantity++;
                this.saveCart();
            },
            
            decreaseQuantity(index) {
                if (this.cart[index].quantity > 1) {
                    this.cart[index].quantity--;
                    this.saveCart();
                }
            },
            
            removeItem(index) {
                if (confirm('Retirer cet article du panier ?')) {
                    this.cart.splice(index, 1);
                    this.saveCart();
                }
            },
            
            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },
            
            get tax() {
                return this.subtotal * 0.18;
            },
            
            get deliveryFee() {
                return 1000; // Frais de livraison pour room service
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
            },
            
            checkout() {
                // Le formulaire sera soumis automatiquement
                // Vider le panier après soumission réussie
                localStorage.removeItem('cart');
            }
        }
    }
</script>
@endsection
