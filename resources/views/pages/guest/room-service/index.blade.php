@extends('layouts.guest')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Room Service</h2>
            <p class="text-gray-600 dark:text-gray-400">Commander vos repas et boissons</p>
        </div>
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

    <!-- Categories & Items -->
    @forelse($categories as $category)
        <div class="space-y-4">
            <!-- Category Header -->
            <div class="flex items-center gap-3">
                @if($category->icon)
                    <span class="text-2xl">{{ $category->icon }}</span>
                @endif
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $category->name }}</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">({{ $category->menuItems->count() }} articles)</span>
            </div>

            <!-- Items Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($category->menuItems as $item)
                    <div class="tablet-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Image -->
                        @if($item->image)
                            <div class="aspect-square overflow-hidden bg-gray-100 dark:bg-gray-700">
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Content -->
                        <div class="p-4">
                            <div class="mb-2">
                                @if($item->is_featured)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 dark:text-brand-400 mb-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Populaire
                                    </span>
                                @endif
                                <h4 class="font-semibold text-gray-800 dark:text-white/90 line-clamp-2">{{ $item->name }}</h4>
                            </div>

                            @if($item->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">{{ $item->description }}</p>
                            @endif

                            <div class="flex items-center justify-between gap-2">
                                <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $item->formatted_price }}</span>
                                @if($item->preparation_time)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">⏱️ {{ $item->preparation_time }}min</span>
                                @endif
                            </div>

                            <!-- Add to Cart Button -->
                            <button onclick="addItemToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->price }})" 
                                class="mt-3 w-full px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 active:scale-95 transition-transform font-medium text-sm">
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">Aucun article disponible pour le moment.</p>
        </div>
    @endforelse
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-20 right-4 bg-success-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span id="toast-message">Article ajouté au panier !</span>
    </div>
</div>

<script>
    function addItemToCart(menuItemId, itemName, itemPrice) {
        // Ajouter au panier (localStorage)
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItem = cart.find(item => item.menu_item_id == menuItemId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ 
                menu_item_id: menuItemId, 
                name: itemName,
                price: itemPrice,
                quantity: 1 
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        
        // Mettre à jour le compteur
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
        
        // Afficher la notification
        showToast(`${itemName} ajouté au panier !`);
    }
    
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        
        toastMessage.textContent = message;
        toast.classList.remove('translate-x-full');
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
        }, 3000);
    }
</script>
@endsection
