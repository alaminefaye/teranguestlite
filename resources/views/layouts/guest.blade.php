<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Guest Portal' }} - Teranga Guest</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Optimisations pour tablette */
        body {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .tablet-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .tablet-card:active {
            transform: scale(0.98);
        }
        
        .tablet-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white/90">
                        {{ auth()->user()->enterprise->name ?? 'Teranga Guest' }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Chambre {{ auth()->user()->room_number ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Cart Icon (si sur room service) -->
                    @if(request()->routeIs('guest.room-service.*'))
                        <a href="{{ route('guest.room-service.cart') }}" class="relative">
                            <svg class="w-8 h-8 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span id="cart-count" class="absolute -top-2 -right-2 bg-brand-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                0
                            </span>
                        </a>
                    @endif
                    
                    <!-- User Menu -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ auth()->user()->name }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-error-600 hover:text-error-700">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6 pb-24">
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    <nav class="tablet-nav bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-5 gap-2 py-3">
                <a href="{{ route('guest.dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('guest.dashboard') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="text-xs font-medium">Accueil</span>
                </a>

                <a href="{{ route('guest.room-service.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('guest.room-service.*') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-xs font-medium">Room Service</span>
                </a>

                <a href="{{ route('guest.orders.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('guest.orders.*') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-xs font-medium">Commandes</span>
                </a>

                <a href="{{ route('guest.services.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('guest.services.*') || request()->routeIs('guest.restaurants.*') || request()->routeIs('guest.spa.*') || request()->routeIs('guest.excursions.*') || request()->routeIs('guest.laundry.*') || request()->routeIs('guest.palace.*') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs font-medium">Services</span>
                </a>

                <a href="{{ route('guest.dashboard') }}" class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-xs font-medium">Profil</span>
                </a>
            </div>
        </div>
    </nav>

    <script>
        // Gestion du panier (localStorage)
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = totalItems;
                cartCountEl.style.display = totalItems > 0 ? 'flex' : 'none';
            }
        }

        // Mettre à jour le compteur au chargement
        document.addEventListener('DOMContentLoaded', updateCartCount);

        // Fonction globale pour ajouter au panier
        window.addToCart = function(menuItemId, quantity = 1) {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existingItem = cart.find(item => item.menu_item_id == menuItemId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({ menu_item_id: menuItemId, quantity: quantity });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
        };
    </script>
</body>
</html>
